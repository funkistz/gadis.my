<?php
/**
 * Show messages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $messages ) {
	return;
}

?>

<div class="notice-success notice-mobiconnector">
	<?php foreach ( $messages as $message ) : ?>
		<p><?php echo wp_kses_post( $message ); ?></p>
	<?php endforeach; ?>
</div>
