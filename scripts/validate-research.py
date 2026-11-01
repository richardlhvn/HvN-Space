#!/usr/bin/env python3
"""Validate bundled research without guessing links, dates, claims, or photo rights."""
import json
import re
from pathlib import Path
from urllib.parse import urlsplit

root = Path(__file__).resolve().parents[1]
catalog = json.loads((root / 'plugin/data/research-catalog.json').read_text())
assert catalog['schema_version'] == 'afterglow.catalog.v1'
slugs = [slug for batch in catalog['batches'] for slug in batch['slugs']]
assert len(slugs) == len(set(slugs)), 'Duplicate catalog artist'
tracks = {}
summary = []
records = {}
aliases = json.loads((root / 'plugin/data/research-aliases.json').read_text())
assert aliases['schema_version'] == 'afterglow.aliases.v1'
for slug in slugs:
    assert re.fullmatch(r'[a-z0-9]+(?:-[a-z0-9]+)*', slug)
    pack = json.loads((root / f'plugin/data/{slug}.json').read_text())
    assert pack['schema_version'] == 'afterglow.research.v1'
    assert pack['artist']['slug'] == slug
    assert len(pack['songs']) == 10
    ids = [source['id'] for source in pack['sources']]
    assert len(ids) == len(set(ids)), f'{slug}: duplicate source ID'
    song_slugs = [song['slug'] for song in pack['songs']]
    assert len(song_slugs) == len(set(song_slugs)), f'{slug}: duplicate song slug'

    def references(value):
        if isinstance(value, dict):
            for key, child in value.items():
                if key.endswith('source_ids'):
                    assert child is None or isinstance(child, list), (slug, key)
                    assert all(source in ids for source in child or []), (slug, key)
                references(child)
        elif isinstance(value, list):
            for child in value:
                references(child)

    references(pack)
    artist_url = pack['artist']['spotify_url']
    assert artist_url is None or re.fullmatch(r'https://open\.spotify\.com/artist/[A-Za-z0-9]{22}', artist_url)
    for song in pack['songs']:
        if any(k.startswith('about_song') for k in song):
            assert 'about_song' in song and (song['about_song'] is None or isinstance(song['about_song'], str)), slug
            types = {'song-meaning', 'creative-process', 'artist-feelings', 'collaboration', 'inspiration', 'song-background', 'interesting-facts'}
            attributions = {'artist_statement', 'third_party_reporting', 'afterglow_editorial', 'mixed'}
            for field in ['about_song_type', 'about_song_source_ids', 'about_song_source_urls']:
                assert isinstance(song.get(field, []), list) and all(isinstance(x, str) for x in song.get(field, [])), (slug, field)
                assert len(song.get(field, [])) == len(set(song.get(field, []))), (slug, field)
            assert song.get('about_song_attribution') in attributions | {'', None}, slug
            assert set(song.get('about_song_type', [])) <= types, slug
            assert all(urlsplit(u).scheme == 'https' and urlsplit(u).netloc and not re.search(r'\s', u) for u in song.get('about_song_source_urls', [])), slug
            if song.get('about_song'):
                assert song.get('about_song_attribution') in attributions, slug
                sources = [s for s in pack['sources'] if s['id'] in song.get('about_song_source_ids', [])]
                if song['about_song_attribution'] != 'afterglow_editorial':
                    assert sources and all(s['access_status'] == 'read' and s['source_type'] != 'website_current_status' for s in sources), slug
                assert set(song.get('about_song_source_urls', [])) <= {s['url'] for s in sources}, slug
                if song['about_song_attribution'] == 'artist_statement':
                    assert all('transcript' not in s['source_type'].lower() for s in sources), slug
        key = slug + '/' + song['slug']
        records[key] = song
        assert song['artist_slug'] == slug
        assert re.fullmatch(r'[a-z0-9]+(?:-[a-z0-9]+)*', song['slug'])
        assert all(row['song_slug'] in song_slugs for row in song['related_songs']), key
        assert song['release_date_precision'] in ('day', 'month', 'year', 'unknown', None), key
        if song['release_date']:
            formats = {'day': r'\d{4}-\d{2}-\d{2}', 'month': r'\d{4}-\d{2}', 'year': r'\d{4}'}
            assert re.fullmatch(formats[song['release_date_precision']], song['release_date']), key
        if song['spotify_url'] is not None:
            assert re.fullmatch(r'https://open\.spotify\.com/track/[A-Za-z0-9]{22}', song['spotify_url']), key
            assert song['spotify_url'] not in tracks, f'Shared recording requires review: {key}'
            tracks[song['spotify_url']] = key
        if pack['research_date'] >= '2026-10-29':
            assert song['position'] is None, key
            assert pack['artist']['image']['use_placeholder'] is True
            assert pack['artist']['image']['url'] is None
    summary.append({'slug': slug, 'songs': len(pack['songs']), 'exact_track_links': sum(bool(s['spotify_url']) for s in pack['songs'])})
for key, alias in aliases['records'].items():
    assert key in records and alias['canonical_key'] in records and alias['canonical_key'] not in aliases['records']
    a, b = records[key], records[alias['canonical_key']]
    assert a['title'].casefold() == b['title'].casefold() and a['artist_credits'] == b['artist_credits']
    assert a['spotify_url'] == alias['incoming_spotify_url'] and b['spotify_url'] == alias['canonical_spotify_url']
    assert a['release_date'] == b['release_date'] and a['release_date_precision'] == b['release_date_precision']
canonical = {key: record for key, record in records.items() if key not in aliases['records']}
print(json.dumps({'artists': len(summary), 'curated_song_selections': len(records), 'distinct_song_pages': len(canonical), 'shared_selections': len(aliases['records']), 'supplied_track_urls': len(tracks), 'active_track_mappings': sum(bool(s['spotify_url']) for s in canonical.values()), 'unresolved_track_links': sum(not s['spotify_url'] for s in canonical.values()), 'intake_status': catalog.get('artist_intake', {}).get('status', 'open'), 'packages': summary}, indent=2))
