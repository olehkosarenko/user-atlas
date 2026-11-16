<?php
/**
 * Template of the User Atlas page
 *
 * A theme can replace it with its own user-atlas/page.php.
 *
 * @package WpApp\UserAtlas
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php if ( false === has_action( 'wp_head', '_block_template_viewport_meta_tag' ) ) : // Block themes print their own. ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'user-atlas-page' ); ?>>
<?php wp_body_open(); ?>
<main id="user-atlas-root"></main>
<?php wp_footer(); ?>
</body>
</html>
