# Final artist intake — 31 October 2026

The three uploaded archives are the correct, readable packages for **33 distinct additional artists**. Together with the previous twenty, the registered catalog now contains **53 artists**. Artist additions are paused after this intake at the owner's request; no other artists were sourced or added.

| Archive | Artists | Curated selections | Supplied track URLs | Research status |
| --- | ---: | ---: | ---: | --- |
| `afterglow_batch5_ten_artists_2026-10-30.zip` | 10 | 100 | 20 | Research drafts pending review |
| `Afterglow_Batch6_10_Artists_Research_Starter_2026-10-30.zip` | 10 | 100 | 6 | Research starter; not publication ready |
| `Afterglow_Batch7_Complete_Package_REDOWNLOAD.zip` | 13 | 130 | 7 | Research starter; not publication ready |

Batch 5: Jessica Baio, Alex Sampson, Cole Norton, Etham, Avery Lynch, Katelyn Tarver, Sody, Emily James, Victoria Nadine, Andi.

Batch 6: ROSIE, Nina Nesbitt, Henry Moodie, Jamie Miller, Natalie Jane, Zoe Wees, Leanna Firestone, Jillian Rossi, Sarah Barrios, Lauren Aquilina.

Batch 7: Gracie Abrams, Lizzy McAlpine, Maisie Peters, Holly Humberstone, Ashley Kutcher, vaultboy, Dylan Conrique, Cian Ducrot, Dean Lewis, Em Beihold, Frawley, FLETCHER, Gabrielle Aplin.

## File and data verification

All archives extracted without unsafe paths or unreadable/corrupt entries. Batches 5/6's independent artist files match their combined wrappers. Batch 7 supplies one combined thirteen-artist package; it was split into the existing individual canonical-file layout without changing field values. Source IDs resolve within each artist's own registry, and no artist slug overlaps the earlier twenty or another incoming artist. Names and documented identities are retained, including ROSIE versus Rosie Darling, FLETCHER, Frawley, the intended Andi Spotify identity, and Avery Lynch's correct Apple Music identity.

Wrapper/archive filenames, SHA-256 hashes, integrity checks, status, and counts are retained in `plugin/data/research-provenance-2026-10-31.json`. CSV review/backlog files are retained separately in `docs/research-review/`, with line endings normalized to LF for the repository; parsed cell values and UTF-8 BOMs are unchanged, and source/repository hashes are recorded. No Google Sheets consolidation or external messages were performed. The Batch 7 backlog contains **356 open research rows**. All new images remain placeholders; there are now six existing licensed portraits and forty-seven placeholders. Prior rights/credit files and original photo binaries are unchanged.

## One shared duet, no thin duplicate

Caleb Hearn's existing **Little Bit Better**, credited to Caleb Hearn and ROSIE, also appears in ROSIE's new starter selection. Both sources describe the 23 February 2024 single, but supply different Spotify IDs. We preserve the established, richer song page and its existing player, sources, interpretation, date context, and artist statement. ROSIE's ten-song selection links to that same page.

The incoming record, alternate Spotify URL, and its artist-scoped source registry are retained separately for review. No claim is made that the two Spotify IDs have identical master audio. The explicit mapping and reason are in `plugin/data/research-aliases.json`; runtime validation checks the named title, performers, and supplied URLs. A missing canonical record blocks a scoped import before writes, rather than creating a blind duplicate. Media synchronization skips the alias row so it cannot overwrite richer canonical metadata.

This yields **530 curated selections across 529 distinct selected song pages**, rather than 530 duplicate-free recording pages. There are **33 new artists and 329 new song pages**: **362 created posts**, zero updates to existing posts, and one shared selection reused. The old starter song and five older drafts remain; raw WordPress song counts include the legacy starter.

## Known research gaps

