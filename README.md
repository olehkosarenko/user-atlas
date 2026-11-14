# User Atlas

[![Lint](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml/badge.svg)](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml)

**User Atlas** is a WordPress plugin that shows users from an external API in a dynamic React table on a dedicated page of your site.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later
- [Composer](https://getcomposer.org/)
- Node.js 26 LTS (see `assets/.nvmrc`) and npm, to build the frontend

## Installation

The `vendor/` and `assets/build/` directories are not committed, so install the Composer dependencies and build the frontend after cloning. Without them the plugin does not load.

```sh
cd wp-content/plugins
git clone https://github.com/olehkosarenko/user-atlas.git
cd user-atlas
composer install --no-dev
cd assets
npm ci
npm run build
```

Use `composer install` without `--no-dev` if you also want the development tools (PHPCS).

Then activate **User Atlas** in **Plugins**.

## Usage

Open `/user-atlas/` on your site, for example `https://example.com/user-atlas/`.

Settings live in **Settings > User Atlas**:

- **Endpoint** - the page slug, `user-atlas` by default
- **Title** - the page title, `User Atlas` by default

### REST API

The page loads its data from two public, read-only REST routes:

- `GET /wp-json/user-atlas/v1/users` - the list of users
- `GET /wp-json/user-atlas/v1/users/<id>` - a single user

The routes need no nonce, so the page keeps working when its HTML is served from a page cache.

### Caching

Successful API responses are cached in transients for one hour, so repeated requests do not call the API. When the site has a persistent object cache, the transients are stored there. Errors are never cached. A single user is requested from the API only when its ID is in the cached list, so unknown IDs answer `404` without an API call or a new cache entry.

Administrators see when the cached data expires below the table. To load fresh data right away, use **Clear cache** in **Settings > User Atlas**.

Requests time out after 3 seconds. If the API fails (timeout, connection error, 429 or 5xx response), the plugin stops calling it for one minute and answers `503`; cached data is still served. A 404 for a single resource does not pause the API. Visitors only see a generic error message, never the technical reason.

### Filters

- `user_atlas__endpoint` - change the page slug
- `user_atlas__title` - change the page title
- `user_atlas__global_args` - change the data passed to the frontend (`window.global_user_atlas`)
- `user_atlas__cache_ttl` - how long API responses are cached, in seconds (default `3600`)
- `user_atlas__request_timeout` - API request timeout, in seconds (default `3`)
- `user_atlas__failure_pause` - how long API requests are paused after a failure, in seconds (default `60`)

### Uninstall

Deleting the plugin removes its settings and cache version. Cached API responses expire on their own.

## Development

### PHP coding standards

The PHP code follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) and is checked for PHP version compatibility. The rules are in `phpcs.xml.dist`.

```sh
composer install
composer lint       # check the code with PHPCS
composer lint:fix   # fix what can be fixed automatically with PHPCBF
```

The code is also checked against the WordPress VIP coding standards (WordPress-VIP-Go); the few exceptions are documented in `phpcs.xml.dist` and inline.

The same check runs in GitHub Actions on every pull request and on pushes to `main`.

### Frontend

The frontend is a React app built with [`@wordpress/scripts`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/). The source is in `assets/src`, the build goes to `assets/build`. React comes from WordPress (`wp-element`), it is not bundled.

Run `nvm use` in `assets/` to switch to the Node.js version from `.nvmrc`.

```sh
cd assets
nvm use
npm ci
npm run build      # production build
npm start          # rebuild on every change
npm test           # unit tests
npm run lint:js    # WordPress JavaScript Coding Standards
npm run lint:css   # WordPress CSS Coding Standards
npm run format     # format the code with the WordPress Prettier config
```

GitHub Actions runs the linters, the unit tests and the build on every pull request and on pushes to `main`.

## License

[GPL-2.0-or-later](LICENSE)
