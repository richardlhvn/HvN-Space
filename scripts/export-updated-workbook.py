#!/usr/bin/env python3
"""Create a private, tag-normalized copy of the owner's master XLSX.

Preserves existing tabs/columns, review decisions, evidence and historical logs.
Does not evaluate workbook formulas or write to WordPress/Google Sheets.
"""
import argparse
import hashlib
import html
import json
import re
from collections import Counter
from pathlib import Path

from openpyxl import load_workbook
from openpyxl.comments import Comment
from openpyxl.styles import Alignment, Font, PatternFill
from openpyxl.utils import get_column_letter
from openpyxl.workbook.properties import CalcProperties

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / 'plugin/data'
HISTORY = {'Update Audit', 'Cleanup Log', 'Unresolved QA', 'Core 128 QA Audit',
           'Editorial Rewrite Review', 'Copy Revision v2', 'Listener Copy v3',
           'Crash Research', 'New Songs Review', 'Merge Notes'}
TAG_FIELDS = {'genres': 'genre', 'moods': 'mood', 'themes': 'theme'}
DATE = '2026-11-14'


def slug(value):
    value = re.sub(r'\s*\([^)]*\)', '', html.unescape(value)).lower().replace('&', '').replace("'", '')
    return re.sub(r'[^a-z0-9]+', '-', value).strip('-')


