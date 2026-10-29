# Listening and discovery — 29 October 2026

Core 0.7 and Theme 0.2 implement the discussed discovery direction in the existing WordPress architecture. This is a source update: no research data, featured images, rights decisions, biographies, song explanations, or publication statuses are rewritten.

## Visitor experience

- Before Spotify loads, the song listening panel shows its artist's existing verified portrait, including attribution, license, uncropped scaling information, and full-credit link. If no cleared portrait exists, it shows original abstract artwork with the current song title and artist. Original artwork is explicitly distinguished from official cover art.
- **Listen to this song** loads one unmodified official Spotify iframe in place of that visual. The widget is not cropped or overlaid. Spotify is contacted only after this choice, with no autoplay URL parameter. The direct link stays available when an embed is blocked. No cover/audio is downloaded or self-hosted.
- **Volume help** explains phone/system volume controls and the Windows browser Volume Mixer. It is native expandable help, not an audio slider or a Spotify volume API claim.
- A next-song suggestion appears beside the introduction on desktop, with an actual reason and a direct link to its artist. A matching different artist is favored when available. **More from [artist]** goes to that artist's existing ten-song starter selection.
- **Find another song like this** opens the feeling/theme controls. Up to three matching songs appear before the longer research notes. Explicit filters intersect; combinations with no songs show an empty state rather than unrelated replacements. Fewer than three results are displayed when the catalog has fewer matches.
- With JavaScript, filtering updates only the suggestions and next card: the existing Spotify iframe, including its playback state, is preserved. A failed update keeps the old results, explains the failure, and offers a normal results-page link. Save buttons retain the visitor's existing browser-local saved state.
- Without JavaScript, the same form submits as an ordinary GET, recommendations and source links still work, and listeners receive a direct Spotify link instead of an inert player button. Native details controls and labeled selects support keyboard use.
- On phones the order is introduction, listening panel, next discovery, filter/results section, then the full notes. Research status remains visible near the introduction; the full notice, editorial-interpretation label, artist-statement attribution, and citations remain below.

## Recommendation basis and growth

The engine reads eligible published songs connected to published artists. Unfinished legacy starter songs without research keys, private/draft songs, and orphaned records are excluded from recommendations. It uses existing editorial theme/mood tags, curated song links, and related-artist links; it does not infer audio characteristics, Spotify popularity, personal characteristics, or artist intent.

For a default route, shared themes are used where an unvisited match exists, otherwise shared feelings, otherwise curated/catalog connections. Explicit user choices are never silently broadened. Reasons describe the actual matched tag or connection; sparse data is not filled with invented classifications. Parenthetical editorial markers are removed only in the display/index, leaving original research unchanged. Distinct labels such as `breakup` and `breakups` remain distinct until an editor deliberately standardizes them.

Next links carry up to eight recent published song IDs in `ag_trail`; these exclude immediate repeats. This bounded URL context needs no account, cookie, tracking service, or stored listening profile. The canonical song URL and underlying records remain unchanged.

The catalog uses bulk WordPress post, metadata, and taxonomy caches. A disposable test with 500 eligible recordings and fifty published artist records took nine database queries and approximately 20 ms to build the index in this environment. This measures index building, not a full page, a load test, or Hostinger production performance. No catalog-size constant limits the visitor feature to today's twenty artists.

For future research batches, keep mood and theme labels consistent and source/editorial distinctions explicit. Leave unsupported tags blank. Only 72 of today's selected songs have theme tags, so richer, careful tagging will improve the range and quality of matches as the catalog grows toward fifty artists.

## Apply locally

1. Download the latest `main` ZIP from https://github.com/richardlhvn/HvN-Space.
2. Copy **both `plugin` and `theme` folders** over those folders in your existing project; preserve any local code edits and keep `.env`, `.runtime`, `compose.yaml`, database, and uploads.
3. In WSL, from the existing project folder, run `docker compose restart wordpress`.
4. Refresh a song page with **Ctrl + F5**. No research import or media resynchronization is needed for this interface update. If you have not applied research batches 3/4 yet, their import remains a separate action described in `RESEARCH-UPDATE-2026-10-29.md`.

No npm dependency changes, Spotify account setup, database reset, or production deployment are required. Hostinger release remains a separate approval.

## Validation

- 32 discovery checks in a disposable namespace, including publication gates, source preservation, real tag intersections, artist diversity, empty states, malformed parameters, repeat prevention, and the 500-song bulk-query check.
- 54 interaction/browser checks for licensed/abstract previews, attribution, explicit loading, blocked fallback, volume help, filter updates preserving the exact iframe, saving, errors/retry, keyboard focus, recent journeys, native GET behavior, and widths 320/390/768.
- The existing 1,084 media browser checks still pass across all 200 selected songs, 142 exact Spotify mappings, 58 artist-profile fallbacks, six licensed photographs, fourteen placeholders, citations, and mobile layouts.
- 40 existing functional browser checks and 18 desktop/mobile accessibility audits pass, including the open controls, new abstract/portrait layouts, filtered results, and empty combinations.

Live Spotify audio remains subject to the cloud connection limitation recorded in the media report; mocked delayed/blocked frames are used only for interaction tests. Screenshots in `test-results/` show actual local pages before loading Spotify, not fabricated live player output. Current permission files and original photograph binaries remain unchanged. This update does not publish the site to Hostinger.
