# Apply the reviewed media update to your existing Windows installation

This updates your local Docker site. It does not deploy to Hostinger. Keep Docker Desktop running.

## 1. Back up the existing site

Open your existing project folder (the one containing `compose.yaml` and `.env`) in a terminal, then enter `wsl` if the terminal starts in PowerShell. Confirm `pwd` points to that folder, not your Windows user folder.

Run these commands individually in WSL:

```bash
bash scripts/backup.sh
docker compose exec -T wordpress tar -czf - -C /var/www/html/wp-content uploads > .runtime/backups/uploads-before-media.tar.gz
```

Keep these backups private. If either command fails, fix that error before continuing. Also keep a private copy of the current `theme` and `plugin` folders so you can restore any local code changes.

## 2. Download and copy the updated source

1. Open https://github.com/richardlhvn/HvN-Space and select **Code → Download ZIP** on the **main** branch.
2. Extract the ZIP into a separate temporary folder. Open the extracted project until you see `compose.yaml`, `theme`, and `plugin`.
3. Copy the new **theme** and **plugin** folders into your **existing project folder**, choosing to replace matching files. If you have edited theme/plugin code locally, merge those edits rather than blindly replacing them.
4. Keep the existing `.env`, `.runtime`, and `compose.yaml`. Do not replace them, start from a new project folder, or delete Docker volumes. Your content and uploads live in the existing Docker volumes.

If your existing folder is a Git checkout rather than a downloaded ZIP, inspect `git status` and preserve local edits, then use `git pull --ff-only origin main` instead of copying folders.

## 3. Restart and synchronize

In WSL, still in your existing project folder, run:

```bash
docker compose restart wordpress
```

1. Open http://localhost:8080/wp-admin/ (use your configured port if different) and sign in with your existing administrator account.
2. If the current fifty-three research artists and 530 curated song selections have not been imported, open **Artists → Import supplied research**, inspect its review, and apply the relevant collection. **Final artist intake — batches 5–7 (33 artists)** adds the final 33 artists and 330 selections, reusing one existing duet page; choose **All research** on an installation missing the earlier catalog. Existing edited records are protected. See `FINAL_ARTIST_INTAKE_2026-10-31.md` for the final intake and review-status limitations.
3. Open **Artists → Media and embeds** and inspect the dry run. A complete catalog should match 582 distinct records plus one reused selection, with six verified photo candidates and forty-seven unresolved placeholders. Some photos can be preserved rather than replaced if you edited the corresponding artist record. Only 174 distinct selected pages have active exact track mappings; 355 retain missing track links and use artist-profile or clearly labeled search fallbacks.
4. Apply the media synchronization using the button on that page. Repeating it reuses the same attachments rather than adding duplicates. Review any preserved or missing records rather than forcing an overwrite.
5. Refresh http://localhost:8080/artists/ and check the photo credits, an artist profile, a song page, and **Media credits** in the footer. Click **Load Spotify player** to test playback from your own browser. A direct Spotify link remains available if embedding is blocked.

On updated song pages, the Spotify player replaces the large abstract hero image. The real cover appears inside the full official player after you click **Load Spotify player**. Abstract illustrations remain on directory cards and on song profiles that have no verified track link. This is a theme layout update; existing Spotify mappings do not need to be imported again.

You do not need to run `npm ci`, reset WordPress, or reinstall the database for this update. Never run `docker compose down -v`: that removes the stored site data.
