#!/usr/bin/env python3
"""Read an XLSX as data (never execute formulas/macros) and export approved keys.

Requires openpyxl. Original worksheet wording and evidence status are retained.
This produces an explicit enrichment overlay, leaving historical packs intact.
"""
import argparse
import hashlib
import json
from pathlib import Path
import re

import openpyxl

ROOT = Path(__file__).resolve().parents[1]
ARTISTS = {'astrid-s', 'caleb-hearn', 'chelsea-cutler', 'clara-mae', 'elina',
           'mokita', 'rosie-darling', 'sara-kays', 'sasha-alex-sloan', 'shy-martin'}
ARRAYS = {'artist_credits', 'genres', 'moods', 'themes', 'editorial_notes_source_ids',
          'artist_statement_source_ids', 'related_songs', 'supported_claims',
          'uncertainties', 'about_song_source_ids', 'about_song_source_urls',
          'social_profiles', 'career_milestones', 'similar_artists'}
TOPICS = {
    'meaning': 'song-meaning', 'artist_feelings': 'artist-feelings',
    'feelings': 'artist-feelings', 'creative_process': 'creative-process',
    'production': 'creative-process', 'production_intent': 'creative-process',
    'inspiration': 'inspiration', 'collaboration': 'collaboration',
    'editorial_interpretation': 'song-meaning', 'artist_commentary': 'song-background',
    'editorial_lyric_interpretation': 'song-meaning',
}
ATTRIBUTIONS = {
    'afterglow_editorial': ['editorial_based_on_sources', 'editorial_lyric_interpretation',
        'editorial_lyric_interpretation_plus_ep_context', 'artist_published_lyrics_plus_catalogue_credits'],
    'third_party_reporting': ['catalogue;third_party_review', 'catalogue_credits',
        'catalogue;project_context', 'official_label_plus_apple_editorial_note',
        'third_party_song_specific_analysis', 'song_specific_press_analysis'],
    'mixed': ['artist_statement_plus_editorial', 'artist_statement_existing;production_report',
        'mixed_artist_and_press', 'artist_statement_reported;catalogue', 'artist_interview;catalogue',
        'reported_artist_statement;catalogue_credits', 'first_person_song_statement_plus_press_analysis',
        'artist_interview_plus_official_lyrics', 'apple_editorial_note_plus_first_person_process',
        'album_level_artist_statement_plus_apple_editorial', 'artist_lyrics_plus_first_person_live_reaction'],
    'artist_statement': ['artist_statement_paraphrase', 'artist_statement', 'artist_first_person',
        'first_person_song_statement', 'artist_interview', 'artist_statement_reported',
        'first_person_songwriting_commentary', 'official_label_with_artist_quote',
        'artist_statement_reported;artist_interview', 'label_artist_statement',
        'first_person_duet_explanation', 'first_person_track_by_track_interview',
        'official_release_with_artist_quote'],
}
ATTRIBUTION_MAP = {v: k for k, values in ATTRIBUTIONS.items() for v in values}
ATTRIBUTION_MAP.update({key: key for key in ATTRIBUTIONS})
TOPICS.update({value: value for value in ['song-meaning', 'artist-feelings', 'creative-process', 'inspiration', 'collaboration', 'song-background', 'interesting-facts']})


