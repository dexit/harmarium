<?php
/**
 * Checkout customisations: reference image upload, commission order type field.
 */

defined( 'ABSPATH' ) || exit;

/* ── Add reference image upload field to checkout ── */
add_filter( 'woocommerce_checkout_fields', function ( array $fields ): array {
	$fields['order']['hm_reference_note'] = [
		'type'        => 'textarea',
		'label'       => __( 'Commission notes / reference description', 'harmarium-ext' ),
		'placeholder' => __( 'Describe your reference image or any special requirements…', 'harmarium-ext' ),
		'required'    => false,
		'class'       => [ 'form-row-wide' ],
		'priority'    => 110,
	];
	return $fields;
} );

/* ── Render file upload field after order notes ── */
add_action( 'woocommerce_after_order_notes', function ( \WC_Checkout $checkout ): void {
	if ( ! harmarium_cart_has_commission_product() ) {
		return;
	}
	?>
	<div id="hm-reference-upload" class="form-row form-row-wide">
		<label for="hm_reference_image">
			<?php esc_html_e( 'Reference image (optional)', 'harmarium-ext' ); ?>
			<abbr class="optional"><?php esc_html_e( 'Optional', 'woocommerce' ); ?></abbr>
		</label>
		<span class="woocommerce-input-wrapper">
			<input type="file"
				id="hm_reference_image"
				name="hm_reference_image"
				accept="image/jpeg,image/png,image/gif,image/webp,application/pdf"
				class="input-text">
			<span class="description"><?php esc_html_e( 'JPG, PNG, GIF, WebP or PDF — max 8 MB', 'harmarium-ext' ); ?></span>
		</span>
	</div>
	<?php
} );

/* ── Make checkout form multipart ── */
add_filter( 'woocommerce_checkout_form_attributes', function ( array $attrs ): array {
	$attrs['enctype'] = 'multipart/form-data';
	return $attrs;
} );

/* ── Validate & process file upload on checkout ── */
add_action( 'woocommerce_checkout_process', function (): void {
	if ( empty( $_FILES['hm_reference_image']['name'] ) ) {
		return;
	}
	$file = $_FILES['hm_reference_image'];

	if ( $file['error'] !== UPLOAD_ERR_OK ) {
		wc_add_notice( __( 'Reference image upload failed. Please try again.', 'harmarium-ext' ), 'error' );
		return;
	}

	$max_bytes = 8 * 1024 * 1024;
	if ( $file['size'] > $max_bytes ) {
		wc_add_notice( __( 'Reference image must be smaller than 8 MB.', 'harmarium-ext' ), 'error' );
		return;
	}

	$allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf' ];
	$finfo         = new \finfo( FILEINFO_MIME_TYPE );
	$detected_type = $finfo->file( $file['tmp_name'] );
	if ( ! in_array( $detected_type, $allowed_types, true ) ) {
		wc_add_notice( __( 'Reference image must be JPG, PNG, GIF, WebP, or PDF.', 'harmarium-ext' ), 'error' );
	}
} );

add_action( 'woocommerce_checkout_update_order_meta', function ( int $order_id ): void {
	$order = wc_get_order( $order_id );
	if ( ! $order ) return;

	if ( ! empty( $_POST['hm_reference_note'] ) ) {
		$order->update_meta_data( '_hm_reference_note', sanitize_textarea_field( wp_unslash( $_POST['hm_reference_note'] ) ) );
	}

	if ( ! empty( $_FILES['hm_reference_image']['name'] ) && $_FILES['hm_reference_image']['error'] === UPLOAD_ERR_OK ) {
		$attachment_id = harmarium_upload_reference_image( $_FILES['hm_reference_image'], $order_id );
		if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
			$order->update_meta_data( '_hm_reference_image_id', $attachment_id );
		}
	}

	$order->save();
} );

function harmarium_upload_reference_image( array $file, int $order_id ): int|WP_Error {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload = wp_handle_upload( $file, [ 'test_form' => false ] );

	if ( isset( $upload['error'] ) ) {
		return new WP_Error( 'upload_failed', $upload['error'] );
	}

	$attachment = [
		'post_title'     => sprintf( __( 'Commission reference — Order #%d', 'harmarium-ext' ), $order_id ),
		'post_content'   => '',
		'post_status'    => 'private',
		'post_mime_type' => $upload['type'],
	];

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	wp_update_attachment_metadata( $attachment_id, $metadata );

	return $attachment_id;
}

/* ── Show reference image in admin order view ── */
add_action( 'woocommerce_admin_order_data_after_order_details', function ( \WC_Order $order ): void {
	$image_id = $order->get_meta( '_hm_reference_image_id' );
	$note     = $order->get_meta( '_hm_reference_note' );
	if ( ! $image_id && ! $note ) return;
	?>
	<div class="order_data_column">
		<h4><?php esc_html_e( 'Commission Reference', 'harmarium-ext' ); ?></h4>
		<?php if ( $note ) : ?>
			<p><strong><?php esc_html_e( 'Notes:', 'harmarium-ext' ); ?></strong><br>
			<?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
		<?php if ( $image_id ) : ?>
			<p><strong><?php esc_html_e( 'Reference Image:', 'harmarium-ext' ); ?></strong><br>
			<?php
			$url = wp_get_attachment_url( (int) $image_id );
			if ( $url ) {
				$thumb = wp_get_attachment_image( (int) $image_id, [ 120, 120 ] );
				echo '<a href="' . esc_url( $url ) . '" target="_blank">' . $thumb . '</a>';
			}
			?>
			</p>
		<?php endif; ?>
	</div>
	<?php
} );

/* ── Show reference image in order confirmation email ── */
add_action( 'woocommerce_email_order_meta', function ( \WC_Order $order, bool $sent_to_admin ): void {
	if ( ! $sent_to_admin ) return;
	$image_id = $order->get_meta( '_hm_reference_image_id' );
	$note     = $order->get_meta( '_hm_reference_note' );
	if ( ! $image_id && ! $note ) return;

	echo '<h2>' . esc_html__( 'Commission Reference', 'harmarium-ext' ) . '</h2>';
	if ( $note ) {
		echo '<p>' . esc_html( $note ) . '</p>';
	}
	if ( $image_id ) {
		$url = wp_get_attachment_url( (int) $image_id );
		if ( $url ) {
			echo '<p><a href="' . esc_url( $url ) . '">' . esc_html__( 'View reference image', 'harmarium-ext' ) . '</a></p>';
		}
	}
}, 10, 2 );

/* ── Helper: check if cart contains a commission/artwork product ── */
function harmarium_cart_has_commission_product(): bool {
	if ( ! WC()->cart ) return false;
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = $item['data'] ?? null;
		if ( ! $product ) continue;
		$cats = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'slugs' ] );
		if ( in_array( 'commission', (array) $cats, true ) || in_array( 'artwork', (array) $cats, true ) ) {
			return true;
		}
	}
	return false;
}
