# Validation of this MVP

Validated in the current cloud machine using the digest-pinned Docker stack: WordPress 7.1.3, PHP 8.3, MariaDB 11.4, Node 24, and system Chromium.

- **38 functional browser checks passed:** homepage, six-artist/six-song directories, name and mood filtering, combined-filter empty state, profiles, source/interpretation labels, Spotify search fallback, global search, journal/supporting pages, actual 404 status, safe saved-record lookup, browser-storage persistence/removal, and mobile navigation/search/layout.
- **16 WordPress editorial checks passed:** nonce and capability enforcement, artist relationships, rejecting self/wrong-type relationships, Spotify host validation, safe source URLs, plain-text metadata, valid calendar dates, attribution downgrade when sources are absent, valid sourced attribution, and demo exclusion from post-sitemap queries.
- **10 automated WCAG A/AA audits passed:** homepage, directory, artist profile, song profile, and saved page, each at desktop and mobile widths. No third-party resources loaded on those public pages. Automated checks do not certify complete accessibility or replace assistive-technology/user testing.
- **WordPress editor save passed:** logged into administration, opened the structured-field panel, changed a search description and Demo status on a temporary draft, saved through the normal UI, and read back the persisted values. The temporary draft was deleted.
- PHP syntax checks passed for all theme/plugin PHP files; the frontend JavaScript parsed successfully. Frozen dependency installation and repeated setup/restart succeeded. Installable ZIPs were built and checked for their expected root paths.
- A fresh WordPress bootstrap and demo seed also passed in a separate temporary table namespace. The check verified artist/song counts and demo/noindex defaults, then removed only those temporary tables.

Screenshots are generated in `test-results/` by the functional tests. The theme also includes its own appearance-preview screenshot. No live music, ads, analytics, deployment, purchases, or production credentials were used.

The optional upstream `wp core verify-checksums` request could not reach `api.wordpress.org`: the cloud proxy returned HTTP 403. This is a network-policy limitation, not a passed checksum check. The official Docker image pulls did verify their registry digests, and their exact digests are pinned; no TLS, package signature, or checksum verification was disabled. Re-run the WordPress checksum command when that official API is permitted, or in the production hosting environment.

This validates the current local development instance. It does not establish Hostinger production behavior, future cloud snapshot restoration, production performance/Core Web Vitals, advertising eligibility, legal-policy sufficiency, or researched factual content for the demo artist/song profiles. Follow the deployment and editorial guides before public launch.
