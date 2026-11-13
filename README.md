# User Atlas

[![Lint](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml/badge.svg)](https://github.com/olehkosarenko/user-atlas/actions/workflows/lint.yml)

**User Atlas** is a WordPress plugin that shows users from an external API in a dynamic React table on a dedicated page of your site.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later
- [Composer](https://getcomposer.org/)
- Node.js and npm, only if you change the frontend

## Installation

The `vendor/` directory is not committed, so install the Composer dependencies after cloning. Without them the plugin does not load.

```sh
cd wp-content/plugins
git clone https://github.com/olehkosarenko/user-atlas.git
cd user-atlas
composer install --no-dev
```

Use `composer install` without `--no-dev` if you also want the development tools (PHPCS).

The compiled frontend in `assets/build/` is committed, so no build step is needed to run the plugin.

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

The frontend source is in `assets/src`. To rebuild it:

```sh
cd assets
npm install
npm run build
```

Lint the frontend code:

```sh
npm run lint:js    # WordPress JavaScript Coding Standards
npm run lint:css   # WordPress CSS Coding Standards
```

## License

GPL-3.0-or-later
