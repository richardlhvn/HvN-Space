# About the Song — 2 November 2026

Core **0.9.0** and Theme **0.4.0** broaden the existing song narrative to include meaning, creative process, artist feelings, collaboration, inspiration, documented background and other interesting facts. Song meaning is optional. No artist is added and the intake remains paused at 53.

The song page keeps its Spotify/listening layout, discovery controls and responsive order. One **About the Song** section displays the best available narrative, with attribution and direct supporting-source links. Category metadata is not displayed as database labels. If there is no suitable narrative, the entire section is omitted; the introduction, listening link, release/research information and recommendations continue to work. Repeated identical paragraphs are displayed once. A supported legacy artist statement takes precedence over weaker editorial navigation copy; neither is converted into another person's intent.

## Fields and compatibility

These optional fields extend each existing `afterglow.research.v1` song object. The schema fragment is `plugin/schemas/song-about.schema.json`; PHP validation in `plugin/about-song.php` and `scripts/validate-research.py` additionally check source references and URLs.

| Research field | Format | WordPress metadata |
| --- | --- | --- |
| `about_song` | Original plain-text narrative; blank lines separate paragraphs; null/empty permitted | `_ag_about_song` |
| `about_song_type` | Array of category identifiers | `_ag_about_song_type`, newline-separated |
| `about_song_source_urls` | Array of exact HTTPS evidence URLs | `_ag_about_song_source_urls`, newline-separated |
| `about_song_source_ids` | Array of IDs in this artist's source registry | `_ag_about_song_source_ids`, newline-separated |
| `about_song_attribution` | `artist_statement`, `third_party_reporting`, `afterglow_editorial`, or `mixed` | `_ag_about_song_attribution` |

Categories: `song-meaning`, `creative-process`, `artist-feelings`, `collaboration`, `inspiration`, `song-background`, `interesting-facts`. Several may apply. For `mixed`, identify the speaker/reporting/editorial perspective in the narrative itself; a global label does not establish who said each claim.

Factual package imports require supporting source IDs whose registry records have usable HTTPS URLs and `access_status: read`; explicit URLs must match those IDs. Unconfirmed transcript-only material is not accepted as a new artist statement. Structural validation does not establish the truth of a claim: editors must read the exact passage, check the recording identity and avoid treating lyrics as autobiography. Existing `read` labels are supplied research, not evidence of a new independent source check in this update.

There are no new database columns or tables. Existing `artist_statement_paraphrase`, `editorial_notes`, `short_description`, source registries, uncertainty lists, post IDs, slugs, review/noindex status, and image-rights decisions remain intact. Old packages without the new fields continue to import. The importer only writes optional fields present in the incoming record; its existing whole-record edit protection still applies. Empty absent fields do not invalidate historic import baselines. Deliberately clearing an existing About narrative is an explicit override, including for the import protection check.

Legacy supported `artist_comment` / `meaning_type` fields map at read time to an attributed paraphrase. This does not duplicate or migrate their text into new fields. A useful, source-linked current editorial reading may map as **Afterglow interpretation** if there is no stronger account. Generic starter copy, missing-meaning guidance and text identical to the introduction do not become About narratives. Existing custom WordPress editor notes remain accessible below under editorial/research notes; old import boilerplate is retained in storage without becoming a redundant public explanation.

## Research in Sheets, CSV and JSON

Use `docs/research-templates/ABOUT_SONG.csv` as additional working columns, joined by **artist_slug + song_slug**. Preserve the existing worksheet tabs and identifiers. For array cells, use valid JSON arrays, such as `["collaboration","creative-process"]`. Use `[]` for no references/categories, and an empty cell for unavailable narrative. CSV quoting must preserve embedded commas, quotes and paragraph newlines. The JSON package uses actual arrays and null, not strings containing JSON. The WordPress editor accepts newline/comma-separated lists as a convenience.

Keep `researcher_notes` and `review_status` as working-review columns; they are not factual prose. Return the workbook/CSV for review and merge approved fields into the existing artist JSON packages. This does not add a new unattended spreadsheet uploader or make a review status an automatic publication decision. Add sources to the existing artist registry and preserve the old IDs. Never copy bare `src-001` between artists.

One strong piece of sourced background is enough. Leave the narrative empty when evidence is unavailable. Do not infer an artist's emotions, invent a listening experience, copy interview passages or lyrics, or force a lyrical interpretation onto an account of recording or collaboration.

## Additive update on the existing local site

