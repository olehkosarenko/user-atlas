# User Atlas

**User Atlas** is an interactive WordPress plugin that displays users in a dynamic table.

## Code Quality Checks

Before running the plugin, ensure that your PHP code follows the WordPress coding standards and is free from syntax errors.

### Check PHP CodeSniffer (PHPCS) and PHP Syntax

Install the necessary Composer dependencies:

```sh
composer require wp-coding-standards/wpcs
composer require squizlabs/php_codesniffer
```

Run the following command to analyze your code:

```sh
vendor/bin/phpcs
```

## Build Process

Navigate to the `assets` directory. Then, install dependencies and build the project:

```sh
cd assets
npm install
npm run build
```

## Available Commands

You can use the following commands for code quality checks and optimizations:

- **`npm run lint:css`** – Checks all SASS files against the [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/).
- **`npm run lint:js`** – Checks all JavaScript files against the [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/).  

```sh
cd assets
npm run lint:css
npm run lint:js
```