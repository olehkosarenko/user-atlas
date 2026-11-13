# User Atlas

[![Lint](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml/badge.svg)](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml)

**User Atlas** is a WordPress plugin that shows users from an external API in a dynamic React table on a dedicated page of your site.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later
- [Composer](https://getcomposer.org/)
- Node.js 20.19 or later and npm, to build the frontend

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

### Filters

- `user_atlas__endpoint` - change the page slug
- `user_atlas__title` - change the page title
- `user_atlas__global_args` - change the data passed to the frontend (`window.global_user_atlas`)

## Development

### PHP coding standards

The PHP code follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) and is checked for PHP version compatibility. The rules are in `phpcs.xml.dist`.

```sh
composer install
composer lint       # check the code with PHPCS
composer lint:fix   # fix what can be fixed automatically with PHPCBF
```

The same check runs in GitHub Actions on every pull request and on pushes to `main`.

### Frontend

The frontend is a React app built with [`@wordpress/scripts`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/). The source is in `assets/src`, the build goes to `assets/build`. React comes from WordPress (`wp-element`), it is not bundled.

```sh
cd assets
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

GPL-3.0-or-later