- Across the full catalog, **175 supplied track URLs** include the shared duet's alternate reference. **174 active exact track mappings** serve distinct pages; **355 selected song pages** have no verified exact recording link. Of those, 273 offer a supplied artist profile and 82 offer an explicitly labeled Spotify search. No track or artist ID is invented.
- Artist profiles have 44 supplied Spotify identities; nine remain absent. Unknown fields stay null in raw research. Null song descriptions receive only neutral navigation text in the page header, not a guessed song meaning or an automatically generated excerpt of research warnings.
- The incoming batches contain **45 sourced artist-statement paraphrases**, retained with their scoped provenance and separate from editorial interpretation. They remain subject to source review; this import does not certify every factual claim. Earlier withheld unsupported/transcript-based statements remain withheld.
- Batch 5's catalog dates often describe edition years, not independently established first releases. **94** Batch 6 songs and **127** Batch 7 songs have no established release date. Missing dates are not filled from albums, deluxe editions, live versions, or covers. Cover/performer credits remain as supplied; Cole Norton's repertoire and Gabrielle Aplin's *The Power of Love* are not promoted to original compositions.
- Batches 6/7 retain `research_starter_not_publication_ready` in database metadata. Profiles and song pages display **Research starter — not publication ready**, keep the review/noindex flags, and preserve their uncertainty lists. These are local review pages, not a claim of launch readiness.
- Live checks of the 33 incoming track URLs were rejected by the cloud network proxy at CONNECT with 403 Forbidden. The links are syntactically validated and supplied as recording-specific research; their current recording identity and playback could not be independently reconfirmed here. Check them from the owner's browser/private staging as part of verification.

## Apply on your existing Windows site

1. Keep Docker Desktop running. Open your existing project folder in WSL and run `bash scripts/backup.sh`. Preserve a private copy of your current theme/plugin source and uploads.
2. Download the latest `main` ZIP from https://github.com/richardlhvn/HvN-Space. Extract separately and copy its **plugin** and **theme** folders over those folders in your existing project. Merge any local code edits. Keep `.env`, `.runtime`, `compose.yaml`, database, and uploads; do not delete Docker volumes.
3. Run `docker compose restart wordpress` from your existing project folder.
4. Sign in to http://localhost:8080/wp-admin/ (or your configured port). Open **Artists → Import supplied research**. The default **Final artist intake — batches 5–7 (33 artists)** includes both research dates. Review all **363 selection targets**: normally 362 creations and one reused shared song on a twenty-artist installation. Applying only the latest date would omit twenty artists, so use the named final-intake collection.
5. Keep **Show imported pages on the site for review** checked for your local preview and apply the selected collection. It retains noindex and labels starters. Earlier packages are excluded. Edited/trash records stay protected. If your Windows installation has fewer than the previous twenty artists, review/import the missing earlier collections or **All research** to reach the complete registered catalog. A missing earlier duet blocks the scoped import before writes; do not create another copy manually.
6. Open **Artists → Media and embeds**, review, and synchronize. A complete catalog has **582 distinct matched artist/song records**, one shared selection, six reused photo attachments, forty-seven placeholders, and 174 active track mappings. Repeating the operation must add no posts or attachments.
7. Refresh with **Ctrl + F5**. The artist directory has five pages. Check ROSIE's shared duet, starter labels, native Spotify searches for missing identities, citations, next-discovery/filter navigation, and mobile layouts. Production deployment remains separate and unapproved.

No npm dependency changes or database reset are needed. The cloud preview database is separate from your Windows installation; downloading source does not automatically import these records on your PC.

## Local validation and preservation

Database and upload backups preceded changes. The dry run planned 362 creations and one reused selection, with no existing updates. The actual import matched that plan. All **226 pre-existing artist/song records** and approved media files were compared before and after and remained unchanged. Repeat import/media synchronization created no duplicates, new attachments, extra embed writes, or thin alias overwrites.

JSON/reference/date/URL/collision validation, disposable importer/media tests, and full local browser checks cover the expanded catalog. Private reports/backups stay under ignored `.runtime/`; screenshots stay under ignored `test-results/`. Only source, canonical research, provenance, review queues, tests, and documentation belong in the repository. The intake pause is also stored in `research-catalog.json` and displayed in administration.

Validation passed: **4,099** importer/protection checks, **607** media/permission checks, **2,798** full-catalog browser checks, **54** listening/discovery interactions, **40** functional browser checks, **22** desktop/mobile accessibility audits, and **11** admin scope/nonce/repeat-import checks. These checks establish import and interface behavior, not independent verification of every research claim or live audio availability.
