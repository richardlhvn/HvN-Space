# Afterglow local downloads

## Latest: Theme 0.5.2 — About as the song introduction

[Download the theme-only update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_theme_update_0.5.2.zip)

[GitHub file page](downloads/Afterglow_theme_update_0.5.2.zip) — use **Download raw file** (down-arrow) if needed.

On song pages, approved About the Song text is now the main introduction. The older short-description paragraph is hidden when About exists. The current Spotify player and all data remain unchanged. A separate short sample option is still awaiting verification of Spotify's supported playback capabilities; it is not included in this patch.

For the existing local installation: extract the ZIP, merge its `theme` folder into the existing `HvN-Space` folder, and replace matching theme files. From Ubuntu/WSL in that project folder run:

```bash
chmod -R a+rX theme
docker compose restart wordpress
```

Refresh a song page (Ctrl+F5 if needed). No data import is required for this theme-only patch. Keep `.env`, uploads and database volumes. See `LOCAL_UPDATE_README.txt` inside the ZIP.

Theme ZIP SHA-256: `e102b2fa5633e30e35adb6e7ea40019b2b13da1d81ee2768f55ca234e6b22f42`

## Full update: Core 0.10.1 / Theme 0.5.1

[Download the earlier full local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_local_update_Core_0.10.1_Theme_0.5.1.zip)

Use the full update first if the reviewed ten-artist catalog is not installed locally yet, then apply the latest theme patch above. The full package includes plugin, theme, helper scripts and local instructions. Back up the local database before the full update, preserve existing credentials/volumes, review protected import conflicts, then enable **Artists → Public catalog → Use ten-artist core catalog**.

These downloads update the existing local WordPress project. No production deployment is performed by downloading them.
