# Research and media synchronization — 28 October 2026

## Scope and safeguards

Implemented in the existing Afterglow theme/plugin and local Docker WordPress instance. No redesign, production deployment, purchase, or permission-request message was performed. After reviewing the local result, the owner approved publishing the reviewed source update to GitHub. Production deployment remains unapproved.

The complete handoff's inventory hashes matched. The supplied validator passed: ten artists, one hundred artist-scoped song keys, six photo candidates, four placeholders, and one hundred official embed mappings. The canonical batch and individual updated records match; only the canonical records were consumed. Biography/song prose is unchanged from the existing catalog. Six image objects and source registries were updated in the supplied handoff.

Database and upload backups were made before changes under ignored `.runtime/backups/`. Current reports are under `.runtime/reports/`. These contain private runtime state and must not be committed or uploaded to a public web root.

## Dry run and actual result

| Operation | Planned | Applied |
| --- | ---: | ---: |
| Existing managed artist/song records matched | 110 | 110 |
| New artist/song posts | 0 | 0 |
| Duplicate managed keys | 0 | 0 |
| Supplied-record metadata updates | 6 | 6 |
| Full artist source registries saved | 10 | 10 |
| Exact official track embed mappings saved | 100 | 100 |
| Cleared artist attachments imported | 6 | 6 |
| Unresolved artist placeholders retained | 4 | 4 |
| Media Credits pages created | 1 | 1 |
| Unedited starter privacy paragraphs updated | 1 | 1 |

Repeated data/media synchronization creates no additional artist/song posts or attachments. Six existing attachment IDs are reused. Artist titles, biographies, excerpts, publication status, prior IDs, and permalinks were preserved. One legacy demo song and five older drafts were retained rather than deleted to force a raw post count of 100. Validation counts the 100 selected managed songs.

## Photograph decisions

Current official Wikimedia shared-file metadata was inspected for captions/categories identifying the editorial subject, creator, license, and permission status. Original image URLs matched the handoff. Downloaded JPEG bytes matched Commons's SHA-1 and were additionally hashed with SHA-256. Original files are in `plugin/media-assets/`; exact sources, licenses, hashes, and change details are in `plugin/data/media-source-evidence.json`, `media-reviews.json`, and `media-assets/ATTRIBUTION.md`.

| Artist | Creator | Copyright basis | Local result |
| --- | --- | --- | --- |
| Astrid S | Canal Street | CC BY 2.0 | Imported and credited |
| Sara Kays | Bull-Doser | PD-self declaration | Imported; voluntary credit retained |
| shy martin | Teokg33 | CC BY-SA 4.0; confirmed VRT ticket 2019052710006301 | Imported; scaled files retain ShareAlike |
| Clara Mae | Justin Higuchi | CC BY 2.0 | Imported and credited |
| Rosie Darling | Justin Higuchi | CC BY 2.0 | Imported and credited |
| Sasha Alex Sloan | Justin Higuchi | CC BY 2.0 | Imported and credited |
| Caleb Hearn | No cleared selected photo | Permission unresolved | Abstract placeholder retained |
| Chelsea Cutler | No cleared selected photo | Permission unresolved | Abstract placeholder retained |
| Mokita | No cleared selected photo | Permission unresolved | Abstract placeholder retained |
| Elina | No cleared selected photo | Permission unresolved | Abstract placeholder retained |

No face recognition was used; identity attribution follows source captions and Commons categories. Copyright license confirmation does not itself settle every personality, publicity, moral-right, or endorsement issue. Images are presented only as independent editorial identification, without advertisements implying endorsement. Before commercial/public release, review that actual use in the owner's jurisdiction. No additional restriction is imposed on CC BY-SA image files by the site's code license.

WordPress generates uncropped scaled sizes. The full photo frame is retained on cards and profiles, including on hover; signatures are not intentionally cropped. The credits ledger links creator/source/license, original photographer pages where provided, and changes. Cards link to each photo's section on Media Credits. Other illustrations remain Afterglow originals.

## Research outside Commons

Sources were investigated beyond Wikimedia; accessible images were not assumed to be licensed merely because they were visible or downloadable.