def export(path, source_url=None, research_date=None):
    source = json.loads((ROOT / 'plugin/data/canonical-research-source.json').read_text())
    source_id = re.search(r'/spreadsheets/d/([A-Za-z0-9_-]+)(?:/|$)', source_url or '')
    if not source_id or source_id[1] != source['spreadsheet_id']:
        raise ValueError('Supply --source-url for the current cleaned canonical Google Sheet; older workbook sources are superseded.')
    checksum = hashlib.sha256(path.read_bytes()).hexdigest()
    previous = json.loads((ROOT / 'plugin/data' / source['superseded_snapshot']).read_text())
    if checksum == previous['provenance']['sha256']:
        raise ValueError('This file is the superseded workbook snapshot. Upload/export the cleaned canonical workbook instead.')
    research_date = research_date or source['declared_on']
    if not re.fullmatch(r'\d{4}-\d{2}-\d{2}', research_date):
        raise ValueError('Research date must use YYYY-MM-DD.')
    book = openpyxl.load_workbook(path, read_only=True, data_only=False)
    def rows(name):
        iterator = iter(book[name].iter_rows())
        headers = [c.value for c in next(iterator)]
        result = []
        for cells in iterator:
            if not any(c.value is not None for c in cells):
                continue
            if any(c.data_type == 'f' for c in cells):
                raise ValueError(f'{name}: formulas in canonical data must be exported as values')
            result.append(dict(zip(headers, [c.value for c in cells])))
        return result

    def indexed(items, key):
        out = {}
        for item in items:
            k = key(item)
            if k in out:
                raise ValueError(f'Duplicate key: {k}')
            out[k] = item
        return out

    artists = indexed(rows('Artists Exact'), lambda r: r['slug'])
    songs = indexed(rows('Songs Exact'), lambda r: (r['artist_slug'], r['slug']))
    sources = indexed(rows('Sources'), lambda r: (r['artist_slug'], r['id']))
    rights = indexed(rows('Media Rights'), lambda r: r['artist_slug'])
    review = rows('Review 10 Artists')
    indexed(review, lambda r: (r['Artist slug'], r['Song slug']))
    approved = [r for r in review if r['Review decision (edit)'] == 'verified']
    if len(approved) != source['expected_selections']:
        raise ValueError(f"Expected {source['expected_selections']} approved selections; found {len(approved)}")
    if {r['Artist slug'] for r in approved} != ARTISTS:
        raise ValueError('The reviewed artist scope differs from the ten existing artists')

    def decoded(row):
        out = dict(row)
        for key in ARRAYS & row.keys():
            out[key] = json.loads(row[key]) if row[key] else []
            if not isinstance(out[key], list):
                raise ValueError(f'{key} must be a JSON list')
        if 'image' in row:
            out['image'] = json.loads(row['image']) if row['image'] else None
            if out['image'] is not None and not isinstance(out['image'], dict):
                raise ValueError('image must be a JSON object or null')
        return out

    packs = {}
    notes = []
    for slug in sorted(ARTISTS):
        # Neutral import metadata, not a fallback to the superseded research.
        # The new worksheet determines every included artist/song and order.
        packs[slug] = {
            'schema_version': 'afterglow.research.v1', 'research_date': research_date,
            'selection': {'type': 'curated', 'description': 'An editorial selection from the owner-approved launch catalog.',
                          'ranking_basis': None, 'market': None, 'retrieved_at': None,
                          'source_ids': [], 'limitations': ['Selection order is editorial, not a Spotify popularity ranking.']},
            'artist': decoded(artists[slug]), 'songs': [],
            'sources': [s for (a, _), s in sources.items() if a == slug],
            'media_rights': rights[slug],
        }

    for row in approved:
        slug, song_slug = row['Artist slug'], row['Song slug']
        key = slug + '/' + song_slug
        song = decoded(songs[slug, song_slug])
        for review_field, field in [
            ('About the Song — proposed reader-friendly copy', 'about_song'),
            ('Artist statement paraphrase — source-based', 'artist_statement_paraphrase'),
            ('Spotify track URL', 'spotify_url'), ('Attribution', 'about_song_attribution')]:
            if (row[review_field] or '') != (song[field] or ''):
                raise ValueError(f'{key}: canonical/review mismatch for {field}')
        if not song['about_song'] or not re.fullmatch(r'https://open\.spotify\.com/track/[A-Za-z0-9]{22}', song['spotify_url'] or ''):
            raise ValueError(f'{key}: missing approved copy or invalid track URL')
        raw_topics = song['about_song_type']
        raw_attribution = song['about_song_attribution']
        song['about_song_type'] = list(dict.fromkeys(TOPICS.get(t, 'song-background')
                                                   for t in json.loads(row['Topics'] or '[]')))
        song['about_song_attribution'] = ATTRIBUTION_MAP[raw_attribution]
        song['review'] = {
            'decision': 'verified', 'key': key, 'attribution': raw_attribution,
            'topics': raw_topics, 'notes': row['Reviewer notes (edit)'],
            'supporting_urls': row['Supporting source URL(s)'],
            'unregistered_source_urls': [],
        }
        # Reconcile an explicit About URL to its already existing scoped registry
        # entry. Never invent a source or mark a pending source as read.
        for url in song['about_song_source_urls']:
            matching = [s['id'] for (a, _), s in sources.items() if a == slug and s['url'] == url]
            if not matching:
                # Preserve the explicit approved reference without inventing
                # bibliographic details or pretending it has a registry ID.
                song['review']['unregistered_source_urls'].append(url)
                notes.append({'key': key, 'action': 'unregistered_approved_url_pending_source_metadata', 'url': url})
                continue
            if not any(i in song['about_song_source_ids'] for i in matching):
                song['about_song_source_ids'].append(matching[0])
                notes.append({'key': key, 'action': 'matched_existing_source_id', 'source_id': matching[0], 'url': url})
        packs[slug]['songs'].append(song)

    def references(value, ids):
        if isinstance(value, dict):
            for key, child in value.items():
                if key.endswith('source_ids') and child:
                    assert isinstance(child, list) and set(child) <= ids, (key, child)
                references(child, ids)
        elif isinstance(value, list):
            for child in value:
                references(child, ids)

    for slug, pack in packs.items():
        ids = {s['id'] for s in pack['sources']}
        references(pack['artist'], ids)
        for song in pack['songs']:
            references(song, ids)
            date = song['release_date']
            precision = song['release_date_precision']
            assert date and re.fullmatch({'day': r'\d{4}-\d{2}-\d{2}', 'month': r'\d{4}-\d{2}', 'year': r'\d{4}'}[precision], date), (slug, song['slug'])
        assert not pack['media_rights']['image_use_placeholder'] == 'false' or slug not in {'caleb-hearn', 'chelsea-cutler', 'elina', 'mokita'}

    return {
        'schema_version': 'afterglow.reviewed.v1',
        'provenance': {'file_name': path.name, 'sha256': checksum,
                       'source_url': source['url'], 'spreadsheet_id': source['spreadsheet_id'],
                       'canonical_source_declared_on': source['declared_on'],
                       'review_date': research_date, 'approval_tab': 'Review 10 Artists',
                       'approval_count': len(approved), 'excluded_review_rows': len(review) - len(approved),
                       'ignored_internal_tabs': source['internal_qa_tabs'],
                       'spotify_links_approval': source['spotify_links'],
                       'release_date_policy': source['release_date_policy'],
                       'source_verification': 'Worksheet editorial approvals; source access statuses retained. Not a new independent source or playback audit.'},
        'reconciliations': notes, 'packs': packs,
    }


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('workbook', type=Path)
    parser.add_argument('--source-url', required=True, help='Canonical Google Sheet URL from which this uploaded workbook was exported')
    parser.add_argument('--research-date', help='Date of the supplied research snapshot; defaults to the canonical-source declaration date')
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    data = export(args.workbook, source_url=args.source_url, research_date=args.research_date)
    args.output.write_text(json.dumps(data, ensure_ascii=False, indent=2) + '\n')
    print(json.dumps({'approved_selections': data['provenance']['approval_count'],
                      'artists': {k: len(v['songs']) for k, v in data['packs'].items()},
                      'reconciliations': data['reconciliations']}, indent=2))
