# Architecture notes

## WordPress boundaries

`afterglow-core` registers public `artist` and `song` post types. Both use WordPress title, content, excerpt, revisions, featured images, and shared `genre`, `mood`, `country` taxonomies. The plugin adds structured `_ag_…` metadata through a nonce- and capability-protected editor panel. It also filters directory queries and manages demo indexing rules. Deactivation does not delete content.

The classic PHP theme renders WordPress templates directly. No client framework or build process is needed. JavaScript enhances navigation, an accessible native search dialog, and saved collections; directories, profiles, search, and Spotify navigation work without it. Fonts and all default visuals are local. Browser-saved collections require JavaScript and local storage, hold up to 100 IDs, and are specific to one browser/origin. No account, cross-device sync, or server-side visitor profile is implied.

The public, read-only `/wp-json/afterglow/v1/saved?ids=1,2` route returns at most 100 published artist/song records, never drafts. The client renders returned text with DOM text nodes, validates same-origin destinations, and avoids injecting record content as HTML. Failed loads offer retry; storage failures report that saving did not occur. Requests for saved IDs may appear in ordinary hosting access logs.

## Metadata

Shared content fields: `_ag_meta_title`, `_ag_meta_description`, `_ag_demo`. Artist/song fields: `_ag_spotify`, `_ag_sources`, `_ag_artwork_credit`, `_ag_palette`.

Artist fields: `_ag_website`, `_ag_social`, `_ag_recommended` (song IDs), `_ag_related` (artist IDs), `_ag_similarity` (ID/explanation lines).

Song fields: `_ag_artist_id`, `_ag_release`, `_ag_themes`, `_ag_meaning_type` (`editorial` or `sourced`), `_ag_artist_comment`, `_ag_related` (song IDs).

The IDs are stable WordPress post IDs, serialized in comma-separated form for small-scale manual curation. Links are label/HTTPS-URL pairs. Text is sanitized on save and escaped in context on output. Source links are not fetched when editing, so editing does not depend on external DNS availability or API credentials. Relationship targets are checked for type and self-reference. Public output checks publish status again.

## SEO and integrations

WordPress supplies singular canonicals, document titles, and XML sitemaps. The theme supplies editable descriptions, basic Open Graph tags, and conservative WebPage structured data for non-demo music profiles. It does not emit invented artist identifiers, ratings, or rich-result claims. Demo records are excluded from core post sitemaps; custom taxonomies and filtered/search pages are intentionally kept out of indexing until substantive editorial landing pages are designed. A future SEO plugin should replace, rather than duplicate, this output.

Standard `wp_head`/`wp_footer` and `afterglow_after_header`/`afterglow_before_footer` hooks support later integrations. No ads, analytics IDs, autoplay embeds, or third-party trackers are active. Advertising-provider eligibility and legal compliance require separate decisions and review; they are not properties a theme can certify.

## Operations and scope

Local Docker containers use digest-pinned official WordPress, MariaDB, and WP-CLI images. Only WordPress HTTP is published, bound to the local loopback interface. Database access remains on the Compose network. Environment secrets are generated with cryptographic randomness and stored in ignored `.env`, never in source. The development setup sets `WP_ENVIRONMENT_TYPE=local`, disables dashboard file editing, and discourages indexing. It never modifies WordPress core.

The local installation helper and explicit admin demo action share a guarded seed. Theme/plugin activation itself never creates sample posts. The seed is intended for a fresh staging site; it selects a homepage/journal and uses demo flags for unfinished records. It is not a migration or an automatic content refresh. No external facts are sourced or invented by the seed.

Out of scope for this milestone: automatic recommendations, Spotify OAuth/API synchronization, a music player, user accounts for visitors, mailing lists, active ad/analytics integrations, reviewed legal policies, and public deployment. The site remains usable without these services.