1. Run `bash scripts/backup.sh` in WSL from the existing project directory. Keep the SQL backup private.
2. Copy the latest **plugin** and **theme** folders into that existing project, preserving independent code edits and keeping `.env`, `.runtime`, database volumes and uploads.
3. Run `docker compose restart wordpress` and refresh with Ctrl+F5. Existing legacy statements now map automatically.
4. In WordPress, open **Songs → About the Song**, review the two additions, then choose **Add sourced song background**. This updates only the new optional metadata for existing, unchanged targets; it does not reimport 53 artists or rewrite their old text. Owner-edited targets, existing About fields (even blank overrides), and missing/trashed targets are preserved.

The same administrator-only migration is callable through `ag_sync_about_songs(true)` for a dry run and `ag_sync_about_songs(false)` to apply. It shares the research import lock. Repeating it adds nothing. New full research imports also support these fields while retaining the existing protections and shared-duet alias behavior.

## Examples and remaining research

- **SHY Martin — Forget to Forget:** the owner's newly supplied Billboard paraphrase is saved in `plugin/data/about-song-review.json` as a proposal. The existing package had no verified song-specific meaning. The article request failed at the environment proxy with CONNECT 403, so no artist meaning was silently promoted or added to the page. Verify the exact original passage before adding its scoped source and applying the new narrative. This acceptance case remains blocked by source verification.
- **Astrid S / NOTD — I Don't Know Why:** the new narrative reuses the supplied NOTD interview and catalog evidence (`src-035`, `src-023`). It describes collaboration and the vocal topline without attributing undocumented feelings or a lyrical story to Astrid.
- **KREAM / Clara Mae — Taped Up Heart:** the new narrative uses the existing producer-interview evidence (`src-007`) for remote collaboration and electronic production around Clara Mae's vocal. The additional piano-demo detail and Clara Mae's impressions from the owner's Euphoria link remain a proposal pending an exact-passage check; they were not invented from the source title.
- **ROSIE — Never the 1:** the unsupported About section is omitted; the normal introduction, listening fallback and recommendations still render.
- **Meaning plus production:** a temporary, explicitly marked local rendering fixture verifies two attributed paragraphs and one section without duplicate narrative text. It is deleted after the browser test and is not a new research record.

## Backup and rollback

A private SQL backup was made before the local update. The two actual additions change only the five new About metadata values and their import baselines; all older fields are compared before/after. To undo just this additive migration, restore those six metadata entries for the two targeted IDs from the private pre-update snapshot. If either has since been edited, reconcile it manually rather than discarding new work. A full SQL restore is a broader rollback and replaces later database work, so keep it for a deliberate restoration. Rolling back the theme/plugin code restores the previous presentation; the optional metadata can remain stored harmlessly.

If you downgrade the importer code, the two updated import baselines may be conservatively treated as edited records by that older version. Restore their pre-update baselines along with the optional fields when performing a full downgrade; do not force an overwrite. Use Core 0.9 with Theme 0.4 for the new layout and field support.

Nothing is deployed to Hostinger. Existing review/noindex states and the network limitation on live Spotify playback remain unchanged.

## Changed implementation and validation

- `plugin/about-song.php`: optional field contract, source/attribution validation, legacy read-time mapping, additive migration and administrator preview/apply action.
- `plugin/afterglow-core.php`: optional editor fields and sanitization; Core version 0.9.0.
- `plugin/research-handoffs.php`: optional-field import and provenance links, with backward-compatible baseline protection.
- `plugin/schemas/song-about.schema.json` and `scripts/validate-research.py`: formal field schema and cross-record/source validation. All 530 existing selections pass, including older packages that omit the fields.
- `plugin/data/astrid-s.json` and `plugin/data/clara-mae.json`: only new optional narrative fields added for the two examples; all earlier values remain intact. `plugin/data/about-song-review.json` holds unresolved proposals, outside the public import catalog.
- `theme/single-song.php`, `theme/assets/app.css`, and `theme/style.css`: one optional attributed editorial section; Theme version 0.4.0.
- Research CSV template, editorial guide, README, and the schema/migration/browser tests are updated.

Verification includes 29 disposable-database migration/schema/editor-save checks, 28 dedicated browser checks covering the five requested cases (four pass, the SHY Martin meaning is blocked by source verification), all 4,099 existing importer/protection checks, 63 discovery interactions, 40 functional checks, and 22 desktop/mobile accessibility audits. The media browser suite checks all 529 selected song pages and the existing artist/media/player safeguards.

The private preservation report compares **588 existing artist/song records** before and after: every post field and older metadata value is identical, with just the five optional fields and import baseline added to the two targeted songs. A repeated actual migration adds zero records and preserves both targets. There are no artist/song ID or slug changes, no new catalog entries, and no source-ID renumbering.

Current coverage across the 529 selected pages is **123 legacy artist statements**, **91 existing editorial readings**, **two explicit collaboration/process stories**, and **313 omitted About sections**. Those 313 pages can receive interesting, source-backed background later; a missing lyric meaning is not a mandatory research task or a reason to invent a narrative.
