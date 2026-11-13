# Reviewed research enrichment — 13 November 2026

**Resolved:** the owner supplied `Afterglow_Master_Worksheet.xlsx`, and the local
enrichment is complete. See [results, import instructions and outstanding QA](REVIEWED_WORKBOOK_ENRICHMENT_2026-11-13.md).
The earlier access blocker and plan below are retained as context.

## Current request and scope

Preserve the existing Afterglow WordPress site, design and functionality. Enrich
the existing catalog using the owner's newly completed research for ten artists
first. Artist intake remains paused. Preserve the other 43 artists and their
records, IDs, slugs, sources, editor changes, media, credentials and volumes.
No production deployment is authorized.

The attached Phase 1 prompt is reference material describing a proposed launch
workflow, not authorization to replace the current site or hide its catalog.
Its expected ten artists / 128 approved selections and verification claims must
be checked against the current data before being treated as confirmed.

Research sheet:
https://docs.google.com/spreadsheets/d/1UaICgRurAY9zVje9NGImxk7AtfuXYCXBaSLRLwwEuG0/edit

Reference tabs: Review 10 Artists, Songs Exact, Artists Exact, Sources, Media
Rights, Field Mapping, Launch Readiness. Read all relevant tabs from the same
workbook snapshot; one CSV tab alone is insufficient for evidence and mappings.

Referenced artists: Astrid S, Caleb Hearn, Chelsea Cutler, Clara Mae, Elina,
Mokita, Rosie Darling, Sara Kays, Sasha Alex Sloan and SHY Martin.

## Assessment and relevance

- Keep Core 0.9 / Theme 0.5 layouts, click-to-load official Spotify widgets,
  recommendations, optional About sections, existing media credits and privacy
  behavior. No redesign is needed to add research.
- Existing bundled research is older and has ten songs per artist. The parser
  in plugin/research-handoffs.php enforces that count. The new approved selection
  count may differ by artist; do not force the new research into ten-song packs.
- Match selections by artist_slug + song_slug, resolve existing aliases, and
  preserve Caleb Hearn/ROSIE's shared Little Bit Better page and canonical player.
- Preserve approved wording exactly, artist-scoped sources, attribution,
  release precision and uncertainty. A spreadsheet's verified label is an
  editorial status, not proof this environment independently verified playback
  or source passages.
- Reuse existing About/source fields where appropriate; inspect Field Mapping
  before introducing fields. Surface conflicting editor changes for review
  rather than forcing updates or silently skipping them.
- Existing six licensed portraits and abstract artwork remain appropriate.
  Compare returned rights evidence before changing media.
- Restricting public launch to approved records is a separate publication
  decision. Do not delete, unpublish or hide the other catalog records merely
  because the reference prompt describes a ten-artist launch.

## Next implementation steps after data access

1. Capture a dated, nonsecret workbook export and compare all relevant tabs.
   Validate actual approval keys, unique selections, source references, Spotify
   IDs, counts and recording aliases; report discrepancies rather than inventing
   rows to meet 128.
2. Produce a field-level diff for the ten artists: new selections, enrichment,
   unchanged values, conflicts and excluded/unapproved rows. Preserve later
   workbook edits and all existing records outside the selected update.
3. Back up the local database privately before changing data. Adapt the existing
   importer to explicit approved selection keys and variable song counts, with
   a dry run, protected upserts and created/updated/skipped/rejected/failed logs.
   Preserve unrelated catalog validation and import behavior.
4. Test in a disposable namespace, then apply the reviewed enrichment locally.
   Repeat the import and verify no duplicates, unchanged approved wording,
   stable IDs/slugs, correct relationships and preserved unrelated/editor data.
5. Run appropriate catalog, About, media and browser checks and show actual
   inline screenshots of representative enriched pages using the existing design.
6. Record remaining source/playback/manual QA separately from completed checks.
   The reference flags Astrid S / Sidelove recording identity and playback for
   independent QA. Previous SHY Martin / Forget to Forget and Clara Mae piano-demo
   verification remain pending unless the new evidence resolves them explicitly.
7. Defer public launch, production deployment, advertising and unrelated feature
   work. Assess the reference Launch Readiness checklist after enrichment.

## Current blocker and changes made

The CSV export request to docs.google.com failed at the environment proxy with
CONNECT 403 on 13 November 2026. No Google Sheets connector is available here.
The current workbook rows, approval totals and final wording have not been read.
The older continuation bundle cannot substitute for this newly completed data.

No application code, canonical research, database content or media changed in
this task. This note records the actionable follow-up while access is blocked.
