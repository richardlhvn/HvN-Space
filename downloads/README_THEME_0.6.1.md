# tunetofond Theme 0.6.1 — cleaner reader pages

For an existing tunetofond Core 0.12.0 / Theme 0.6.0 installation. This changes the theme only. No data import, branding migration, database reset, npm installation or credential change is needed.

## Apply on your Windows/WSL local site

1. Download `tunetofond_theme_update_0.6.1.zip` and extract it to a separate folder.
2. Copy its **theme** folder into your existing HvN-Space project (the folder containing `compose.yaml` and `.env`). Merge folders and replace matching theme files. Include the new `reader-presentation.php` file. Leave `plugin`, `.env`, uploads and Docker volumes alone.
3. In Ubuntu/WSL, from that same existing project folder, run:

   ```bash
   chmod -R a+rX theme
   docker compose restart wordpress
   ```

4. Open **http://localhost:8080** and press **Ctrl+F5**. Check an artist biography and **Think Before I Talk**. The theme version in WordPress **Appearance → Themes** is **0.6.1**.

## What changes

- Artist biographies omit the known site-editorial endings and generated “A curated way into the music” / owner-approved-catalog / popularity-ranking notes. The factual biography and full song list remain.
- Related-artist cards retain their names, links, portraits/placeholders, saved buttons and required photo credits, without the editorial pairing/workflow descriptions below them.
- About the Song keeps its narrative and source links without “Artist statement · paraphrased” or the other generated attribution badges.
- Recommendation tags show the existing sparkle icon followed by the actual theme/feeling. No “tunetofond theme:” prefix. Inline filtering uses the same presentation and preserves the loaded Spotify iframe.
- Generic editor’s-pick starter descriptions use the existing song’s theme/feeling instead. The explanatory editorial-tags note beneath discovery controls is omitted.
- Public REST/RSS use the same reader cleanup. WordPress editor fields, stored attribution enums, internal QA, source evidence and all raw research remain unchanged.

Source/photographer/license credits, release facts, official Spotify consent/fallback behavior, the public ten-artist/128-song scope, and noindex settings are preserved. The ZIP contains theme source, these instructions and actual preview screenshots. No private workbooks, credentials, database backups, uploads, dependencies or runtime state are included. No production deployment occurred.

If the site still looks unchanged after copying and Ctrl+F5, confirm you merged into the same project folder from which Docker Compose is running, and that `theme/reader-presentation.php` exists there. Do not rerun research imports to fix a theme-copy issue.
