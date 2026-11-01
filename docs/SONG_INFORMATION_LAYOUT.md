# Song information layout — 2 November 2026

**Superseded editorial section:** [Core 0.9 / Theme 0.4 — About the Song](ABOUT_THE_SONG.md) now combines supported song information into one optional narrative. Its update requires both plugin and theme, with an additive two-song background update. The placement and interaction work described below is retained; the two separate information headings are historical.

Theme **0.3.0** puts the current song's listening notes and attributed artist explanation beside the Spotify listening panel. Recommendations immediately follow this main section; release context, research questions, the full review notice and source citations come afterwards. The small research-status link remains visible in the main view.

The main view distinguishes **Afterglow interpretation** from **Artist statement · editorial paraphrase, not a direct quotation**. Missing supported artist comments have an explicit empty state. The existing importer continues to withhold unconfirmed transcript-only statements and interpretations from reviewers. The supporting-sources link jumps to the citations below.

This changes presentation without updating any stored artist or song record. The template renders the current WordPress editor content once, then separates the existing top-level listening, release and research sections. It moves the existing basis/provenance paragraphs to the research area, preserves custom editor markup and text, and does not substitute the older imported JSON. If PHP's DOM extension is unavailable, the complete editor content remains visible in the main view instead of being discarded.

On phones, the order is title/introduction, listening panel, listening notes and artist explanation, discovery actions/status, recommendations, then research and citations. The next-song card is now part of the discovery section. The same inline filters update that card and the results without replacing either the song information or a loaded Spotify iframe. Licensed portrait credits, abstract placeholders, explicit player loading, volume help, direct Spotify fallbacks, saved songs and bounded recent-song trails are retained.

## Local update

1. Download the current repository ZIP and extract it separately.
2. Copy its **theme** folder over the existing project's theme folder. Preserve any independent theme edits, and keep `.env`, `.runtime`, uploads and the database.
3. With Docker Desktop running, open WSL in the existing project folder and run `docker compose restart wordpress`.
4. Refresh a song page with **Ctrl+F5**.

No research import, media synchronization, plugin upgrade or database migration is required for this layout change. Nothing is deployed to Hostinger.

## Verification

- 10 read-only rendering checks cover current editor text, UTF-8 punctuation, custom blocks/links, release/research separation, manual content and empty content.
- 63 discovery/listening browser checks cover the new desktop/mobile order, source attribution, incomplete records, filter updates retaining both the exact player and song-information elements, keyboard use, saved state, empty/error results, and native forms without JavaScript.
- 40 functional browser checks and 22 desktop/mobile accessibility audits pass.
- The media browser suite checks all 529 distinct selected song pages, 53 artist pages, 174 exact player mappings, 355 honest fallbacks, six licensed portraits and 47 placeholders.

The preview screenshots show actual local WordPress pages before loading Spotify. Spotify playback itself is not claimed as a new live verification; blocked and delayed frames test the unchanged loading/fallback behavior.
