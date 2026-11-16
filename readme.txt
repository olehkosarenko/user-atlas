=== User Atlas ===
Contributors: olehkosarenko
Tags: users, table, rest-api, react, api
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows users from an external API in a React table on a dedicated page of your site.

== Description ==

User Atlas adds a page to your site, `/user-atlas/` by default, that lists users from an external API in a table. Click a user to see all of their details.

* Data is loaded through public, read-only REST routes: `user-atlas/v1/users` and `user-atlas/v1/users/<id>`.
* Successful API responses are cached for one hour, so repeated visits do not call the API.
* If the API fails, requests are paused for one minute and visitors see a short error message; cached data is still served.
* Administrators see when the cached data expires and can clear the cache in **Settings > User Atlas**.

User Atlas gets its data from an external service, see "Does the plugin call an external service?" below.

== Installation ==

1. Download `user-atlas.zip` from the GitHub releases page.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**, choose the zip and click **Install Now**.
3. Activate **User Atlas**.
4. Open `/user-atlas/` on your site.

To change the page address or title, go to **Settings > User Atlas**.

To install from source, clone the repository into `wp-content/plugins`, then run `composer install --no-dev` in the plugin folder and `npm ci && npm run build` in its `assets` folder.

== Frequently Asked Questions ==

= Where is the page? =

At `/user-atlas/` on your site, or at `/?user_atlas_page=1` with plain permalinks. Change the address (Endpoint) and the page title in **Settings > User Atlas**.

= Can a theme change the page? =

Yes. Copy `templates/page.php` from the plugin to `user-atlas/page.php` in your theme and change the markup; keep the element with the ID `user-atlas-root`. The `user_atlas__endpoint` filter can also be used from a theme.

= Does the plugin call an external service? =

Yes. The users come from [JSONPlaceholder](https://jsonplaceholder.typicode.com/), a free public test API. The plugin requests `https://jsonplaceholder.typicode.com/users` and `https://jsonplaceholder.typicode.com/users/<id>` from your server when the page needs data that is not cached. No data about visitors is sent; like any request made through the WordPress HTTP API, it carries the default user agent with your WordPress version and site address. See the [JSONPlaceholder website](https://jsonplaceholder.typicode.com/) for details about the service.

= How long is the data cached? =

One hour. Use the `user_atlas__cache_ttl` filter to change it, or **Clear cache** in **Settings > User Atlas** to load fresh data right away.

= What happens when the API is down? =

The request times out after 3 seconds, and the plugin stops calling the API for one minute. During that minute visitors get an error message right away, while cached data is still shown. Use the `user_atlas__request_timeout` and `user_atlas__failure_pause` filters to change these values.

= Which filters are available? =

* `user_atlas__endpoint` - the page slug
* `user_atlas__title` - the page title
* `user_atlas__global_args` - the data passed to the frontend
* `user_atlas__cache_ttl` - how long API responses are cached, in seconds
* `user_atlas__request_timeout` - the API request timeout, in seconds
* `user_atlas__failure_pause` - how long API requests are paused after a failure, in seconds

= Does the page work with page caching? =

Yes. The REST routes need no nonce, so a cached copy of the page keeps loading data.

== Changelog ==

= 2.0.0 =
* Breaking: the admin-ajax endpoint is replaced by the REST routes `user-atlas/v1/users` and `user-atlas/v1/users/<id>`.
* Breaking: WordPress 6.6 and PHP 7.4 are the minimum supported versions.
* Added caching of API responses, a request timeout, a pause after API failures and a Clear cache button.
* Added admin notices when the plugin is not built and cleanup of its options on uninstall.
* The frontend is built with @wordpress/scripts and no longer uses Bootstrap.
* Improved accessibility of the tables, buttons and loader.
* Relicensed under GPL-2.0-or-later.

The full list of changes is in [CHANGELOG.md](https://github.com/olehkosarenko/user-atlas/blob/main/CHANGELOG.md).

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.0.0 =
The admin-ajax endpoint is replaced by REST routes, and WordPress 6.6 and PHP 7.4 are now required. Check custom code that called the old endpoint before upgrading.
