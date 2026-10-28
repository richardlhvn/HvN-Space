# Local media/data synchronization plan — 28 October 2026

The owner authorized local implementation and tests, not production deployment or external publication. Do not push these changes to GitHub until the owner approves the completed result.

## Inputs and current state

The handoff was extracted after checking archive traversal, symlinks, file count, and uncompressed size. All listed SHA-256 inventory hashes match. `README_START_HERE.md`, `CODEX_IMPLEMENTATION_PROMPT.md`, media sync instructions, credits, image sourcing notes, manifest, canonical batch, and supplied scripts were inspected. The supplied validator passes after arranging its expected relative paths in a temporary validation directory.

Use the canonical batch's `records` once. Its ten records match the individual updated packages. Compared with the installed collection, all song and biography data is unchanged; six artist image objects and six source registries are updated. Do not import the individual packages again as new posts.

Before changes, database and uploads backups were saved privately under `.runtime/backups/`. The preflight report is `.runtime/reports/media-preflight.json`. Match managed artist/song keys, retain IDs/slugs, preserve manual edits and Trash, and leave older unused starter records alone. A raw WordPress song count can include retained legacy demos; validation must identify the 100 managed selected songs rather than delete user records to force a total.

## Apply sequence

1. Validate ten artist slugs, one hundred artist-scoped song keys, source references, null/empty fields, recording URLs, and manifest embed mappings. Save a no-write dry-run report.
2. Refresh only importer-owned, unchanged records. Store full supplied records and source registries, including image candidates, while keeping candidate permission separate from verified attachment permission. Preserve statuses and review/noindex flags; do not reset editor-approved indexing settings silently.
3. Install the hundred official embed mappings. Derive and validate HTTPS URLs against exact track IDs; preserve working Spotify links. Use explicit load-on-click and lazy iframe loading so no Spotify request occurs before consent. Keep an accessible link available if JavaScript, networking, or embedding fails. Do not download covers/audio.
4. Recheck each candidate's current Commons page/API, author, license URL, status, and source caption. Only a live-verified, documented, permitted candidate can be downloaded as an image and imported. Deduplicate attachment identity by source and verified file hash, retain credit/license/change metadata, and preserve full-frame images without cutting signatures. Apply the same CC BY-SA 4.0 license to SHY Martin derivatives.
5. Keep Caleb Hearn, Chelsea Cutler, Mokita, and Elina on placeholders unless the owner approves a newly cleared alternative. Research official/label/photographer leads outside Commons; record exact observed permissions or access failures. A press image without an explicit grant remains a lead.
6. Create a local Media Credits page linked from the footer and image credit affordances. Candidate licenses must be labeled as candidates, not credits for photographs actually used. Show actual attribution only for imported photos; keep the correction/contact route.
7. Exercise actual database sync twice; confirm stable record and attachment counts, no orphan relationships, edit preservation, sources, credits, desktop/mobile layouts, click-to-load/blocked embeds, and accessibility. Produce screenshots, a rights status table, and a Hostinger release checklist.

## Access diagnosis and resolution

Initial Commons, Creative Commons, Spotify developer-policy, and press-source requests returned proxy HTTP 403. Network domain additions were saved as a draft. Subsequent read-only checks found the official Wikipedia shared-file API and original Wikimedia image destination available. Current Commons captions, creators, licenses, categories, and SHY Martin's confirmed VRT permission ticket were read through that official API. Each downloaded original matched its Commons SHA-1 and received a recorded SHA-256. Six photographs were then staged with full credits and uncropped display derivatives; the four unresolved placeholders remain unchanged.

The Commons reuse policy, Creative Commons BY-SA deed, and Spotify widget terms also became readable and were reviewed. Some specific press-lead paths still return proxy 403. Accessible official artist/label pages did not establish new commercial-editorial photo grants for the four remaining artists. Synthetic fixture tests exercise unsafe/unverified cases and do not count as artist photographs. See MEDIA_SYNC_REPORT.md for the final observed status and release gate.
