# Batches 3 and 4 — 29 October 2026

The two uploaded archives add ten artists and one hundred curated song selections to the existing architecture. The site now contains **20 managed artist profiles and 200 distinct selected song pages**. The original six licensed photographs and their rights register are unchanged; fourteen artists use abstract placeholders. No production deployment is included.

| Collection | Artists | Songs | Supplied exact Spotify track links | Missing track links |
| --- | --- | ---: | ---: | ---: |
| Batch 3 | Sadie Jean, Lyn Lapid, Alexander 23, Anson Seabra, BLÜ EYES | 50 | 26 | 24 |
| Batch 4 | Hanniou, Loote, Rachel Grae, NERIAH, Fly By Midnight | 50 | 16 | 34 |
| New total | 10 artists | 100 | 42 | 58 |

These are curated introductions, not current Spotify popularity rankings. The source JSON files remain unchanged from the independent artist files in the archives. Both combined wrappers were compared against those files, and the supplied validation counts match. Each package's source IDs are scoped to its artist; identical `src-001` strings never merge unrelated references. Raw research records retain intentional null values, uncertainties, exact credits, supported claims, and release-date precision and edition context.

## Apply to an existing Windows/WSL site

1. Keep Docker Desktop running. In WSL, open the **existing project folder** containing `compose.yaml` and your `.env`.
2. Run `bash scripts/backup.sh`. Keep that private database backup and a copy of your current theme/plugin source. If there are uploaded images, also back up the Docker WordPress volume's `wp-content/uploads` folder. A missing uploads folder on a site that has never uploaded images needs no media backup.
3. Download the latest `main` ZIP from https://github.com/richardlhvn/HvN-Space and extract it into a separate temporary folder. Copy its **plugin** and **theme** folders over those folders in your existing project. Merge any local code edits you made. Keep `.env`, `.runtime`, your existing `compose.yaml`, database, and uploads. Do not delete Docker volumes.
4. Run `docker compose restart wordpress` from your existing project folder.
5. Sign in at http://localhost:8080/wp-admin/ (or your configured port). Open **Artists → Import supplied research**. The newest collection, **2026-10-29**, is selected by default. It contains **10 artists and 100 songs**. Review the planned actions; earlier packages are excluded unless you explicitly select **All research**.
6. Keep **Show imported pages on the site for review** checked for a local preview, then select **Apply supplied research**. New pages retain noindex. Repeating the import reuses the same artist/song IDs. Edited and trashed records remain protected.
7. Open **Artists → Media and embeds**, inspect the dry run, and select **Sync checked mappings and credits**. After the full catalog exists it should match **220 records**, retain six licensed photo attachments, leave fourteen placeholders, and store exact mappings only where supplied track URLs exist. If your installation has not imported earlier research, review **All research** first so the full catalog exists before media synchronization.
8. Refresh the directories with **Ctrl + F5**. Artists span two directory pages. Inspect new profiles, song pages, citations, collaboration links, and the missing-track fallback. Test the actual Spotify player from your browser.

No npm dependency changes or database reset are required. The cloud preview database is separate from your Windows database: downloading the code does not automatically import these records on your PC.

## Spotify, artist statements, and images

The 42 supplied exact links have strict origin/path/recording-ID validation and use unmodified official players in the song hero position. The 58 missing track links remain null in their research records, do not generate iframes, and offer the artist's verified Spotify profile instead. No track ID is guessed from a title, and no Spotify cover or audio is downloaded.

Attempts to open all 42 new track URLs from this cloud environment were rejected by the network proxy with **403 Forbidden during CONNECT**. This is a cloud access limitation, not evidence that a recording is unavailable. Recording identity and live playback were not independently reconfirmed here. Source-backed records remain marked for editorial review, and live player behavior must be checked in the owner's browser or private staging.

Fourteen sourced song-specific paraphrases from these batches are displayed separately from Afterglow interpretation. Loote's `85%` record includes a non-null artist-statement field that explicitly describes a reviewer interpretation and provides no artist-statement source IDs; it remains preserved in raw research but is withheld from the artist's-own-words section. Missing statements remain missing. Album/EP edition dates are not rewritten as original single release dates.

No commercial-editorial photo permission is established for any of these ten artists. All retain placeholders. The prior approved media manifest, review register, source evidence, attribution file, and six original photo binaries remain byte-for-byte unchanged.

## Collaborations and duplicate handling

The incoming selections contain no repeated exact Spotify recording URLs and no duplicated collaboration selections. They produce exactly 100 new song pages. The existing single-primary-artist model is retained, with source credits recording all performers. Known credited performers link to their existing artist profiles. An **Also appears on** section links a collaborator to the same existing song page without adding another post or changing their ten-song starter selection.

Examples include Loote and Anson Seabra's **This Is the Last Song About You**, Fly By Midnight and Rachel Grae's **Different Lives**, and Alexander 23 with Chelsea Cutler on **Lucky**. Related-artist recommendations are labeled editorial. Unknown external contributors remain plain credits rather than invented artist profiles. Rachel Grae's **Colorblind** and Mokita's **Colorblind** have different credits and recording IDs and correctly remain separate.

The loader rejects a repeated exact recording URL before writes, so a future shared-selection package requires explicit review rather than silently creating two pages. Upserts otherwise use stable artist/song keys, with checks for existing IDs, titles scoped to the artist, manual changes, and Trash.

Directory name filters search titles so a new biography merely mentioning Sara Kays does not appear as a Sara Kays name match. Global site search continues searching full content. Unicode names such as BLÜ EYES and sourced country filters are checked independently.

## Local result and verification

Database/uploads backups preceded the import. The dry run planned **110 creations, zero existing updates**. The actual scoped import created **10 artists and 100 songs**, preserved all **116 pre-existing artist/song records** (including legacy records and drafts) exactly, reused all six photo attachment IDs, and added 42 embed mappings. Repeating the import/sync created no duplicate posts or attachments and required no further embed writes. Existing content, URLs, edits, privacy text, and Media Credits were retained.

- All twenty JSON packages passed catalog, source-reference, duplicate, URL, null, date-precision, and scope validation.
- **1,310** importer/protection checks and **243** media/permission checks passed in disposable database namespaces.
- **1,084** browser checks cover all 200 selected song pages, directory pagination, twenty profiles, six credited photographs, fourteen placeholders, 142 official player mappings, 58 missing-track fallbacks, collaboration links, citations, blocked/slow embeds, and mobile layout.
- **40** existing/expanded functional checks, **12** desktop/mobile accessibility audits, and **10** research-admin scope/nonce/form/media-review checks passed. The normal admin form selected only the latest collection and repeated the import without duplicates. Automated checks do not independently establish factual truth or audio availability.

Private reports and backups are in ignored `.runtime/`; screenshots are in ignored `test-results/`. They must not be copied into a public web root. Commit the source/data/tests/docs only. Hostinger production remains subject to the separate owner-approved release checklist.
