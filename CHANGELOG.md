# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- PHP unit tests with PHPUnit and Brain Monkey (`composer test`), run on PHP 7.4 and 8.5 in GitHub Actions.
- The page template `templates/page.php`, which a theme can replace with `user-atlas/page.php`.
- With plain permalinks the page is available at `/?user_atlas_page=1`.

### Changed

- API requests no longer follow redirects. The API address is fixed, so a redirect now counts as an API failure instead of loading data from another address.

### Fixed

- The `user_atlas__endpoint` filter had no effect when it was added from a theme, and the link in the settings then led to a 404 page.
- The page was sent with the no-cache headers of a 404 page.
- The page used the embed template: it had no viewport meta tag, sent an `X-WP-embed` header, contained a hidden sharing dialog and did not run `wp_head()` and `wp_footer()`.
- A 400, 401 or 403 response from the API did not pause the API, so every visit requested it again. Now every failed request pauses the API; only a 404 for a single user still answers `404` without a pause.
- A 404 for the user list answered `404` and did not pause the API; it now counts as an API failure.
- A saved settings option or value of an unexpected type caused a fatal error on every request; such values now fall back to the defaults.

## [2.0.0] - 2026-11-15

### Breaking changes

- The `admin-ajax.php` endpoint (`action=user_atlas`) is removed. The page now loads its data from the REST routes `user-atlas/v1/users` and `user-atlas/v1/users/<id>`.
- `config.json` is removed.
- The data passed to the frontend through the `user_atlas__global_args` filter changed: `ajaxUrl` and `nonce` are replaced by `restUrl`, and `restNonce` for logged-in users.
- The plugin now declares WordPress 6.6 and PHP 7.4 as the minimum supported versions.

### Added

- Public, read-only REST routes for the users list and a single user, with argument validation.
- Caching of successful API responses in transients for one hour (filter `user_atlas__cache_ttl`).
- A 3 second timeout for API requests (filter `user_atlas__request_timeout`).
- A one minute pause of API requests after a timeout, connection error, 429 or 5xx response (filter `user_atlas__failure_pause`).
- The cache expiry time below the table for administrators.
- A **Clear cache** button in **Settings > User Atlas**.
- Admin notices when the Composer dependencies or the frontend build are missing.
- Cleanup of the plugin options on uninstall.
- Text domain `user-atlas`; the settings page labels are translatable.
- Unit tests for the frontend utilities.
- Coding standards checks (WordPress, WordPress-VIP-Go, PHP compatibility) and frontend lint, tests and build in GitHub Actions.

### Changed

- The frontend is built with `@wordpress/scripts` instead of Create React App and uses React from WordPress.
- The plugin's own styles replace Bootstrap.
- Accessibility: one link per table row, hidden table captions, column header scope, keyboard focusable scroll regions, visible focus outlines, an error color with sufficient contrast and a loader that is announced to screen readers.
- API failures return proper HTTP statuses (404, 502, 503) and visitors only see generic error messages.
- Unknown user IDs are rejected before calling the external API.
- The plugin page is matched by its exact path, also when WordPress runs in a subdirectory.
- Only known keys are saved in the plugin settings.
- The last update time in the settings is shown in the site timezone.
- The `user_atlas__title` filter also applies to the document title.
- The plugin is licensed under GPL-2.0-or-later instead of GPL-3.0-or-later.

### Fixed

- An empty list from the API was treated as an error.
- User details lost the parent keys of nested fields, and arrays were not joined.
- Assets were enqueued with a new version on every request, which prevented browser caching.

### Removed

- Bootstrap.
- The unused `psr/log` dependency.
- The `localhost:3000` mock mode of the frontend.
- API methods for posts, comments, albums and todos that the plugin did not use.

## [1.0.0] - 2025-01-20

- Initial release.

[2.0.0]: https://github.com/olehkosarenko/user-atlas/releases/tag/v2.0.0
