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

<div class="notti-settings-onsignal setting-success">
	<?php foreach ( $messages as $message ) : ?>
		<?php echo wp_kses_post( $message ); ?>
	<?php endforeach; ?>
</div>
