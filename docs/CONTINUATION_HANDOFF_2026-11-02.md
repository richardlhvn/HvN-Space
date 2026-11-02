# Afterglow continuation handoff — 2 November 2026

This is the durable project context for continuing in a new chat. It summarizes the decisions, implementation, research, constraints and open work; it is not a word-for-word chat transcript. Read this before changing the site. Inspect the current repository and local state because this document is a dated snapshot, not proof that the owner's PC has every update installed.

## Current project and authority

- Repository: **https://github.com/richardlhvn/HvN-Space**, branch **main**. This is the existing **Afterglow** WordPress emotional-pop discovery website. Continue within its plugin/theme architecture; do not rebuild it in another framework.
- Feature baseline before this handoff: **`5fcc323`**, "Unify artist listening panels and highlight three starter songs". The handoff documentation is committed after that baseline; inspect `git log` for the current HEAD.
- Current versions: **Afterglow Core 0.9.0**, **Afterglow Theme 0.5.0**.
- Existing catalog: **53 artists**, **530 curated selections**, **529 distinct selected song pages**. These are editorial selections, not claims of Spotify's top-ten/popularity rankings.
- **Artist intake is paused at 53 by the owner.** Prior discussion of aiming for about 50 has been superseded. Focus on quality, missing data and the existing site unless the owner explicitly resumes additions.
- Local implementation and tests are complete through the artist listening layout. Source has been pushed to GitHub with the owner's standing authorization for these reviewed source updates. Nothing has been deployed to Hostinger; production deployment, destructive resets and overwriting owner work require separate authorization.
- The owner's **Windows/WSL/Docker database** and the **cloud development database** are separate. A GitHub download contains source and bundled research; it does not automatically update WordPress content on the owner's computer. Do not assume the owner has applied the latest update unless confirmed by evidence.

## Product direction and owner preferences

Afterglow should introduce listeners to new emotional-pop artists and songs through concise, informative editorial content, official listening previews and prominent next discoveries. The owner describes this as an infographic-style discovery experience: help visitors understand something interesting, sample music, then find another song or artist before leaving for Spotify.

The warm paper/lilac/rose visual design, local serif/sans fonts, abstract landscapes and existing WordPress architecture are accepted. The owner prefers action on agreed changes, concise progress updates and practical instructions. Avoid unnecessary confirmation questions for reversible work already requested. They are not a command-line expert: explain Windows versus PowerShell versus WSL explicitly when it matters, and run project commands from the actual project directory. Earlier setup problems included missing WSL, mismatched initial Ubuntu password entries, Docker integration/permission problems, and commands run from the home directory. Those problems were resolved; do not restart troubleshooting without current evidence.

Provide **actual inline screenshots** when showing changes. The owner has repeatedly been unable to access prior preview URLs or artifact downloads. Do not rely solely on an inaccessible preview service. Do not present generated mockups or simulated Spotify frames as a real live player. They often download GitHub ZIPs rather than using `git pull`; provide copying/restart steps suited to that workflow.

Do not invent biography facts, recording dates, Spotify IDs, artist emotions/intent, listening experiences, photo permissions, popularity rankings or artist endorsements. An unavailable field may remain empty. Distinguish artist statements, reporting and Afterglow interpretation. No full lyrics or long copied interviews. Retain uncertainty and provenance.

No external outreach to artists, labels, photographers, email or messaging services has been authorized or sent. Do not purchase licenses, deploy, delete Docker volumes, expose credentials, or publish private backups/worksheets merely to move work forward.

## Architecture and development environment

