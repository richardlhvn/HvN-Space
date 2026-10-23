# The editorial workflow

All everyday publishing happens inside WordPress. No code or Spotify API account is required.

## Artists

Open **Artists → Add Artist**. Enter the name, an original short excerpt, and a substantive biography in the main editor. In the sidebar, choose/create genre, mood, and country taxonomy terms. Mood/genre choices are curatorial; country and biography claims need verification. Leave unavailable facts blank.

The **Afterglow · Editorial details** panel contains the structured fields. In WordPress 7, click the **Meta Boxes** label at the bottom-left of the editor to open it. On older versions the panel appears beneath the main content. Its fields include:

- An official Spotify artist URL: `https://open.spotify.com/artist/…`. Open and verify the destination first. The public page uses a clearly labeled Spotify search if this field is blank.
- Verified official website/social links. Each social/source line is `Descriptive label | https://…`.
- Recommended songs and similar artists, selected from existing WordPress records. Ctrl/Cmd selects several. The artist’s ID is shown beside its name.
- Similarity explanations, one per line: `Related artist ID | Why this is an interesting editorial pairing`. Describe musical similarities as your own considered judgment; never imply an endorsement or an undocumented influence.
- Abstract artwork palette, image license/credit, editable search title/description, and the Demo flag.

Save a draft, preview it, and verify the layout and links before publishing. Revisit related profiles after deleting or unpublishing an artist; unpublished relationships are not displayed publicly.

## Songs

Create the artist first, then open **Songs → Add Song** and select that artist. Add the title, original excerpt, and main listening notes in the editor. Choose moods/genres and add the following:

- A verified Spotify track URL, or leave it blank for labeled search. Canonical `/track/…` and `/artist/…` URLs are accepted; remove tracking parameters if desired. No music is uploaded or downloaded.
- A verified release date in `YYYY-MM-DD`, with a supporting source. The public release date is hidden without a source.
- Themes as editorial terms. These are not a substitute for reading/listening and research.
- Meaning attribution: **Editorial interpretation** by default. Use **Artist explanation** only with an actual original source and a short, accurate paraphrase in the artist statement field. The frontend labels that section as a sourced paraphrase rather than a direct quotation. Main-editor text is always labeled editorial.
- Original source links and similar songs.

If all source links are missing/invalid, the system switches attribution back to editorial and clears the artist statement. It validates link structure and HTTPS, not the truth of a source or its relationship to the artist. An editor must verify credibility and the actual statement. Do not invent citations or use copied biographies, complete lyrics, fabricated quotes, or an interpretation presented as confirmed intent.

## Photos and artwork

Original CSS/SVG abstract artwork is the safe default and is explicitly identified as a placeholder on profiles. It is not official album artwork. A featured photograph is displayed only when its image-rights/attribution field is filled; that field is a record of the editor’s rights review, not automated permission. Keep permission/license records separately, provide the required attribution, and write accurate alt text in the Media Library. If rights are unclear, retain the abstract artwork.

## Search and discovery

Use **Artists → Genres / Moods / Countries** to maintain terms. Public directories combine the name query and selected taxonomy filters with AND. Empty filters include all values. Filters do not create indexable SEO landing pages; substantive curated articles belong in **Posts**.

Recommendations are manual, directed relationships. Selecting artist B on artist A does not silently create the reverse link. Add both when that makes editorial sense. Song-to-artist links and recommended-song selections are separate, so an editor controls the listening route explicitly.

## Journal, branding, and legal pages

Use **Posts** for original features and collections. The homepage shows four artist cards, four song rows, and the latest three journal articles; artist/song features use creation order for this milestone. Extend homepage curation in a later iteration if a larger publishing schedule needs explicit placements.

Edit **Pages** to complete About, Privacy, Contact, and Copyright. Their legal text is a pre-launch template, not a reviewed notice for your business. Set the actual public email in **Appearance → Customize → Afterglow identity**; Contact and Copyright then show a working email link. No form submissions are collected in the MVP.

## Publishing review

Keep the Demo flag checked until the page has substantive original content, accurate claims, verified source/Spotify links, and documented image rights. Uncheck it only after review. Demo artist/song records are excluded from search-engine indexing and the core post sitemap. Site-wide Reading settings also control whether the site is public to search engines.

Normal WordPress roles, drafts, previews, revisions, scheduling, and trash apply. An administrator installs/configures the theme/plugin; a WordPress Editor can publish and maintain records. Only users allowed to edit a record can save its protected fields; a subscriber cannot. Back up content and uploads regularly.
