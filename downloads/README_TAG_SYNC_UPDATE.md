# tunetofond tag sync — Core 0.15.0 / Theme 0.9.0

This combined update uses **15 genres, 20 feelings, and 20 themes** across browsing, stored WordPress tags and active bundled research. It includes all earlier branding and reader cleanup changes. The Google Sheet and XLSX remain unchanged; their update is deferred to the owner.

## Update your existing local project

1. Download and extract `tunetofond_tag_sync_Core_0.15.0_Theme_0.9.0.zip` outside your existing project.
2. Merge its **plugin**, **theme**, and **scripts** folders into your existing project, replacing matching source files. Keep your existing `.env`, uploads, database and Docker volumes.
3. Open WSL in the existing project folder that contains `compose.yaml`. Back up your database:

   ```bash
   bash scripts/backup.sh
   ```

4. Make the source readable to Docker and restart WordPress:

   ```bash
   chmod -R a+rX plugin theme scripts
   docker compose restart wordpress
   ```

5. Preview your local tag changes:

   ```bash
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-tags.php
   ```

   The report lists `updated`, `skipped`, and `conflicts`. The preview makes no database changes. If it reports conflicts, resolve the listed unknown labels or custom navigation mappings before applying; the migration never partially applies a conflicted batch.

6. Apply the migration, then run the preview again:

   ```bash
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-tags.php apply
   docker compose run --rm cli wp --user=editor eval-file /scripts/migrate-tags.php
   ```

   The second preview should show empty `updated`, `terms`, and `conflicts` lists. Counts depend on the records in your local database.

7. Open **http://localhost:8080** and refresh with **Ctrl+Shift+R**. Open a song’s **Change the feeling or theme** controls. The theme menu now contains at most 20 topics, plus the same/any controls.

The migration updates existing genre/mood assignments, comma-separated themes, the classification arrays inside stored research, and only the corresponding parts of import baselines/hashes. Existing editor changes remain protected. Hidden records receive the same tag normalization and remain hidden. Empty tags stay empty. Non-emotional mood descriptors `melodic`, `atmospheric`, and `late-night` retire without assigning an invented feeling.

No research reimport is needed for this update. Historical term aliases remain available so old filter links continue to work. If your previous stored-brand migration is still pending, run the existing protected `migrate-branding.php` after tag sync; it does not change classification tags.

The full mapping and validation report are in `docs/TAG_SYNC.md`. `docs/tag-sync-original-tags-2026-11-14.json` archives the original changed classification arrays. Original reviewed JSON snapshots remain in `plugin/data` as historical evidence.
