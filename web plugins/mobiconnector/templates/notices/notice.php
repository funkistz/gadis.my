<?php
/**
 * Show messages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ) {
	return;
}

?>

<div class="notice-notice notice-mobiconnector">
	<?php foreach ( $messages as $message ) : ?>
		<p><?php echo wp_kses_post( $message ); ?></p>
	<?php endforeach; ?>
</div>
