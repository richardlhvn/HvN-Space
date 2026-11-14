# tunetofond local update — Core 0.12.0 / Theme 0.6.0

This release rebrands the existing website to **tunetofond**, exactly in lowercase. It keeps the design, WordPress architecture, URLs, compatibility identifiers, research wording and licensed portraits. The public selection remains ten artists / 128 songs. No production deployment or indexing change is included.

This is a source update, not a full replacement project or a database backup. The site on your Windows/WSL computer has its own database; downloading this ZIP does not update that database automatically.

## Update your existing project in Windows/WSL

1. Open **Ubuntu/WSL** in your existing `HvN-Space` project folder, the folder containing `compose.yaml` and your private `.env`. Start Docker Desktop with WSL integration if that is how you run Docker. Back up before copying files:

   ```bash
   npm start
   bash scripts/backup.sh
   ```

   Keep that SQL backup private. Also back up your local uploads using your existing backup method; the database backup does not contain uploaded image files.

2. Extract `tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip` into a separate folder in Windows. **Merge its `plugin`, `theme`, and `scripts` folders into the matching folders in your existing project**, replacing matching source files. Do not replace the whole project. Keep `.env`, uploads, existing accounts and Docker volumes. Do not copy the old `downloads` folder over the new one or reset the database.

3. In Ubuntu/WSL, from that existing project folder:

   ```bash
   chmod -R a+rX plugin theme scripts
   docker compose restart wordpress
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-branding.php
   ```

   The last command is a **dry run**. It lists the old default site title, known first-party phrases on About/Copyright/Media Credits, and exact brand-only substitutions/keys for existing approved research. On an untouched cleaned ten/128 catalog, 69 research records need brand adjustments and 69 already match. It does not write changes. Other owner wording, facts, recording links, original workbook names, source identities and license credits are protected. Later research edits are preserved as conflicts; never force them. The migration refuses missing/older canonical records rather than creating pages or importing factual changes. Customized identity/text that cannot be safely recognized is reported for manual review; update only the intended branding in WordPress, never run a global search-and-replace.

4. Apply those listed branding changes:

   ```bash
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-branding.php apply
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-branding.php
   ```

   The repeat dry run should report `changed: 0`. Alternatively, log in as **editor** with your existing private password and open **Tools → Site branding** to preview and apply the same migration. If the site title was previously customized to something other than Afterglow, set the intended title **tunetofond** under **Settings → General**; the migration intentionally preserves an unexpected custom title.

5. Refresh with Ctrl+F5 and clear any site/CDN cache. Check the lowercase wordmark, footer, About/Copyright/Media Credits, a song’s attribution and the journal. In WordPress, plugin/theme display names should be **tunetofond Core 0.12.0** and **tunetofond 0.6.0**. Confirm **Artists → Public catalog** still shows ten artists / 128 songs. The brand migration does not change that option, statuses, slugs, publication dates/authors, `home`/`siteurl`, permalinks, saved items or indexing.

**For an existing cleaned ten/128 installation, apply only the branding migration. Do not reimport research or run setup for a rename.** If your local database predates the cleaned canonical catalog, that is a separate data-update prerequisite: use the protected dry run/import documented in `docs/CANONICAL_RESEARCH_SOURCE.md`, review conflicts, and enable the approved public scope after the records exist. Never use an old all-catalog importer. Source download alone cannot restore a former cloud database or editor changes.

## What stays compatible

Folders remain `theme`/`plugin` in the source and `afterglow`/`afterglow-core` in WordPress installation ZIPs. Functions/meta keys, attribution enums, schema identities, REST routes, the browser’s `afterglow:saved:v1` collection, Compose project/volume names and `AFTERGLOW_ADMIN_PASSWORD` are retained. Historical source filenames/URLs and authorship/license notices keep their original names. At the owner’s explicit request, identified brand self-references such as “For Afterglow” change to tunetofond in stored approved research and the active prepared JSON too. The migration uses the existing edit protection and transaction machinery; all other research wording stays unchanged. The original cleaned JSON and XLSX remain historical source evidence. The active manifest selects `reviewed-tunetofond-2026-11-14.json`, with a brand-only adjustment note identifying the original export. Source links, quoted passages, original workbook/source identities and licenses remain protected. `tunetofond-theme.zip` and `tunetofond-core.zip` have the compatible WordPress folder roots.

No domain, operator email or legal entity is invented. Complete operator/contact/privacy details before a separately authorized production launch. Official Spotify players still load only by choice. A separate 15-second sample is not implemented, and cloud audio playback is not claimed.

## Fresh development checkout

Use the complete source branch linked in `docs/TUNETOFOND_REBRAND.md`, rather than treating this update ZIP as a complete project. Node 20+, npm, Docker Engine/Compose v2, Python 3 and Chromium are prerequisites. Run `npm ci --cache /tmp/afterglow-npm`, make source bind mounts readable with `chmod -R a+rX plugin theme scripts tests`, then `npm run setup`. The current initializer creates only the canonical ten artists / 128 songs with tunetofond defaults; it never imports the other 43 artists. Existing installations should use the steps above instead.

The ZIP excludes credentials, `.env`, database dumps/backups, uploads, private master workbooks, internal QA worksheets, dependencies and runtime files. Original research registries and license records needed by the plugin remain included. Keep older downloads as historical releases.