- Typical checkout here: `/workspace/HvN-Space`; a new environment may use another path.
- `plugin/`: Afterglow Core, custom `artist`/`song` post types, `genre`/`mood`/`country` taxonomies, metadata/editor controls, research import, media/rights synchronization, About the Song and discovery engine.
- `theme/`: classic PHP WordPress theme. Important files: `single-artist.php`, `single-song.php`, `discovery-ui.php`, `functions.php`, `assets/app.css`, `assets/app.js`.
- `plugin/data/`: individual canonical artist research JSON; source registries, catalog, aliases, provenance, media manifests and reviews. `plugin/media-assets/` includes the six approved originals, attribution/license records and evidence. Those photo licenses are separate from the code license.
- Docker Compose: official digest-pinned WordPress/PHP **8.3**, MariaDB **11.4**, WP-CLI. The default site is **http://localhost:8080**, admin **/wp-admin/**. Username **editor**; generated password is in ignored `.env`. Never print or commit it.
- The running project uses the Compose project name `afterglow`, database/WordPress named volumes, and theme/plugin read-only bind mounts. WordPress sets `WP_ENVIRONMENT_TYPE=local`, disables dashboard file editing and discourages indexing.
- `.env`, `.runtime/`, `node_modules/`, `test-results/`, `dist/` and ZIP artifacts are ignored. Keep private SQL/database snapshots/uploads outside GitHub/public web roots.
- `scripts/setup.sh` is for setting up the environment and guarded initial installation, not a destructive refresh. `scripts/backup.sh` makes a private SQL backup. `npm run package` builds theme/plugin installation ZIPs under ignored `dist/`.
- `npm start` / `docker compose up -d --wait db wordpress` starts an already configured site. `docker compose restart wordpress` reloads the local PHP container after source copying. Avoid `docker compose down -v`, database reset, or replacement of existing secrets/volumes.
- Older architecture/release docs contain historical milestones; descriptions of recommendations or embeds as out of scope are superseded by the current code and latest feature reports below.

## Current artist-page experience — Theme 0.5

The latest accepted artist-page layout is implemented and tested, not just a proposal:

1. One unified listening panel beside the artist introduction and short biography preview. This replaces the old giant artwork, separate "Let the music speak" external-link box, and separate player block.
2. Before loading, original abstract artwork carries the artist's name and **Listen to [artist]**. After an explicit click/keyboard choice, one official Spotify artist iframe replaces the preview. No autoplay parameter is added. The widget is not cropped, overlaid or harvested for separate artwork.
3. **44** profiles have verified existing artist URLs and exact artist players. **Nine** have no verified identity and retain a labeled Spotify search instead of an invented widget.
4. A small direct Spotify link remains available if loading fails. Volume help explains phone/system controls and Windows browser Volume Mixer. There is no fake volume slider or claim of controlling Spotify's cross-origin player volume.
5. The **six existing cleared portraits** appear as smaller uncropped identity images beside the artist names, with license/full-credit links. Other profiles have no identity portrait; no new photo license is required for the shared layout.
6. **Start with these songs** shows up to the first three published songs from the artist's existing saved curated order. Notes reuse useful existing descriptions, existing editorial themes/feelings or a neutral selection note. No new popularity rank or song meaning is inferred.
7. The complete current biography, full ten-song selection, collaboration links, citations and related-artist suggestions are retained below. `#artist-songs` remains the full-list anchor. Saving a starter song and its duplicate navigation row uses the same existing post ID and updates both buttons.
8. Mobile order: identity/introduction, listening panel, biography preview, discovery actions, starter songs, then full details. Native in-page navigation and direct Spotify links work without JavaScript; inactive player buttons are hidden in that case.

Details and local-copy instructions: **`docs/ARTIST_LISTENING_LAYOUT.md`**. Only the theme needs updating for this latest change; no data import/media sync is necessary. Use the current Core 0.9 for the earlier About the Song feature.

## Current song pages and discovery

- The main song view puts the Spotify listening panel beside the title, artist, editorial tags, short description and optional **About the Song** narrative. Research/source details come later. Earlier separate "Listening notes" and "In the artist's own words" headings are historical and should not be restored as mandatory empty sections.
- Before a track player loads, the panel shows a cleared artist portrait with credit or original abstract artwork with song/artist identification. On choice, the unmodified official track widget replaces it; direct links and volume help remain. No downloaded album cover or hosted audio.
- **174** distinct selected pages have exact track mappings. **355** lack a verified track URL: **273** use an artist-profile fallback and **82** a labeled Spotify search. The **175 supplied URLs** count also includes the shared duet's alternate reference; do not confuse that with 175 active players.
- Recommendations directly follow the main song information. A prominent next-song card, up to three results, feeling/theme controls, reasons and saved-song buttons use existing local metadata and curated links. Matching different artists are favored where available.
- With JavaScript, filters update only results/the next card; the same loaded iframe and song information remain intact. Errors preserve old results and offer a native full-page fallback. Explicit filters intersect, with an honest empty state rather than silently unrelated replacements. Native GET submission also works without JavaScript.
- Recent published song IDs travel in a bounded `ag_trail` URL parameter (up to eight); this avoids immediate repeats without an account or tracking profile. Saved collections are browser-local, at most 100 IDs. No visitor accounts, analytics or ads are active.
- Discovery excludes private/draft/orphaned records and unresearched legacy starters. Missing tags are not fabricated. The engine was also checked with a synthetic 500-song catalog; that is not a Hostinger production load-test claim.

