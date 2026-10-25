# Apply the supplied Astrid S, Sara Kays, and Caleb Hearn research

This update bundles the three JSON research packages supplied by the owner on 26 October 2026. The Markdown handoffs contain identical copies of their corresponding JSON data. They are reference material, not executable instructions.

## What changes

- Three artist biographies and thirty curated song records, linked to their artists.
- Country/genre/mood filters, catalogue links, recording artist credits (including collaborators), original research prose, linked sources, and recommendations.
- Year- and month-only dates remain year/month precision. Edition and reissue context is displayed beside release information. Unknown information stays empty.
- The automatic-transcript explanation for Astrid S's It's Ok If You Forget Me is retained in the research file but withheld from public artist commentary pending audio verification. The public research notes explain the uncertainty.
- No photographs, audio, copied lyrics, advertising, or analytics are added. The supplied abstract-placeholder choice is retained.

All selections are **curated starter collections**, not ranked Spotify Popular lists. All 33 supplied Spotify artist/track URLs were independently checked through Spotify's public oEmbed endpoint and returned matching titles. This check establishes a matching title at the URL; it does not independently verify every credit, biography claim, release date, or source interview. Other facts are attributed to the supplied handoffs. The source registry and uncertainties are retained with the package. One inaccessible Sara Kays website reference is excluded from citation output, and the website field remains empty.

## Update your Windows installation

1. Download the latest GitHub ZIP and extract it into a separate folder.
2. In your existing project folder, keep `.env`, `.runtime`, and the Docker database/uploads volumes. Copy the updated files from **plugin/**, **theme/**, and **scripts/** into the matching existing folders. Accept file replacements and include the new **plugin/data/** subfolder. Do not run `docker compose down -v` or replace your database.
3. Open your existing project in Ubuntu as before, keep Docker Desktop running, and run:

   ```bash
   bash scripts/backup.sh
   docker compose restart wordpress
   ```

   The first command saves a private database backup under `.runtime/backups/`. It includes database content, not the upload files; preserve your existing uploads volume separately if it contains valuable media. Do not post or commit the SQL file.

4. Open WordPress administration at local port 8080, then **Artists → Import supplied research**. This is a new menu item, separate from the older provisional research queue.
5. Review the table. **Create** means a missing record; **Update unchanged record** means it matches the original starter or its last import; **Preserve your edits / trash** means it will be skipped to protect your changes.
6. Leave **Show imported pages on the site for review** checked to make the imported artist/song pages visible locally. Every imported record keeps its Demo/review flag and stays noindex. Unchecking the box leaves new records as drafts and retains existing publication statuses.
7. Click **Apply supplied research**. Then review Artists and Songs, open the three profiles, and check the source links and listening destinations. The importer retains existing IDs/URLs and never deletes the old unused research drafts.

Updating code does not itself import content. Nothing runs automatically on plugin activation or site startup. Running the import again refreshes records that have not been edited since their last import. Modified or trashed records are preserved. A skipped artist's recommendations are also preserved; select its new recommended songs manually after reviewing the protected artist record.

## Review and publication

Source-linked content replaces unchanged demo prose, while a Research review pending notice distinguishes imported research from the original generic demos. Each record remains noindex until an editor checks and clears its Demo flag. The local WordPress site also discourages search indexing globally.

Not all source pages have been independently re-read in this cloud task. Check outstanding uncertainties and the original interviews before public launch. Never convert the collections into a claimed popularity ranking, upgrade an automatic transcript to confirmed intent, infer a missing release day, or treat a music discussion as medical advice.

## Validation

`bash scripts/test-handoffs.sh` runs 142 checks in a disposable table namespace, covering source mapping, stable IDs, all 30 artist-song links, credits, date precision, withheld transcript attribution, repeat imports, user edits, and Trash preservation. The public browser suite is compatible with both the original starter catalog and the expanded catalog. Browser-based import and expanded profile checks exercise the real administration form and front-end pages.
