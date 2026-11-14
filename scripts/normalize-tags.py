#!/usr/bin/env python3
"""Prepare the owner's tag-only source delta; never edit workbook exports."""
import json, re, hashlib, html
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DATA = ROOT / 'plugin/data'
def read(path): return json.loads(path.read_text())
def write(path, value): path.write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n')
def slug(value):
    value = re.sub(r'\s*\([^)]*\)', '', html.unescape(value)).lower().replace('&', '').replace("'", '')
    return re.sub(r'[^a-z0-9]+', '-', value).strip('-')

THEMES = [
 ('love-romance', 'Love & Romance', 'love|early romance|first meeting|infatuation|romantic attraction|romantic confidence|short-lived romance|unexpected encounter|unexpected encounters|unspoken romantic attraction'),
 ('relationships', 'Relationships', 'relationships|relationships and personal reflection|being a second choice|conflicted attraction|dating|dating ambiguity|doubt|emotional detachment|emotional disconnection|falling out of love|jealousy|manipulation in a relationship|new relationships|one-sided relationships|painful attraction|past relationships|push-pull relationships|relationship comparison|relationship deterioration|relationship timing|repeated relationship mistakes|timing|uncertainty|unequal commitment|unhealthy attachment|unreciprocated affection'),
 ('heartbreak-breakups', 'Heartbreak & Breakups', 'breakup|breakups|heartbreak|separation'),
 ('longing-distance', 'Longing & Distance', 'distance|emotional distance|lingering attachment|lingering feelings|lingering love|missed connection'),
 ('memories-nostalgia', 'Memories & Nostalgia', 'breakup memories|idealizing the past|lost familiarity|memory|nostalgia|photos|post-breakup memories|preserving memories'),
 ('moving-on-closure', 'Moving On & Closure', 'acceptance|closure|letting go|moving on|starting over|uncertain closure'),
 ('trust-commitment', 'Trust & Commitment', 'choosing love despite uncertainty|commitment|emotional certainty|emotional clarity|mutual respect|secure commitment|trust in love|unconditional love'),
 ('communication-conflict', 'Communication & Conflict', 'arguments|avoidance|communication|different viewpoints|frustration|guilt|regret|self-blame'),
 ('friendship', 'Friendship', 'companionship|friendship|friendship changes|lost friendship|old friends|post-breakup friendship|summer reunions|unequal friendship'),
 ('family-childhood', 'Family & Childhood', 'childhood|childhood home|family|family changes'),
 ('growing-up-change', 'Growing Up & Change', 'adolescence|change|growing up|growth|lost innocence|personal growth|resistance to change'),
 ('identity-belonging', 'Identity & Belonging', 'belonging|feeling overlooked|gender stereotypes|identity in relationships|isolation|social displacement|social exclusion'),
 ('self-worth', 'Self-Worth & Body Image', 'body image|feeling insufficient|imperfection|insecurity|self-acceptance|self-acceptance struggles|self-comparison|self-criticism|self-doubt|self-image|self-worth'),
 ('mental-health', 'Mental Health & Anxiety', 'anxiety|concealed emotional pain|depression|emotional distress|emotional numbness|emotional overwhelm|existential unease|helplessness|mental health|mental wellness|overwhelm|overwhelming moments|panic|relationship anxiety|searching for relief'),
 ('grief-loss', 'Grief & Loss', 'bereavement|grief|loss|loss of a partner|remembering a friend|renewed grief'),
 ('healing-recovery', 'Healing & Recovery', 'love after heartbreak|past relationship wounds|personal recovery|processing hurt|recovery|writing to heal'),
 ('support-kindness', 'Support & Kindness', 'emotional reassurance|emotional support|empathy|encouragement|goodwill|mutual support|support|support through hardship|wishing someone well'),
 ('independence', 'Independence & Self-Belief', 'independence|self-belief'),
 ('vulnerability', 'Vulnerability', 'emotional risk|fear of vulnerability|risking love again|vulnerability'),
 ('life-purpose', 'Life & Purpose', 'escapism|happiness|living in the moment|meaning|pandemic experience|purpose|reflection|shared routines|world events'),
]

def main():
    groups = {key: {'name': name, 'aliases': [slug(v) for v in aliases.split('|')]} for key, name, aliases in THEMES}
    groups['love-romance']['aliases'].append('unspoken-romantic-attraction-press-release-characterization')
    write(DATA / 'theme-groups.json', {'schema_version': 'afterglow.theme-groups.v1', 'groups': groups})
    catalogs = {tax: read(DATA / f'{tax}-groups.json')['groups'] for tax in ['genre', 'mood', 'theme']}
    maps = {tax: {slug(label): key for key, g in gs.items() for label in [key, g['name']] + g['aliases']} for tax, gs in catalogs.items()}
    originals = {}
    def record(r, key):
        before = {f: r[f] for f in ['genres', 'moods', 'themes'] if f in r}
        for field, tax in [('genres','genre'), ('moods','mood'), ('themes','theme')]:
            if field not in r: continue
            values = []
            for old in r[field]:
                s = slug(old)
                if tax == 'mood' and s in ['atmospheric','melodic','late-night']: continue
                if s not in maps[tax]: raise ValueError(f'Unmapped {tax}: {old} ({key})')
                name = catalogs[tax][maps[tax][s]]['name']
                if name not in values: values.append(name)
            r[field] = values
        after = {f: r[f] for f in before}
        if before != after: originals[key] = before
    files = []
    for path in sorted(DATA.glob('*.json')):
        d = read(path)
        if not isinstance(d, dict) or d.get('schema_version') != 'afterglow.research.v1': continue
        artist = d['artist']['slug']
        record(d['artist'], f'{path.name}:{artist}/profile')
        for r in d['songs']: record(r, f'{path.name}:{artist}/{r["slug"]}')
        write(path, d); files.append(path.name)
    original = DATA / 'reviewed-tunetofond-2026-11-14.json'
    d = read(original)
    for artist, pack in d['packs'].items():
        record(pack['artist'], f'canonical:{artist}/profile')
        for r in pack['songs']: record(r, f'canonical:{artist}/{r["slug"]}')
    adjustment = {'authorized_on': '2026-11-14', 'policy': 'Owner-authorized classification tags only: 15 genres, 20 moods, 20 themes. Non-emotional mood descriptors atmospheric, melodic and late-night retired without inventing an emotion.', 'original_prepared_snapshot': original.name, 'original_prepared_sha256': hashlib.sha256(original.read_bytes()).hexdigest(), 'workbook_sync_status': 'pending_owner_update', 'original_workbook_unchanged': True}
    d['tag_adjustment'] = adjustment
    name = 'reviewed-tunetofond-tags-2026-11-14.json'
    write(DATA / name, d)
    manifest = read(DATA / 'canonical-research-source.json')
    manifest['prepared_snapshot'] = name
    manifest['tag_adjustment'] = adjustment
    write(DATA / 'canonical-research-source.json', manifest)
    archive = ROOT / 'docs/tag-sync-original-tags-2026-11-14.json'
    if not archive.exists(): write(archive, {'original_commit': '42324ecfa865a57ad7ca7748e2d5c58ef0188080', 'files': files, 'original_tags': originals})
    print(f'Normalized {len(files)} research bundles and canonical ten/128; {len(originals)} original tag sets archived.')

if __name__ == '__main__': main()