Relevant files/reports: `plugin/discovery.php`, `theme/discovery-ui.php`, `theme/assets/app.js`, `docs/LISTENING_AND_DISCOVERY.md`, `docs/SONG_INFORMATION_LAYOUT.md` (historical headings), and the current About the Song report.

## About the Song — Core 0.9 / Theme 0.4 and later

Meaning is optional. Supported information includes **song-meaning**, **creative-process**, **artist-feelings**, **collaboration**, **inspiration**, **song-background** and **interesting-facts**; several may apply. Categories stay metadata so the section reads naturally.

Optional fields extend the existing `afterglow.research.v1` song object:

| Field | Purpose |
| --- | --- |
| `about_song` | Original plain-text narrative; null/empty allowed |
| `about_song_type` | Array of category identifiers |
| `about_song_source_urls` | Exact supporting HTTPS URL array |
| `about_song_source_ids` | IDs in that artist's existing source registry |
| `about_song_attribution` | `artist_statement`, `third_party_reporting`, `afterglow_editorial`, or `mixed` |

WordPress stores these as optional `_ag_…` metadata; list values are newline-separated in metadata and arrays in JSON. Source IDs restart per artist: never merge bare `src-001` identities across artists. For early imports whose full registry is only on the artist, the resolver uses the matching artist scope. Explicit URLs entered by editors represent an editorial review decision, not automatic factual verification.

Factual imports require usable matching registry evidence; unknown IDs, mismatched URLs, unread references and unconfirmed transcript-based artist claims are not promoted. Source `access_status: read` labels often came from supplied research and are not a fresh independent verification of every claim.

Existing artist-statement paraphrases map at read time without being copied over old fields. Current useful editorial text may map as Afterglow interpretation; generic missing-meaning boilerplate does not create an About section. Empty sections disappear. Current custom editor formatting and inline citations are preserved. Existing `artist_statement_paraphrase`, `artist_comment`, `meaning_type`, `editorial_notes`, descriptions, source IDs and uncertainty notes remain supported.

Two optional background additions were applied locally through **Songs → About the Song** / `ag_sync_about_songs(false)`:

- **Astrid S / NOTD — I Don't Know Why**: existing NOTD interview/catalog evidence, sources `src-035`/`src-023`; collaboration/vocal topline. Does not claim Astrid's undocumented feelings or lyric meaning.
- **KREAM / Clara Mae — Taped Up Heart**: producer interview `src-007`; remote production around Clara Mae's submitted vocal. Only the supported narrower detail is displayed.