def build(input_path, output_path, brand_map_path):
    book = load_workbook(input_path, data_only=False)
    original_sheets = list(book.sheetnames)
    brand_map = json.loads(brand_map_path.read_text())
    groups = {tax: json.loads((DATA / f'{tax}-groups.json').read_text())['groups'] for tax in TAG_FIELDS.values()}
    aliases = {tax: {slug(alias): key for key, group in catalog.items()
                    for alias in [key, group['name']] + group['aliases']}
               for tax, catalog in groups.items()}
    packs = {}
    for path in DATA.glob('*.json'):
        data = json.loads(path.read_text())
        if isinstance(data, dict) and data.get('schema_version') == 'afterglow.research.v1':
            packs[data['artist']['slug']] = data
    manifest = json.loads((DATA / 'canonical-research-source.json').read_text())
    packs.update(json.loads((DATA / manifest['prepared_snapshot']).read_text())['packs'])
    artists = {key: pack['artist'] for key, pack in packs.items()}
    songs = {(key, song['slug']): song for key, pack in packs.items() for song in pack['songs']}
    changes = []
    retired = []

    def set_value(sheet, row, col, value, reason, artist='', song=''):
        cell = sheet.cell(row, col)
        if cell.value == value:
            return
        changes.append([sheet.title, cell.coordinate, artist, song,
                        sheet.cell(1, col).value, cell.value, value, reason, DATE])
        cell.value = value

    def normalize(tax, value, key):
        values = json.loads(value) if value else []
        if not isinstance(values, list):
            raise ValueError(f'{key}: expected a JSON array')
        result = []
        for label in values:
            old = slug(label)
            if tax == 'mood' and old in ['atmospheric', 'melodic', 'late-night']:
                retired.append((key, label))
                continue
            if old not in aliases[tax]:
                raise ValueError(f'{key}: unmapped {tax}: {label}')
            name = groups[tax][aliases[tax][old]]['name']
            if name not in result:
                result.append(name)
        return result

    def brand(value):
        if isinstance(value, str):
            for old, new in brand_map.items():
                value = value.replace(old, new)
        elif isinstance(value, list):
            value = [brand(child) for child in value]
        elif isinstance(value, dict):
            value = {key: brand(child) for key, child in value.items()}
        return value

    for name in ['Songs Exact', 'Song Review', 'Artists Exact']:
        sheet = book[name]
        headers = {cell.value: cell.column for cell in sheet[1] if cell.value}
        for row in range(2, sheet.max_row + 1):
            artist = sheet.cell(row, headers.get('artist_slug', headers.get('slug'))).value or ''
            song = '' if name == 'Artists Exact' else sheet.cell(row, headers.get('song_slug', headers.get('slug'))).value or ''
            record = artists[artist] if name == 'Artists Exact' else songs[artist, song]
            for field, tax in TAG_FIELDS.items():
                if field not in headers:
                    continue
                cell = sheet.cell(row, headers[field])
                old_values = json.loads(cell.value) if cell.value else []
                if tax == 'mood':
                    retired.extend((f'{name}!{cell.coordinate}', label) for label in old_values if slug(label) in ['atmospheric','melodic','late-night'])
                new = normalize(tax, json.dumps(record.get(field, [])), f'{name}!{cell.coordinate}')
                if old_values != new:
                    set_value(sheet, row, cell.column, json.dumps(new, ensure_ascii=False, separators=(',', ':')),
                              'Match current source classification assignments; merge aliases and preserve empty source tags.', artist, song)

    # Known first-party brand phrases only. Historical old/new evidence stays intact.
    for sheet in book:
        if sheet.title in HISTORY:
            continue
        headers = {c.value: c.column for c in sheet[1] if isinstance(c.value, str)}
        for row in list(sheet):
            for cell in row:
                if not isinstance(cell.value, str) or cell.data_type == 'f':
                    continue
                old = cell.value
                try:
                    decoded = json.loads(old)
                except (ValueError, TypeError):
                    decoded = None
                if isinstance(decoded, (list, dict)):
                    new_value = brand(decoded)
                    new = json.dumps(new_value, ensure_ascii=False, separators=(',', ':')) if new_value != decoded else old
                else:
                    new = brand(old)
                if sheet.title in {'Research Guide','Field Mapping','Glossary','Copy Style Guide'}:
                    new = re.sub(r'\bAfterglow\b', 'tunetofond', new)
                if new != old:
                    artist_col = headers.get('artist_slug', headers.get('Artist slug'))
                    song_col = headers.get('song_slug', headers.get('Song slug'))
                    set_value(sheet, cell.row, cell.column, new, 'Approved first-party brand reference: tunetofond.',
                              sheet.cell(cell.row, artist_col).value if artist_col else '',
                              sheet.cell(cell.row, song_col).value if song_col else '')

    guide = book['Research Guide']
    set_value(guide, 1, 1, 'tunetofond — RESEARCH WORKSHEET', 'Current workbook identity.')
    set_value(guide, 1, 2, f'Updated {DATE} · Core 0.15.0 / Theme 0.9.0', 'Current data/format revision.')
    set_value(guide, 10, 2, 'This version retains original facts, evidence, approvals and historical review tabs. Current genre, mood and theme arrays use the shared vocabulary. See Update Notes and Workbook Sync Log.', 'Replace superseded workbook-format guidance.')
    set_value(guide, 13, 2, 'All 53 artists and 558 existing song rows are retained. Only the approved ten artists / 128 songs form the public catalog. Hidden research remains available for future review.', 'Current scope and classification policy.')
    set_value(book['Copy Style Guide'], 1, 1, 'tunetofond / ABOUT THE SONG — Editorial Review', 'Current workbook identity.')

    for sheet_name in ['Field Mapping', 'Glossary']:
        sheet = book[sheet_name]
        for row in range(2, sheet.max_row + 1):
            field = sheet.cell(row, 2).value
            if field in TAG_FIELDS:
                tax = TAG_FIELDS[field]
                example = {'genre': '["Pop","Singer-Songwriter"]', 'mood': '["Reflection","Hopeful"]',
                           'theme': '["Relationships","Moving On & Closure"]'}[tax]
                if sheet_name == 'Field Mapping':
                    set_value(sheet, row, 6, f'JSON array using the {len(groups[tax])} shared {tax} labels in Tag Reference.', 'Current tag editing format.')
                    set_value(sheet, row, 7, 'Use exact labels from Tag Reference; preserve JSON array syntax. Update the matching Song Review row when changing Songs Exact.', 'Current tag editing format.')
                else:
                    set_value(sheet, row, 5, 'JSON array of exact labels from Tag Reference', 'Current tag editing format.')
                    set_value(sheet, row, 6, example, 'Current tag editing example.')
                    set_value(sheet, row, 9, 'Use only the shared labels; no extra “(editorial)” suffix. Keep empty arrays empty when no tag is supported.', 'Current tag editing format.')

    # Replace a Google export placeholder with a portable Excel/Sheets formula.
    set_value(book['Launch Readiness'], 2, 4,
              '=SUMPRODUCT((\'Review 10 Artists\'!L2:L129<>"")/COUNTIF(\'Review 10 Artists\'!L2:L129,\'Review 10 Artists\'!L2:L129&""))&" unique artists"',
              'Replace unsupported __xludf.DUMMYFUNCTION export wrapper; formula is not evaluated by this script.')

    reference = book.create_sheet('Tag Reference')
    reference.append(['Type', 'Tag label', 'Slug', 'JSON example', 'Previous aliases (reference only)'])
    for tax, catalog in groups.items():
        for key, group in catalog.items():
            old = list(group['aliases'])
            reference.append([tax, group['name'], key, json.dumps([group['name']], ensure_ascii=False), ', '.join(old)])

    notes = book.create_sheet('Update Notes', 0)
    notes.append(['Item', 'Current value / instruction'])
    notes_rows = [
        ('Workbook', f'tunetofond Master Worksheet · updated {DATE}'),
        ('Source', input_path.name),
        ('Original workbook SHA-256', hashlib.sha256(input_path.read_bytes()).hexdigest()),
        ('Site source baseline', 'b4f4182307ab70bd108e35c67e45e24f1baa6c4d · Core 0.15.0 / Theme 0.9.0'),
        ('Data retained', '53 artists; 558 song rows; all 24 original tabs retained.'),
        ('Public scope', 'Ten approved artists / 128 verified selections; other research remains outside the public catalog.'),
        ('Shared tags', '15 genres / 20 feelings (moods) / 20 themes. See Tag Reference.'),
        ('Mood descriptors retired', 'melodic, atmospheric and late-night are removed from mood lists without inventing an emotion. Original values are recorded in Workbook Sync Log.'),
        ('Tag assignment baseline', 'All artist/song classifications match the current active source bundles, with the approved ten/128 canonical dataset taking precedence. Unsupported old tags on Jamie Miller / it-is-what-it-is are cleared to match its empty source assignments, with original values retained in Workbook Sync Log.'),
        ('Edit songs', 'Edit Songs Exact using the existing columns. Mirror changes to the matching Song Review row using artist_slug + song_slug, never title alone.'),
        ('Edit artists', 'Edit Artists Exact. Keep slug, name, source identities and permissions intact unless a deliberate reviewed change is intended.'),
        ('Tag cell format', 'JSON arrays of exact Tag Reference labels, e.g. ["Reflection","Hopeful"]. Use [] when intentionally empty. Do not add (editorial) or invent new labels.'),
        ('Approve songs', 'Review 10 Artists remains the approval sheet. Keep the existing 15 headers and verified decisions; its artist/song keys identify approved records.'),
        ('Historical tabs', 'Review drafts, Update Audit, Cleanup Log, Unresolved QA and previous copy versions remain historical evidence. Old labels/brand wording there describe earlier states.'),
        ('Preserved data', 'Research wording except approved first-party brand references; source URLs/IDs/statuses, Spotify URLs, release dates/precision, media rights and approval decisions are preserved.'),
        ('Formatting', 'Original tab/column names retained; filters cover populated rows, headers stay frozen, and current tag columns are highlighted. Review decisions keep their existing dropdowns.'),
        ('Import into existing Google Sheet', 'File → Import → Upload this XLSX. “Replace spreadsheet” updates the current spreadsheet contents while retaining its file URL. This workbook is based on the uploaded source named above, not a fresh download of later Sheet edits.'),
        ('Future website updates', 'Editing Google Sheets does not automatically change WordPress. Export the revised Sheet as XLSX and provide it for a protected import/review.'),
        ('Change log', 'Workbook Sync Log records each changed cell with its original and updated values.'),
        ('Formula handling', 'Existing dashboard formulas are retained for Excel/Google Sheets to recalculate. No workbook formula or macro was executed to prepare this file.'),
    ]
    for row in notes_rows: notes.append(row)

    log = book.create_sheet('Workbook Sync Log')
    log.append(['Sheet', 'Cell', 'Artist slug', 'Song slug', 'Field / header', 'Original value', 'Updated value', 'Reason', 'Updated on'])
    for row in changes: log.append(row)
    # Formula text in the audit is evidence, never an executable log formula.
    for row in log:
        for cell in row:
            if isinstance(cell.value, str): cell.data_type = 's'

    # Keep long data readable without changing underlying text or date precision.
    special = {'Research Guide', 'Merge Notes', 'Copy Style Guide'}
    for sheet in book:
        sheet.sheet_view.showGridLines = False
        if sheet.title in special:
            continue
        last_row = max((cell.row for row in sheet for cell in row if cell.value is not None), default=1)
        sheet.auto_filter.ref = f'A1:{get_column_letter(sheet.max_column)}{last_row}'
        sheet.freeze_panes = {'Songs Exact': 'D2', 'Artists Exact': 'C2', 'Review 10 Artists': 'D2',
                             'Song Review': 'C2'}.get(sheet.title, sheet.freeze_panes or 'A2')
        sheet.row_dimensions[1].height = 38
        for cell in sheet[1]:
            cell.font = Font(name='Calibri', size=11, bold=True, color='FFFFFF')
            cell.fill = PatternFill('solid', fgColor='675477')
            cell.alignment = Alignment(vertical='center', wrap_text=True)
            label = str(cell.value or '')
            if label in TAG_FIELDS:
                width = 32 if label != 'themes' else 42
                example = {'genres': '["Pop","Singer-Songwriter"]', 'moods': '["Reflection","Hopeful"]', 'themes': '["Relationships","Moving On & Closure"]'}[label]
                cell.comment = Comment('Use exact labels from Tag Reference, inside a JSON array. Example: ' + example + '. Empty: [].', 'tunetofond')
            elif any(word in label.lower() for word in ['biography','about the song','about_song','paraphrase','original value','updated value','instruction']): width = 56
            elif label in ['release_date','release_date_precision','position','id','Cell','Type','Updated on']: width = 18
            elif 'url' in label.lower() or 'source(s)' in label.lower(): width = 46
            else: width = max(18, min(36, len(label) + 5))
            sheet.column_dimensions[cell.column_letter].width = width
        for row in sheet.iter_rows(min_row=2, max_row=last_row):
            sheet.row_dimensions[row[0].row].height = 64 if sheet.title in ['Song Review','Songs Exact','Artists Exact','Review 10 Artists'] else 38
            for cell in row:
                if cell.value is None:
                    continue
                cell.alignment = Alignment(vertical='top', wrap_text=True)
                if sheet.cell(1, cell.column).value in TAG_FIELDS:
                    cell.fill = PatternFill('solid', fgColor='F0EAF3')
                    cell.font = Font(name='Calibri', size=11, color='4F3E5C')
        sheet.sheet_properties.pageSetUpPr.fitToPage = True
        sheet.sheet_properties.tabColor = '967DA2' if sheet.title in ['Tag Reference','Update Notes','Workbook Sync Log'] else ('6D8B80' if sheet.title in ['Songs Exact','Artists Exact','Song Review','Review 10 Artists'] else 'A6ABB1')
    notes.column_dimensions['A'].width = 29
    notes.column_dimensions['B'].width = 115
    for row in range(2, notes.max_row + 1): notes.row_dimensions[row].height = 47
    reference.column_dimensions['E'].width = 88
    log.column_dimensions['F'].width = 58
    log.column_dimensions['G'].width = 58
    log.column_dimensions['H'].width = 65
    book.calculation = CalcProperties(calcId=191029, fullCalcOnLoad=True, forceFullCalc=True)
    book.properties.title = 'tunetofond — Master Research Worksheet'
    book.properties.subject = 'Shared music discovery tags: 15 genres, 20 feelings, 20 themes'
    book.properties.lastModifiedBy = 'tunetofond'
    book.active = 0
    output_path.parent.mkdir(parents=True, exist_ok=True)
    book.save(output_path)
    report = {'original_sheets': original_sheets, 'output_sheets': book.sheetnames,
              'changes': len(changes), 'changes_by_sheet': dict(Counter(row[0] for row in changes)),
              'retired_mood_occurrences': len(retired), 'sha256': hashlib.sha256(output_path.read_bytes()).hexdigest()}
    output_path.with_suffix('.validation.json').write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n')
    print(json.dumps(report, indent=2))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--input', type=Path, required=True)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--brand-map', type=Path, required=True, help='JSON output of ag_brand_reference_map() from the active plugin')
    args = parser.parse_args()
    build(args.input, args.output, args.brand_map)