| Artist | Sources investigated | Observation and next step |
| --- | --- | --- |
| Caleb Hearn | calebhearnmusic.com; supplied Nettwerk signing announcement | Official home page responds, but no explicit reusable portrait grant found in accessible material. The specific label announcement returned proxy 403. Hunter Cohen is a lead from the supplied notes, not a newly established permission. Ask the rightsholder through a verified contact if the owner wants to pursue it. |
| Chelsea Cutler | chelseacutler.com; supplied Universal Music Canada announcement | Official Mercury/UMG page responds; no independent commercial-editorial photo grant located. The specific label announcement returned proxy 403. The old Commons TIFF's unresolved permission provenance was not promoted to approval. |
| Mokita | Nettwerk's Happiness announcement | Accessible label press content credits Annie Noelker and includes a download lead, but no explicit reusable commercial-editorial photo grant was established. A download link alone is not permission. |
| Elina | elinasongs.com; supplied press article | Artist-branded storefront responds, but no established portrait reuse grant located. The specific press article returned proxy 403. Hampus Hjellström remains a photographer lead from the handoff, not a confirmed license. |

The existing four `publish_image: false` statuses remain unchanged. No selected alternative was independently cleared, downloaded, or installed for them. No artist, label, or photographer has been contacted on the owner's behalf. Better alternatives for the six cleared candidates were optional; the supplied, verified candidates were retained rather than substituted with unlicensed press assets.

## Spotify and editorial distinctions

One hundred official track embed URLs are validated against the exact recording IDs; artist pages also offer official artist widgets when a valid artist URL exists. No cover images, audio, or Spotify profile photos are downloaded. Listing/song-card art remains original abstract artwork.

Widgets require an explicit Load Spotify player action and use lazy iframes. Before that action, pages make no Spotify requests. The widget itself is unaltered; no overlay or recoloring is applied. A direct Spotify link remains available when JavaScript is disabled or a player is blocked. The starter privacy notice was updated only when it matched its known original hash; customized owner text is preserved and flagged for review.

Spotify's current published widget terms were read at https://developer.spotify.com/documentation/embeds/terms (page states version 2.0). The terms allow widgets on controlled websites, prohibit modifying/obscuring widgets or saving covers/audio, require privacy/cookie disclosures, and distinguish commercial use of the widget from websites containing advertising. They expressly do not prohibit embedding on an advertising-containing website. This is not certification of future Journey/ads compliance: before ads are added, ensure they do not obscure players or transfer Spotify-derived data to ad networks, and review the actual integration and current terms.

Song pages label **Afterglow interpretation** separately from sourced artist paraphrases. The unconfirmed automatic-transcript statement remains withheld. Source references, null values, date precision/context, and collaboration credits remain intact.

## Validation and previews

- Supplied offline validator and inventory SHA-256 checks passed.
- 445 supplied-research import checks passed in a disposable namespace.
- 133 media/permission-gate checks passed, including anonymous access rejection, unverified photo blocking, attachment deduplication, ShareAlike requirements, privacy/credits edit preservation, and safe iframe origins. The small synthetic fixture is deleted and does not count as an artist image.
- 450 media browser checks passed, covering all 100 song pages, ten artist profiles, six decoded local photos, four placeholders, no orphaned recommendations, source sections, credit access, explicit/lazy/blocked/slow embeds, keyboard activation, and mobile overflow checks. Test summaries and screenshots are in `test-results/`.
- 38 existing functional browser checks and 12 desktop/mobile accessibility audits passed against the actual preview. Pages make no third-party resource requests before an explicit player choice.
- The actual admin form, nonce rejection, repeat sync, and six verified/four placeholder statuses were checked. A representative live widget document request returned a cloud-proxy HTTP 403, and the cloud browser could not establish live playback. Frame creation, exact URLs, blocked fallback, and delayed loading are tested separately; audio playback was not attempted. Confirm real player behavior from the owner's browser and Hostinger staging. No production playback, Hostinger performance, or geographic streaming availability is guaranteed.

Previews: `media-directory-desktop.png`, `media-artist-desktop.png`, `media-artist-mobile.png`, `media-song-desktop.png`, `media-song-mobile.png`, and `media-credits-desktop.png` in `test-results/`.

## Owner review and release gate

An additive cloud-network configuration draft was saved for source and license research; it was not published. Review, save, and publish that environment draft if these access settings should persist. This concerns the development environment only and does not deploy the website. Source accessibility varied during research; the successful checks and remaining proxy failures are recorded above.

Review the screenshots, attribution, four placeholders, source uncertainties, privacy/contact/copyright text, and the Hostinger checklist. The owner's subsequent approval covers publishing the reviewed source update to GitHub, not production deployment. For a local installation, follow `LOCAL_MEDIA_UPDATE.md`; retain the existing `.env`, database, and uploads. Use Artists → Media and embeds to inspect the dry run and sync checked mappings/credits. If the catalog is missing, import supplied research first. Never reset the database to apply this update.
