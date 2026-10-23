# Deploying to Hostinger

The deliverables are standard WordPress theme/plugin ZIPs. Production needs a Hostinger plan that supports WordPress, PHP 8.1+ (8.3 recommended), and MySQL/MariaDB. A static-file-only plan cannot run this site. Node and Docker are local development tools; neither is required on Hostinger. No paid plugins are required.

This document prepares deployment; nothing has been deployed publicly, purchased, or connected to an advertising/analytics account.

## 1. Prepare a private staging WordPress site

1. Use Hostinger’s WordPress installer to create a clean staging installation. Use a strong, unique production administrator password and a real administrator email. Do not copy the development `.env` or local admin credentials.
2. Use the current supported WordPress/PHP versions, HTTPS, a staging access restriction, and **Settings → Reading → Discourage search engines**. The checkbox is indexing guidance, not access control. Keep staging protected through Hostinger’s available staging/password controls.
3. Back up the existing site first if this is not a new installation. Do not replace a live site’s database with the local demo database.

## 2. Install the artifacts

Run `npm run package` locally. Under WordPress administration:

1. **Plugins → Add New → Upload Plugin**: upload `dist/afterglow-core.zip`, install, and activate.
2. **Appearance → Themes → Add New → Upload Theme**: upload `dist/afterglow.zip`, install, and activate.
3. Select **Settings → Permalinks → Post name**, then save. WordPress/Hostinger must serve rewritten URLs correctly; verify `/artists/` and `/songs/` rather than only the homepage.
4. On a **new staging site**, optionally select **Artists → Editorial guide → Create the labeled demo collection**. This creates six demo artists, six demo songs, three journal articles, and supporting pages, and selects the homepage/journal under Reading. It never runs automatically on activation and never overwrites an existing matching-slug record. Do not run it on an established site: it intentionally changes Reading settings and adds sample content.
5. Without the demo, create pages named `home`, `journal`, `saved`, `about`, `privacy`, `contact`, and `copyright`; assign Home and Journal under **Settings → Reading**. The Saved template activates by its `saved` slug. Artist/song directories are content-type archives, so do not create conflicting pages with the slugs `artists` or `songs`.
6. Change the brand under **Settings → General**, and the eyebrow/tagline/public email under **Appearance → Customize → Afterglow identity**. Review the navigation targets if changing supporting page slugs; the first milestone intentionally uses stable routes.

The code can also be uploaded by SFTP into `wp-content/themes/afterglow/` and `wp-content/plugins/afterglow-core/`. Do not upload `compose.yaml`, `.env`, `node_modules`, tests, database dumps, or the full development checkout into the web root.

## 3. Prepare real content

Follow `EDITORIAL.md`. The seed artist/song pages are placeholders, not launch-ready profiles. Add original substantive biographies, accurate country and release metadata where verified, real official Spotify links, reliable sources, meaningful recommendations, and rights-cleared photographs if desired. Keep the Demo checkbox checked until reviewed. Finish privacy/contact/copyright pages and remove their Demo flags after review. Source pages are links, not proof that an editor verified a claim; source credibility remains a human responsibility.

If migrating a developed content database, use Hostinger’s migration tools or a proper WordPress export/import workflow that preserves post IDs and metadata, followed by a serialized-data-safe URL replacement. Plain content export/import may change IDs and break manual relationships; verify/reselect all related artists, artist-song links, and recommended songs. The simplest initial launch is to create/review the content directly in staging. Back up the database and uploads before migration.

## 4. SEO and performance

- Each artist/song has a clean URL, an editable title/description, a canonical URL supplied by WordPress on singular pages, and an original-content editor. Verified, non-demo profiles emit conservative WebPage structured data. The implementation does not promise music rich results or invent machine-readable facts.
- Core WordPress supplies `/wp-sitemap.xml` once public indexing is enabled. Demo records and the thin mood/genre/country taxonomies are excluded. Filter/search pages use `noindex` to avoid indexing automatically generated low-value permutations. Artist/song archives themselves remain discoverable.
- If adding an SEO plugin later, choose one metadata/schema owner and disable overlapping theme output before enabling it. No SEO plugin is needed for the MVP.
- Enable and test Hostinger’s supported page caching, compression, backups, and image optimization. Keep logged-in/admin pages, `/wp-json/`, and nonce-bearing responses out of inappropriate full-page caches. Purge caches when relationships or taxonomy terms change.
- Fonts and artwork ship locally. Thumbnails use WordPress’s responsive image handling. Supply compressed, appropriately sized licensed images rather than large unoptimized originals.
- Verify actual performance on your production host and connection; local browser checks are not a production Core Web Vitals guarantee.

## 5. Journey and GA4, when you choose to add them

WordPress supports integration through standard `wp_head`/`wp_footer` hooks. Additional theme hooks `afterglow_after_header` and `afterglow_before_footer` provide deliberate future placements. There are no active ad tags, ad placeholders with empty reserved space, analytics scripts, credentials, or consent trackers in this MVP.

When ready, consult the current official Journey by Mediavine requirements and approved installation instructions at `https://www.journeymv.com/`. Acceptance, traffic eligibility, geography/consent rules, and site/content review are determined by the provider; the architecture does not guarantee admission. Use the provider’s supported WordPress integration rather than pasting scripts into core files. Keep ad density and cumulative layout shift under review.

For GA4, use a small reviewed integration plugin or a consent-aware provider integration. Keep measurement IDs and consent configuration outside theme source. Update the privacy notice to match the actual tracking and third parties. Do not enable tracking before the owner has selected and configured an appropriate consent/privacy approach for their audience.

## 6. Launch only after approval

Review substantive content, source/rights checks, real contact details, privacy/copyright notices, backups, HTTPS, mobile behavior, search/filter combinations, 404 responses, Spotify destinations, and browser-saved collections. Test editors adding and changing records. Check email and hosting logs under the actual host’s configuration.

After the owner approves public launch, remove staging access restrictions as intended, disable **Discourage search engines** on production, submit the sitemap through the owner’s Search Console account, and inspect the public robots/canonical tags. Demo pages must remain `noindex` until individually finished. Keep a rollback backup and a maintenance/update schedule. No DNS changes, public deployment, purchases, or live advertising are authorized by this implementation milestone.
