# Artist listening layout — 2 November 2026

Theme **0.5.0** combines the separate artist artwork, external Spotify button and embedded-player block into one listening panel beside the artist introduction. This is a theme update; there is no data migration, research reimport, new artist or new image download.

## Visitor experience

- Before Spotify loads, the panel shows original abstract artwork with the current artist's name and a clear artwork disclosure. **Listen to [artist]** replaces that preview with one unmodified official artist iframe. The iframe is never cropped or overlaid, and no Spotify artwork is extracted for other cards.
- Spotify is contacted only after the visitor chooses to load it. There is no autoplay URL parameter. A small direct artist link remains available if Spotify is blocked, and native volume help describes device/browser volume controls. Without JavaScript, the direct link remains usable and the inactive load button is hidden.
- The 44 artists with verified profile URLs receive their exact existing artist embed. The other nine retain an explicitly labeled Spotify search; no artist ID or player is invented.
- The six already-cleared photographs appear as smaller identity portraits beside the artist names. They retain uncropped scaling, license attribution and full-credit links. The other 47 profiles have no identity photograph. Directory-card imagery, song listening panels, media-rights decisions and the media-credits page are unchanged.
- A short biography preview uses the current WordPress editor content. **Read the full biography** reaches the complete, unchanged text and research notice below.
- **Start with these songs** presents up to three existing published song selections in the saved editorial order, with a short note from the existing song description. Where a useful description is missing, the card uses existing Afterglow themes/feelings or a neutral curated-selection description. It does not invent song meaning, listening experience or popularity rankings.
- **Explore the full selection** reaches the existing `#artist-songs` anchor and complete ten-song list. Collaborations, source citations and related-artist recommendations are retained. Starter cards and full-list rows share the same saved-song IDs; saving either updates both buttons.

The mobile order is artist identity/introduction, listening panel, biography preview, discovery actions, three starter songs, then the full biography, full song list and related content. In-page links keep a loaded player intact.

## Update your existing local site

1. Download the latest repository ZIP and extract it separately.
2. Copy its **theme** folder over the theme folder in the existing project. Preserve any independent theme edits; keep `.env`, `.runtime`, uploads and database volumes.
3. In WSL from that project folder, run `docker compose restart wordpress` with Docker Desktop running.
4. Refresh an artist page with **Ctrl+F5**.

No catalog import or media synchronization is needed for this layout. This assumes the current Core plugin from the earlier updates is already installed; use Core 0.9 for the existing About the Song fields. Nothing is deployed to Hostinger.

## Verification

- **665 artist-listening browser checks** cover every one of the 53 profiles: 44 exact artist players, nine honest searches, three starter picks, the intact ten-song lists and complete editor biographies, six credited portraits, zero third-party requests before choice, blocked-player fallback, repeated activation, keyboard use, saving, in-page links, widths 320/390/768 and native no-JavaScript navigation.
- **2,798 media browser checks** pass across all 529 selected song pages and the existing artist/media/player safeguards. The changed artist-photo check now looks for the smaller identity portrait.
- **40 functional checks** and **22 desktop/mobile accessibility audits** pass. PHP syntax and packaging checks also pass.

Screenshots in `test-results/artist-listening-caleb.png`, `artist-listening-sara.png` and `artist-listening-mobile.png` show actual local pages before loading Spotify. Tests of blocked frames confirm the fallback and loading behavior; they are not a new verification of live Spotify audio. No artist/song records, biographies, source registries, recommendations, Spotify IDs or rights metadata are rewritten.