The migration is additive, administrator-only and repeatable; existing About fields (including blank overrides), owner-edited/trash records and shared aliases are protected. It shares the research import lock. Old absent optional fields do not invalidate historic baselines. Repeat actual migration adds zero. Private before/after checks across **588 existing artist/song records** found no old post-field/metadata changes, only the five new fields plus baseline updates on the two target songs (cloud IDs 125 and 59; never assume those IDs match the owner's database).

Current selected-page coverage: **123 legacy artist statements**, **91 existing editorial readings**, **two explicit collaboration stories**, **313 omitted About sections**. Missing narrative is an optional enrichment opportunity, not a requirement to invent a meaning.

Pending proposals in **`plugin/data/about-song-review.json`** are deliberately outside public research imports:

- **SHY Martin — Forget to Forget**: the owner supplied a Billboard-linked paraphrase about returning to a relationship despite knowing it was a bad idea. Article: https://www.billboard.com/music/pop/shy-martin-forget-to-forget-video-8459467/ . Current source fetch failed at the cloud proxy with CONNECT 403. Keep this proposal pending until the original passage is checked; no unsupported artist statement was added to the live local page.
- **Clara Mae — Taped Up Heart**: the owner's additional piano-demo detail and Clara Mae's impressions of KREAM await an exact-passage check at https://www.euphoriazine.com/blog/2018/11/interviews-clara-mae-interview/ . The source was also proxy-blocked; retain the already-supported producer account until then.

Report: **`docs/ABOUT_THE_SONG.md`**. Implementation: `plugin/about-song.php`, `plugin/research-handoffs.php`, `plugin/afterglow-core.php`, `plugin/schemas/song-about.schema.json`, `scripts/validate-research.py`, `theme/single-song.php`. New working CSV: **`docs/research-templates/ABOUT_SONG.csv`**. There is no unattended spreadsheet uploader; working CSV/Sheets findings are reviewed before merging into canonical research.

## Import, deduplication and preservation

The canonical catalog is `plugin/data/research-catalog.json`; six batches comprise the original ten plus batches 3/4/5/6/7. `artist_intake.status` is `paused`.

Imports are explicit from **Artists → Import supplied research**. The named **Final artist intake — batches 5–7 (33 artists)** spans both supplied research dates; choosing only the latest date omits twenty artists. Choose **All research** only when earlier packages also need applying, such as on a genuinely fresh database. Existing edited/trash records are preserved using portable import baselines; IDs/permalinks are reused instead of deleting/reseeding.

`_ag_handoff_key` identifies an artist profile or artist-scoped song slug. `ag_handoff_state` and `_ag_handoff_baseline` protect current content/meta/taxonomies. Full raw records, nulls and scoped source registries remain stored. About fields participate in protection once present. Do not force overwrite simply because a row says Preserve.

**Little Bit Better** appears in Caleb Hearn's and ROSIE's curated selections but shares one richer existing page:

- Canonical key: `caleb-hearn/little-bit-better`.
- Canonical Spotify URL: `https://open.spotify.com/track/0GMs3jq70wmCo12pavj4st`.
- ROSIE supplied alternate: `https://open.spotify.com/track/7J3ay2nFMDL2rEi3YulAuw`.
- `plugin/data/research-aliases.json` records the explicit editorial-page reuse and uncertainty. The alternate is preserved as incoming research, not asserted to be identical master audio. Do not replace the canonical player or create a thin duplicate.
- Scoped imports require the earlier canonical record before any writes. Media sync skips the alias. ROSIE still has ten curated selections.

The final 33-artist import created **362 posts** (33 artists + 329 songs), updated no older posts and reused one selection. It preserved all 226 previously existing artist/song records and approved media files. The full catalog matches **582 distinct managed artist/song records** (53 + 529). One older published starter song and five drafts were retained; raw WordPress song counts therefore differ from selected-page counts. Do not delete those to force a total.

Media/rights/embed synchronization is separate: **Artists → Media and embeds**. Repeats reuse attachments and preserve owner edits, privacy text and credits. On a fresh catalog, import research before syncing media. Source copying alone does not import data.

## Catalog list — 53 artists

- Original ten: Astrid S; Sara Kays; Caleb Hearn; shy martin; Clara Mae; Elina; Chelsea Cutler; Mokita; Rosie Darling; Sasha Alex Sloan.
- Batch 3: Sadie Jean; Lyn Lapid; Alexander 23; Anson Seabra; BLÜ EYES.
- Batch 4: Hanniou; Loote; Rachel Grae; NERIAH; Fly By Midnight.
- Batch 5: Jessica Baio; Alex Sampson; Cole Norton; Etham; Avery Lynch; Katelyn Tarver; Sody; Emily James; Victoria Nadine; Andi.
- Batch 6: ROSIE; Nina Nesbitt; Henry Moodie; Jamie Miller; Natalie Jane; Zoe Wees; Leanna Firestone; Jillian Rossi; Sarah Barrios; Lauren Aquilina.
- Batch 7: Gracie Abrams; Lizzy McAlpine; Maisie Peters; Holly Humberstone; Ashley Kutcher; vaultboy; Dylan Conrique; Cian Ducrot; Dean Lewis; Em Beihold; Frawley; FLETCHER; Gabrielle Aplin.

Identity safeguards: ROSIE is distinct from Rosie Darling; Andi is tied to Spotify artist `7fEXIS5LgSLbCdzTePqe0V`; Avery Lynch's relevant Apple Music identity is **1511107715**, not unrelated **1822693624**. Preserve original display names/casing. Songs sharing a title across unrelated artists remain separate. Cover/performer credits must not be promoted to original compositions, including Cole Norton repertoire and Gabrielle Aplin's The Power of Love.

## Research packages, gaps and worksheet

The owner supplied research in multiple artist JSON/Markdown packages and ZIP batches. Canonical values, sources, review queues and archive provenance are in the repository. See **`docs/FINAL_ARTIST_INTAKE_2026-10-31.md`**, **`plugin/data/research-provenance-2026-10-31.json`** and `docs/research-review/`.

Batch 5 remains a draft pending review. Batches 6 and 7 are explicitly **`research_starter_not_publication_ready`**, not complete launch-ready biographies. Their scoped-source facts, dates and interpretations require review. Missing original release dates include 94 Batch 6 songs and 127 Batch 7 songs. Edition dates, deluxe/reissue/live dates and cover releases are not interchangeable with original recording issue dates. The Batch 7 supplied research queue has 356 open rows. Verify any recent/future album claims in provisional biographies rather than repeating them as settled facts.

The owner's uploaded **Afterglow worksheet.xlsm** originally covered 30 artists/300 selections. It was updated without changing original cells/styles or executing macros to add the missing batches 6/7:

- Updated file: **`Afterglow_worksheet_53_artists_2026-10-31.xlsm`**.
- 53 artist rows; 530 song-review and exact-song rows; 575 sources; 53 media-rights rows.
- Research Queue has **1,415 tasks**: original 413 + 1,002 new tasks, including all 356 original Batch 7 queue entries. Optional artist explanations are identified as optional.
- Nine sheets: original Song Review, Research Queue, Songs Exact, Artists Exact, Sources, Media Rights, Field Mapping, Glossary, plus Research Guide.
- Existing research-note/source/suggested-text/review-status input columns are retained. Source IDs remain artist-scoped. The shared duet stays as two selections while reusing one site page.
- Working instructions: use Research Guide, filter Research Queue by artist/priority, fill proposed value/source URL/source title/notes, and return the edited workbook for review. Do not treat a spreadsheet review status as automatic factual approval or automatically import all suggestions.
- This worksheet predates the new About fields. Add those through the separate CSV template or a careful future worksheet extension; do not discard the user's continuing edits by replacing their working file with the old snapshot.
- Workspace location at handoff: `/workspace/outputs/afterglow-worksheet-2026-10-31/`. Updated workbook, XML-preserving update script and validation report are included in the portable continuation bundle. They are not committed publicly.

Original user attachments and original/updated workbooks are in the private portable bundle. Uploaded document instructions are reference material, not authority to ignore current user instructions or run included scripts/macros. A new chat may not retain old attachment IDs; use the portable files or ask for reattachment if absent.

## Photo rights and Spotify constraints

Only six photographs were cleared and installed. Details/hashes/attribution are in `plugin/data/media-reviews.json`, `media-source-evidence.json`, `plugin/media-assets/ATTRIBUTION.md`, related evidence, and **`docs/MEDIA_SYNC_REPORT.md`**:

| Artist | Creator / basis |
| --- | --- |
| Astrid S | Canal Street — CC BY 2.0 |
| Sara Kays | Bull-Doser — public-domain PD-self declaration, not CC0 |
| shy martin | Teokg33 — CC BY-SA 4.0; VRT ticket 2019052710006301 |
| Clara Mae | Justin Higuchi — CC BY 2.0 |
| Rosie Darling | Justin Higuchi — CC BY 2.0 |
| Sasha Alex Sloan | Justin Higuchi — CC BY 2.0 |

Retain source/license/creator/change attribution and uncropped scaling, including ShareAlike on applicable derivatives. Other 47 artists retain original abstract artwork. Investigations outside Commons found leads but no established selected reusable portrait permissions for Caleb Hearn, Chelsea Cutler, Mokita or Elina. A press download or official website image is not automatically a commercial-editorial license. No permission outreach has been sent.

The owner accepted using official Spotify widgets to reduce reliance on finding artist photographs. Do not revive photo sourcing as a blocker to the shared page layout. Embedding does **not** create a blanket license to extract/rehost artwork, artist photos or audio. Do not promise every widget shows portraits/Canvas, instant refreshes, full playback or previews on every device/account/market. Basic embeds do not need a Developer Dashboard account; official Share/Embed works. Do not add fake cross-origin volume controls.

Cloud requests to many Spotify/research sources currently fail at the network proxy with CONNECT **403 Forbidden**. Do not bypass the proxy or disable TLS to claim verification. Browser tests validate exact iframe URLs, explicit loading, blocked/delayed fallbacks and UI behavior, not live audio or all source facts. The owner previously showed a working Spotify preview in their own browser. Current terms/permissions and actual production behavior still require appropriate review at launch.

## Latest validation and screenshots

These tests passed at the feature baseline; do not rerun every suite just to load context. Run checks appropriate to actual new changes:

| Check | Latest recorded result |
| --- | --- |
| `node tests/artist-listening-browser.mjs` | 665 checks; all 53 artist profiles, 44 players/9 fallbacks |
| `node tests/media-browser.mjs` | 2,798 checks; 529 selected pages, 53 profiles, player/photo/citation safeguards |
| `node tests/discovery-browser.mjs` | 63 interaction checks |
| `node tests/about-song-browser.mjs` | 28 checks; four requested cases pass, SHY source case explicitly blocked |
| `bash scripts/test-about-song.sh` | 29 schema/migration/protection/editor-save checks in disposable namespace |
| `bash scripts/test-handoffs.sh` | 4,099 importer/protection checks in disposable namespace |
| `bash scripts/test-media.sh` | 607 media/rights checks at the final-catalog release |
| `npm test` | 40 functional browser checks |
| `npm run test:a11y` | 22 desktop/mobile accessibility audits |
| `python scripts/validate-research.py` | Catalog/reference/date/URL/alias/optional-field validation passed |
| JSON Schema fragment | All 530 supplied song selections passed, including old packages without About fields |

Latest actual previews under ignored `test-results/`:

- `artist-listening-preview-final.png`: Caleb Hearn introduction/listening panel and the three starter cards.
- `artist-listening-sara.png`: small cleared identity portrait and unified artist panel.
- `artist-listening-mobile.png`: mobile artist page.
- `about-song-collaboration.png`: Clara Mae song with the supported process narrative.
- `about-song-mobile.png`, `discovery-desktop-preview.png`, `discovery-mobile.png`, `media-credits-desktop.png` and test JSON summaries.

The handoff's portable bundle includes selected previews and nonsecret test summaries. It excludes private database snapshots/reports, credentials and node_modules. Source/research/license records are backed up by GitHub; the current cloud database/uploads were additionally backed up privately before this handoff. Those private backups are not automatically portable to a new workspace.

## Most recent work and sensible continuation

| Commit | Completed work |
| --- | --- |
| `5fcc323` | Artist unified listening panel, smaller portraits, three starter cards, full biography/list retained; Theme 0.5 |
| `6827f83` | About the Song optional schema/editor/import/legacy mapping; Core 0.9 / Theme 0.4 |
| `c3e84eb` | Song information beside Spotify; recommendations below; historical Theme 0.3 headings |
| `c82688c` | Final 33-artist intake, alias protection and pause at 53 |
| `0baa616` | Guided song discovery, local preview visuals and volume help |
| `c84db51` | Batches 3/4 brought the catalog to 20 artists |

There is **no new feature task requested yet** in the new chat. First load this context, inspect the current versions and briefly confirm readiness; then wait for the owner's next request. Do not redo completed imports, recreate the site, restart artist sourcing or deploy while merely loading context.

Likely next work, only when requested: review returned worksheet findings; verify the two pending source passages; enrich optional About narratives and missing track/artist identities/dates; refine existing discovery/editorial content; later prepare Hostinger staging/launch with backups and explicit production approval. Contact/privacy/copyright details and unfinished research flags still need owner/editorial completion before public launch.
