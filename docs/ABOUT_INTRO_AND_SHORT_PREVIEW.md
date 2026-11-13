# About as the introduction; short listening preview

Theme **0.5.2** removes the older short-description paragraph from song pages
whenever About the Song is present. Approved text is unchanged and remains beside
the official Spotify player. Songs without About retain their introduction. The
stored excerpt is preserved for existing cards, metadata and future editing.
No data import, migration, public-catalog or indexing change is needed.

Validation: 1,030 reviewed-page/browser checks passed across all 128 public songs,
including absence of the older intro, exact approved About copy, unchanged
Spotify mapping, no player before choice and filter/player preservation. All 22
desktop/mobile accessibility audits passed. Actual screenshot:
`test-results/about-primary-preview.png`.

For the existing local Docker project, copy the theme folder from
`Afterglow_theme_update_0.5.2.zip` into the existing project, merge matching files,
then run `chmod -R a+rX theme` and `docker compose restart wordpress` from that
project's Ubuntu/WSL terminal. Do not reimport data for this layout-only update.

## Requested short-preview option — outstanding

The owner wants a separate approximately 15-second listen alongside the existing
Spotify option. No second preview button has been shipped yet. It must actually
play a supported sample, retain the official player, and preserve copyrighted
audio/artwork inside an authorized player/source.

Both the official reference at
https://developer.spotify.com/documentation/embeds/references/iframe-api and the
API script https://open.spotify.com/embed/iframe-api/v1 failed at this cloud
environment's proxy with CONNECT 403 on 13 November 2026. Draft Internet-access
additions for `developer.spotify.com` and `open.spotify.com` were saved, preserving
existing custom destinations. Saving a draft has not applied or verified access.

Next steps after access is enabled:

1. Read the current official API/reference/terms and establish whether the controls
   support music tracks, rather than assuming podcast support applies to songs.
2. If supported, implement an explicit sample choice with the supported controller
   while retaining normal listening. Stop after actual playback time, not wall
   time while buffering or paused. Retain accessible status and a direct link
   when playback is blocked. Verify real music playback on the owner's browser;
   mocked events alone cannot establish that Spotify plays a sample.
3. If unsupported, retain the official player and identify an authorized preview
   source/recording match before adding a separate player. Never extract Spotify
   audio, invent MP3 URLs or present a nonfunctional timed button as a preview.

The owner confirmed that the current local widget plays the full song. Preserve
that option and add a separate sample choice once a supported implementation is
verified. That report does not establish programmatic music-control support in
the iFrame API. The cloud iframe tests have not verified actual audio playback.
