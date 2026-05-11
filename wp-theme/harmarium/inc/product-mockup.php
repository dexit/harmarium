<?php
/**
 * Harmarium · Artwork Viewer & Product Mockup
 *
 * Renders the interactive artwork viewer on single product and single portfolio
 * pages. Features:
 *  · Gallery with thumbnails + full-screen zoom
 *  · In-room mockup with 6 SVG scenes and drag-to-reposition
 *  · Frame selector (7 styles — CSS-based, no images)
 *  · Canvas type selector (Stretched / Gallery Wrap / Framed Print / Museum Frame)
 *  · Packaging type selector (Ready to Hang / Rolled / Museum Crate)
 *  · All selections passed to WooCommerce cart as custom item data
 *
 * @package Harmarium
 */

defined( 'ABSPATH' ) || exit;

/* ──────────────────────────────────────────────────────────────────
   Constants / configuration
   ────────────────────────────────────────────────────────────────── */

const HARMARIUM_MOCKUP_SCENES = [
	'living-room'   => [ 'label' => 'Living room',   'aspect' => '16/10', 'wall' => '#efe5d4' ],
	'minimal-loft'  => [ 'label' => 'Minimal loft',  'aspect' => '16/10', 'wall' => '#f6f3ec' ],
	'studio-wall'   => [ 'label' => 'Studio wall',   'aspect' => '4/3',   'wall' => '#f8f6f0' ],
	'concrete-loft' => [ 'label' => 'Concrete loft', 'aspect' => '16/9',  'wall' => '#cccac4' ],
	'dark-gallery'  => [ 'label' => 'Dark gallery',  'aspect' => '16/10', 'wall' => '#1c1a18' ],
	'sunlit-room'   => [ 'label' => 'Sunlit room',   'aspect' => '4/3',   'wall' => '#f5eddc' ],
];

const HARMARIUM_MOCKUP_FRAMES = [
	'none'        => 'Unframed',
	'thin-black'  => 'Thin Black',
	'thick-black' => 'Thick Black',
	'thin-white'  => 'Thin White',
	'oak'         => 'Natural Oak',
	'walnut'      => 'Walnut',
	'gold'        => 'Gold Leaf',
];

const HARMARIUM_CANVAS_TYPES = [
	'stretched'    => [ 'label' => 'Stretched Canvas',   'desc' => 'Gallery-depth 38mm stretcher bars, bare edge' ],
	'gallery-wrap' => [ 'label' => 'Gallery Wrap',        'desc' => 'Image continues around 45mm deep stretcher' ],
	'framed-print' => [ 'label' => 'Framed Print',        'desc' => 'Archival giclée with off-white museum mat' ],
	'museum-frame' => [ 'label' => 'Museum Frame',        'desc' => 'Float-mounted in conservation-grade frame' ],
];

const HARMARIUM_PACKAGING_TYPES = [
	'ready-hang'  => [ 'label' => 'Ready to Hang',  'desc' => 'Wire + wall fixing included',              'icon' => '🖼️' ],
	'flat-ship'   => [ 'label' => 'Rolled / Flat',   'desc' => 'Archival tube or foam-backed flat pack',   'icon' => '📦' ],
	'museum-crate'=> [ 'label' => 'Museum Crate',    'desc' => 'Custom wooden crate, white-glove service', 'icon' => '🏛️' ],
];

/* ──────────────────────────────────────────────────────────────────
   Register product meta
   ────────────────────────────────────────────────────────────────── */
function harmarium_register_product_mockup_meta(): void {
	$metas = [
		'_harmarium_artwork_id'    => 'integer',
		'_harmarium_mockup_scene'  => 'string',
		'_harmarium_mockup_frame'  => 'string',
		'_harmarium_canvas_type'   => 'string',
		'_harmarium_packaging'     => 'string',
		'_harmarium_mockup_mat'    => 'number',
		'_harmarium_mockup_width'  => 'number',
		'_harmarium_mockup_height' => 'number',
		'_harmarium_mockup_scale'  => 'number',
		'_harmarium_mockup_x'      => 'number',
		'_harmarium_mockup_y'      => 'number',
		'_harmarium_mockup_image'  => 'integer',
	];
	foreach ( $metas as $key => $type ) {
		register_post_meta( 'product', $key, [
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => fn() => current_user_can( 'edit_products' ),
		] );
	}
}
add_action( 'init', 'harmarium_register_product_mockup_meta', 7 );

/* ──────────────────────────────────────────────────────────────────
   Customizer defaults
   ────────────────────────────────────────────────────────────────── */
add_action( 'customize_register', function ( \WP_Customize_Manager $wc ): void {
	$wc->add_section( 'harmarium_mockup', [
		'title'    => __( 'Harmarium · Artwork Viewer', 'harmarium' ),
		'priority' => 35,
	] );

	$controls = [
		'harmarium_mockup_default_scene'   => [ 'label' => 'Default room scene',     'type' => 'select', 'choices' => wp_list_pluck( HARMARIUM_MOCKUP_SCENES, 'label' ), 'default' => 'living-room' ],
		'harmarium_mockup_default_frame'   => [ 'label' => 'Default frame',           'type' => 'select', 'choices' => HARMARIUM_MOCKUP_FRAMES, 'default' => 'thin-black' ],
		'harmarium_mockup_default_canvas'  => [ 'label' => 'Default canvas type',     'type' => 'select', 'choices' => wp_list_pluck( HARMARIUM_CANVAS_TYPES, 'label' ), 'default' => 'stretched' ],
		'harmarium_mockup_default_pkg'     => [ 'label' => 'Default packaging',       'type' => 'select', 'choices' => wp_list_pluck( HARMARIUM_PACKAGING_TYPES, 'label' ), 'default' => 'ready-hang' ],
		'harmarium_mockup_show_picker'     => [ 'label' => 'Show viewer controls',    'type' => 'checkbox', 'default' => 1 ],
	];

	foreach ( $controls as $id => $args ) {
		$wc->add_setting( $id, [ 'default' => $args['default'] ?? '', 'sanitize_callback' => 'sanitize_text_field' ] );
		$wc->add_control( $id, array_merge( $args, [ 'section' => 'harmarium_mockup' ] ) );
	}
} );

/* ──────────────────────────────────────────────────────────────────
   Product editor tab: Harmarium Viewer
   ────────────────────────────────────────────────────────────────── */
add_filter( 'woocommerce_product_data_tabs', function ( array $tabs ): array {
	$tabs['harmarium_mockup'] = [
		'label'    => __( 'Harmarium Viewer', 'harmarium' ),
		'target'   => 'harmarium_mockup_data',
		'priority' => 65,
	];
	return $tabs;
} );

add_action( 'woocommerce_product_data_panels', function (): void {
	global $post;
	$pid = $post->ID;

	$artwork_id  = (int) get_post_meta( $pid, '_harmarium_artwork_id',   true );
	$scene       = get_post_meta( $pid, '_harmarium_mockup_scene',  true ) ?: get_theme_mod( 'harmarium_mockup_default_scene', 'living-room' );
	$frame       = get_post_meta( $pid, '_harmarium_mockup_frame',  true ) ?: get_theme_mod( 'harmarium_mockup_default_frame', 'thin-black' );
	$canvas      = get_post_meta( $pid, '_harmarium_canvas_type',   true ) ?: get_theme_mod( 'harmarium_mockup_default_canvas', 'stretched' );
	$packaging   = get_post_meta( $pid, '_harmarium_packaging',     true ) ?: get_theme_mod( 'harmarium_mockup_default_pkg', 'ready-hang' );
	$mat         = get_post_meta( $pid, '_harmarium_mockup_mat',    true );
	$width       = get_post_meta( $pid, '_harmarium_mockup_width',  true );
	$height      = get_post_meta( $pid, '_harmarium_mockup_height', true );
	$scale       = get_post_meta( $pid, '_harmarium_mockup_scale',  true ) ?: 0.42;
	$x           = get_post_meta( $pid, '_harmarium_mockup_x',      true ) ?: 0.50;
	$y           = get_post_meta( $pid, '_harmarium_mockup_y',      true ) ?: 0.44;
	$image_id    = (int) get_post_meta( $pid, '_harmarium_mockup_image', true );

	$portfolio = get_posts( [ 'post_type' => 'portfolio', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );

	echo '<div id="harmarium_mockup_data" class="panel woocommerce_options_panel">';

	/* Linked artwork */
	echo '<p class="form-field"><label for="_harmarium_artwork_id">' . esc_html__( 'Linked artwork', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_artwork_id" id="_harmarium_artwork_id"><option value="">' . esc_html__( '— None —', 'harmarium' ) . '</option>';
	foreach ( $portfolio as $p ) {
		printf( '<option value="%d"%s>%s</option>', $p->ID, selected( $artwork_id, $p->ID, false ), esc_html( get_the_title( $p ) ) );
	}
	echo '</select></p>';

	/* Scene */
	echo '<p class="form-field"><label>' . esc_html__( 'Default room scene', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_mockup_scene">';
	foreach ( HARMARIUM_MOCKUP_SCENES as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $scene, $k, false ), esc_html( $v['label'] ) );
	}
	echo '</select></p>';

	/* Frame */
	echo '<p class="form-field"><label>' . esc_html__( 'Default frame', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_mockup_frame">';
	foreach ( HARMARIUM_MOCKUP_FRAMES as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $frame, $k, false ), esc_html( $v ) );
	}
	echo '</select></p>';

	/* Canvas type */
	echo '<p class="form-field"><label>' . esc_html__( 'Default canvas type', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_canvas_type">';
	foreach ( HARMARIUM_CANVAS_TYPES as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $canvas, $k, false ), esc_html( $v['label'] ) );
	}
	echo '</select></p>';

	/* Packaging */
	echo '<p class="form-field"><label>' . esc_html__( 'Default packaging', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_packaging">';
	foreach ( HARMARIUM_PACKAGING_TYPES as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $packaging, $k, false ), esc_html( $v['label'] ) );
	}
	echo '</select></p>';

	/* Numeric fields */
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_mat',    'label' => __( 'Mat %',          'harmarium' ), 'type' => 'number', 'value' => $mat ] );
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_width',  'label' => __( 'Width (cm)',     'harmarium' ), 'type' => 'number', 'value' => $width ] );
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_height', 'label' => __( 'Height (cm)',    'harmarium' ), 'type' => 'number', 'value' => $height ] );
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_scale',  'label' => __( 'Scene scale',    'harmarium' ), 'type' => 'number', 'value' => $scale, 'custom_attributes' => [ 'step' => '0.01', 'min' => '0.05', 'max' => '1' ] ] );
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_x',      'label' => __( 'Scene X (0–1)', 'harmarium' ), 'type' => 'number', 'value' => $x, 'custom_attributes' => [ 'step' => '0.01', 'min' => '0', 'max' => '1' ] ] );
	woocommerce_wp_text_input( [ 'id' => '_harmarium_mockup_y',      'label' => __( 'Scene Y (0–1)', 'harmarium' ), 'type' => 'number', 'value' => $y, 'custom_attributes' => [ 'step' => '0.01', 'min' => '0', 'max' => '1' ] ] );

	/* Custom scene image */
	$preview_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	echo '<p class="form-field harmarium-media-picker"><label>' . esc_html__( 'Custom scene image', 'harmarium' ) . '</label>';
	echo '<span class="harmarium-media-picker__wrap">';
	if ( $preview_url ) echo '<img src="' . esc_url( $preview_url ) . '" style="max-width:120px;display:block;margin-bottom:.5rem">';
	echo '<input type="hidden" name="_harmarium_mockup_image" id="_harmarium_mockup_image" value="' . esc_attr( (string) $image_id ) . '">';
	echo '<button type="button" class="button harmarium-media-picker__choose">' . esc_html__( 'Choose image', 'harmarium' ) . '</button> ';
	echo '<button type="button" class="button harmarium-media-picker__remove"' . ( $image_id ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Remove', 'harmarium' ) . '</button>';
	echo '<span class="description">' . esc_html__( 'Optional JPG/PNG room scene. Overrides the preset scene above.', 'harmarium' ) . '</span></span></p>';
	echo '</div>';

	// Media picker JS
	?>
	<script>
	jQuery(function($){
		$('.harmarium-media-picker__choose').on('click', function(){
			var $w = $(this).closest('.harmarium-media-picker__wrap');
			var fr = wp.media({ title: 'Choose scene image', button: { text: 'Use this image' }, multiple: false });
			fr.on('select', function(){
				var att = fr.state().get('selection').first().toJSON();
				$w.find('input[type="hidden"]').val(att.id);
				$w.find('.harmarium-media-picker__remove').show();
				var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
				var img = $w.find('img');
				if (!img.length) $w.prepend('<img src="'+url+'" style="max-width:120px;display:block;margin-bottom:.5rem">'); else img.attr('src', url);
			});
			fr.open();
		});
		$('.harmarium-media-picker__remove').on('click', function(){
			var $w = $(this).closest('.harmarium-media-picker__wrap');
			$w.find('input[type="hidden"]').val('');
			$w.find('img').remove();
			$(this).hide();
		});
	});
	</script>
	<?php
} );

add_action( 'woocommerce_process_product_meta', function ( int $pid ): void {
	$int_keys   = [ '_harmarium_artwork_id', '_harmarium_mockup_image' ];
	$float_keys = [ '_harmarium_mockup_mat', '_harmarium_mockup_width', '_harmarium_mockup_height', '_harmarium_mockup_scale', '_harmarium_mockup_x', '_harmarium_mockup_y' ];
	$text_keys  = [ '_harmarium_mockup_scene', '_harmarium_mockup_frame', '_harmarium_canvas_type', '_harmarium_packaging' ];

	foreach ( $int_keys   as $k ) if ( isset( $_POST[$k] ) ) update_post_meta( $pid, $k, (int) $_POST[$k] );
	foreach ( $float_keys as $k ) if ( isset( $_POST[$k] ) ) update_post_meta( $pid, $k, (float) wp_unslash( $_POST[$k] ) );
	foreach ( $text_keys  as $k ) if ( isset( $_POST[$k] ) ) update_post_meta( $pid, $k, sanitize_text_field( wp_unslash( $_POST[$k] ) ) );
} );

/* ──────────────────────────────────────────────────────────────────
   Render the artwork viewer on single product pages
   ────────────────────────────────────────────────────────────────── */
add_action( 'woocommerce_before_single_product_summary', 'harmarium_render_artwork_viewer', 3 );

function harmarium_render_artwork_viewer(): void {
	global $product;
	if ( ! $product ) return;

	$pid = $product->get_id();
	$viewer_data = harmarium_build_viewer_data( $pid, 'product' );
	if ( ! $viewer_data ) return;

	harmarium_output_viewer( $viewer_data );

	// Remove default WooCommerce gallery (we're replacing it)
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
}

/* ──────────────────────────────────────────────────────────────────
   Render viewer on single portfolio pages (no WC)
   ────────────────────────────────────────────────────────────────── */
function harmarium_render_portfolio_viewer( int $post_id ): void {
	$viewer_data = harmarium_build_viewer_data( $post_id, 'portfolio' );
	if ( ! $viewer_data ) return;
	harmarium_output_viewer( $viewer_data );
}

/* ──────────────────────────────────────────────────────────────────
   Build viewer data array
   ────────────────────────────────────────────────────────────────── */
function harmarium_build_viewer_data( int $pid, string $context = 'product' ): ?array {
	// Resolve primary artwork image
	$artwork_id = (int) get_post_meta( $pid, '_harmarium_artwork_id', true );
	$images     = [];

	if ( $artwork_id && 'product' === $context ) {
		// Use linked portfolio item's featured image
		$thumb_id = get_post_thumbnail_id( $artwork_id );
		if ( $thumb_id ) {
			$full = wp_get_attachment_image_url( $thumb_id, 'full' );
			if ( $full ) $images[] = [ 'full' => $full, 'alt' => get_the_title( $artwork_id ) ];
		}
	}

	// Gather all gallery images
	if ( 'product' === $context ) {
		$product = wc_get_product( $pid );
		if ( $product ) {
			$main_id = $product->get_image_id();
			if ( $main_id && ! in_array( $main_id, wp_list_pluck( $images, 'full' ), true ) ) {
				$full = wp_get_attachment_image_url( $main_id, 'full' );
				if ( $full ) array_unshift( $images, [ 'full' => $full, 'alt' => get_post_meta( $main_id, '_wp_attachment_image_alt', true ) ] );
			}
			foreach ( $product->get_gallery_image_ids() as $gid ) {
				$full = wp_get_attachment_image_url( $gid, 'full' );
				if ( $full ) $images[] = [ 'full' => $full, 'alt' => get_post_meta( $gid, '_wp_attachment_image_alt', true ) ];
			}
		}
	} else {
		// Portfolio context
		$thumb_id = get_post_thumbnail_id( $pid );
		if ( $thumb_id ) {
			$full = wp_get_attachment_image_url( $thumb_id, 'full' );
			if ( $full ) $images[] = [ 'full' => $full, 'alt' => get_the_title( $pid ) ];
		}
		// Additional portfolio gallery images (via post_meta or ACF gallery)
		$gallery_ids = get_post_meta( $pid, '_hm_gallery_ids', true );
		if ( is_array( $gallery_ids ) ) {
			foreach ( $gallery_ids as $gid ) {
				$full = wp_get_attachment_image_url( (int) $gid, 'full' );
				if ( $full ) $images[] = [ 'full' => $full, 'alt' => get_post_meta( $gid, '_wp_attachment_image_alt', true ) ];
			}
		}
	}

	if ( empty( $images ) ) return null;

	// Mockup config
	$scene    = get_post_meta( $pid, '_harmarium_mockup_scene', true ) ?: get_theme_mod( 'harmarium_mockup_default_scene', 'living-room' );
	$frame    = get_post_meta( $pid, '_harmarium_mockup_frame', true ) ?: get_theme_mod( 'harmarium_mockup_default_frame', 'thin-black' );
	$canvas   = get_post_meta( $pid, '_harmarium_canvas_type', true )  ?: get_theme_mod( 'harmarium_mockup_default_canvas', 'stretched' );
	$pkg      = get_post_meta( $pid, '_harmarium_packaging', true )    ?: get_theme_mod( 'harmarium_mockup_default_pkg', 'ready-hang' );
	$scale    = (float) ( get_post_meta( $pid, '_harmarium_mockup_scale', true ) ?: 0.42 );
	$x        = (float) ( get_post_meta( $pid, '_harmarium_mockup_x',     true ) ?: 0.50 );
	$y        = (float) ( get_post_meta( $pid, '_harmarium_mockup_y',     true ) ?: 0.44 );
	$width    = get_post_meta( $pid, '_harmarium_mockup_width',  true );
	$height   = get_post_meta( $pid, '_harmarium_mockup_height', true );
	$image_id = (int) get_post_meta( $pid, '_harmarium_mockup_image', true );

	$scene_url = '';
	if ( $image_id ) {
		$scene_url = wp_get_attachment_image_url( $image_id, 'full' ) ?: '';
	}
	if ( ! $scene_url ) {
		$scene_url = get_template_directory_uri() . '/assets/images/scenes/' . sanitize_key( $scene ) . '.svg';
	}

	return [
		'pid'        => $pid,
		'context'    => $context,
		'images'     => $images,
		'scene'      => $scene,
		'scene_url'  => $scene_url,
		'frame'      => $frame,
		'canvas'     => $canvas,
		'packaging'  => $pkg,
		'scale'      => $scale,
		'x'          => $x,
		'y'          => $y,
		'width_cm'   => is_numeric( $width )  ? (float) $width  : null,
		'height_cm'  => is_numeric( $height ) ? (float) $height : null,
		'show_picker'=> (bool) get_theme_mod( 'harmarium_mockup_show_picker', 1 ),
		'scenes_base'=> get_template_directory_uri() . '/assets/images/scenes/',
	];
}

/* ──────────────────────────────────────────────────────────────────
   Output viewer HTML
   ────────────────────────────────────────────────────────────────── */
function harmarium_output_viewer( array $d ): void {
	$images  = $d['images'];
	$primary = $images[0];

	$pkg_labels = [];
	foreach ( HARMARIUM_PACKAGING_TYPES as $k => $v ) $pkg_labels[$k] = $v['label'];

	$config = [
		'images'          => $images,
		'scene'           => $d['scene'],
		'sceneKey'        => $d['scene'],
		'frame'           => $d['frame'],
		'canvas'          => $d['canvas'],
		'packaging'       => $d['packaging'],
		'scale'           => $d['scale'],
		'x'               => $d['x'],
		'y'               => $d['y'],
		'width_cm'        => $d['width_cm'],
		'height_cm'       => $d['height_cm'],
		'scenes'          => HARMARIUM_MOCKUP_SCENES,
		'frames'          => HARMARIUM_MOCKUP_FRAMES,
		'canvasTypes'     => HARMARIUM_CANVAS_TYPES,
		'packagingTypes'  => HARMARIUM_PACKAGING_TYPES,
		'packagingLabels' => $pkg_labels,
		'scenesBase'      => $d['scenes_base'],
	];
	?>
	<div class="hm-av" data-hm-av>
		<!-- ── Gallery panel ── -->
		<div class="hm-av__gallery">
			<!-- Mode toggle -->
			<div class="hm-av__mode-bar" role="group" aria-label="<?php esc_attr_e( 'View mode', 'harmarium' ); ?>">
				<button class="hm-av__mode-btn is-active" data-mode="artwork" type="button" aria-pressed="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
					<?php esc_html_e( 'Artwork', 'harmarium' ); ?>
				</button>
				<button class="hm-av__mode-btn" data-mode="mockup" type="button" aria-pressed="false">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
					<?php esc_html_e( 'In Room', 'harmarium' ); ?>
				</button>
			</div>

			<!-- Main stage -->
			<div class="hm-av__main" role="img" aria-label="<?php echo esc_attr( $primary['alt'] ?: get_the_title() ); ?>">
				<!-- Artwork view -->
				<div class="hm-av__img-wrap">
					<div class="hm-av__artwork-wrap" data-frame="<?php echo esc_attr( $d['frame'] ); ?>" data-canvas="<?php echo esc_attr( $d['canvas'] ); ?>">
						<img class="hm-av__artwork-img"
							src="<?php echo esc_url( $primary['full'] ); ?>"
							alt="<?php echo esc_attr( $primary['alt'] ?: get_the_title() ); ?>"
							loading="eager" decoding="async">
					</div>
				</div>

				<!-- Mockup / In-room stage -->
				<div class="hm-av__mockup-stage" aria-hidden="true">
					<img class="hm-av__mockup-scene"
						src="<?php echo esc_url( $d['scene_url'] ); ?>"
						alt="" loading="lazy" decoding="async">
					<div class="hm-av__mockup-art" data-frame="<?php echo esc_attr( $d['frame'] ); ?>">
						<img src="<?php echo esc_url( $primary['full'] ); ?>" alt="" draggable="false">
					</div>
					<?php if ( $d['show_picker'] ) : ?>
					<div class="hm-av__mockup-controls">
						<select class="hm-av__scene-select" aria-label="<?php esc_attr_e( 'Room scene', 'harmarium' ); ?>">
							<?php foreach ( HARMARIUM_MOCKUP_SCENES as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $d['scene'], $k ); ?>>
									<?php echo esc_html( $v['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<label class="screen-reader-text" for="hm-scale-<?php echo esc_attr( (string) $d['pid'] ); ?>"><?php esc_html_e( 'Artwork size', 'harmarium' ); ?></label>
						<input class="hm-av__scale-input" id="hm-scale-<?php echo esc_attr( (string) $d['pid'] ); ?>"
							type="range" min="0.08" max="0.85" step="0.01" value="<?php echo esc_attr( (string) $d['scale'] ); ?>"
							aria-label="<?php esc_attr_e( 'Artwork size in room', 'harmarium' ); ?>">
						<?php if ( $d['width_cm'] && $d['height_cm'] ) : ?>
							<span class="hm-av__mockup-size"><?php echo esc_html( $d['width_cm'] . ' × ' . $d['height_cm'] . ' cm' ); ?></span>
						<?php endif; ?>
						<button class="hm-av__mockup-reset" type="button"><?php esc_html_e( 'Reset', 'harmarium' ); ?></button>
					</div>
					<?php endif; ?>
				</div>

				<!-- Zoom button (artwork mode) -->
				<button class="hm-av__zoom-btn" type="button" aria-label="<?php esc_attr_e( 'View full size', 'harmarium' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
				</button>
			</div>

			<!-- Thumbnails -->
			<?php if ( count( $images ) > 1 ) : ?>
			<div class="hm-av__thumbs" role="list" aria-label="<?php esc_attr_e( 'Artwork images', 'harmarium' ); ?>">
				<?php foreach ( $images as $i => $img ) : ?>
					<div class="hm-av__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>"
						role="listitem" aria-label="<?php printf( esc_attr__( 'Image %d', 'harmarium' ), $i + 1 ); ?>">
						<img src="<?php echo esc_url( $img['full'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" loading="lazy">
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<!-- ── Configurator panel ── -->
		<div class="hm-av__config">
			<?php if ( 'product' === $d['context'] ) : ?>
				<!-- WooCommerce summary injected by WC into .entry-summary — nothing needed here -->
				<!-- We add hidden cart fields for the configurator selections -->
				<input type="hidden" name="hm_frame" value="<?php echo esc_attr( $d['frame'] ); ?>">
				<input type="hidden" name="hm_canvas_type" value="<?php echo esc_attr( $d['canvas'] ); ?>">
				<input type="hidden" name="hm_packaging" value="<?php echo esc_attr( $d['packaging'] ); ?>">
			<?php endif; ?>

			<!-- Frame selector -->
			<div class="hm-av__section">
				<div class="hm-av__section-label">
					<?php esc_html_e( 'Frame', 'harmarium' ); ?>
					<span class="hm-av__section-value" data-label="frame"><?php echo esc_html( HARMARIUM_MOCKUP_FRAMES[ $d['frame'] ] ?? $d['frame'] ); ?></span>
				</div>
				<div class="hm-av__frame-swatches" role="group" aria-label="<?php esc_attr_e( 'Choose frame', 'harmarium' ); ?>">
					<?php foreach ( HARMARIUM_MOCKUP_FRAMES as $k => $label ) : ?>
						<div class="hm-av__frame-swatch<?php echo $k === $d['frame'] ? ' is-active' : ''; ?>"
							data-frame-swatch="<?php echo esc_attr( $k ); ?>"
							title="<?php echo esc_attr( $label ); ?>"
							role="button" tabindex="0" aria-label="<?php echo esc_attr( $label ); ?>"
							aria-pressed="<?php echo $k === $d['frame'] ? 'true' : 'false'; ?>">
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Canvas type selector -->
			<div class="hm-av__section">
				<div class="hm-av__section-label">
					<?php esc_html_e( 'Canvas', 'harmarium' ); ?>
					<span class="hm-av__section-value" data-label="canvas"><?php echo esc_html( HARMARIUM_CANVAS_TYPES[ $d['canvas'] ]['label'] ?? $d['canvas'] ); ?></span>
				</div>
				<div class="hm-av__canvas-options" role="group" aria-label="<?php esc_attr_e( 'Choose canvas type', 'harmarium' ); ?>">
					<?php foreach ( HARMARIUM_CANVAS_TYPES as $k => $v ) : ?>
						<label class="hm-av__canvas-option<?php echo $k === $d['canvas'] ? ' is-active' : ''; ?>" data-canvas="<?php echo esc_attr( $k ); ?>">
							<input type="radio" name="hm_canvas_ui" value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $d['canvas'] ); ?>>
							<span class="hm-av__canvas-icon hm-av__canvas-icon--<?php echo esc_attr( $k ); ?>"></span>
							<span class="hm-av__canvas-name"><?php echo esc_html( $v['label'] ); ?></span>
							<span class="hm-av__canvas-desc"><?php echo esc_html( $v['desc'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Packaging selector -->
			<div class="hm-av__section">
				<div class="hm-av__section-label">
					<?php esc_html_e( 'Packaging', 'harmarium' ); ?>
					<span class="hm-av__section-value" data-label="packaging"><?php echo esc_html( HARMARIUM_PACKAGING_TYPES[ $d['packaging'] ]['label'] ?? $d['packaging'] ); ?></span>
				</div>
				<div class="hm-av__packaging-options" role="group" aria-label="<?php esc_attr_e( 'Choose packaging', 'harmarium' ); ?>">
					<?php foreach ( HARMARIUM_PACKAGING_TYPES as $k => $v ) : ?>
						<label class="hm-av__pkg-option<?php echo $k === $d['packaging'] ? ' is-active' : ''; ?>" data-pkg="<?php echo esc_attr( $k ); ?>">
							<input type="radio" name="hm_packaging_ui" value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $d['packaging'] ); ?>>
							<span class="hm-av__pkg-icon" aria-hidden="true"><?php echo $v['icon']; ?></span>
							<span class="hm-av__pkg-body">
								<span class="hm-av__pkg-name"><?php echo esc_html( $v['label'] ); ?></span>
								<span class="hm-av__pkg-desc"><?php echo esc_html( $v['desc'] ); ?></span>
							</span>
							<span class="hm-av__pkg-check" aria-hidden="true"></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Trust badges -->
			<div class="hm-av__trust">
				<span class="hm-av__trust-item">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
					<?php esc_html_e( 'Certificate of Authenticity', 'harmarium' ); ?>
				</span>
				<span class="hm-av__trust-item">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
					<?php esc_html_e( '14-day returns', 'harmarium' ); ?>
				</span>
				<span class="hm-av__trust-item">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					<?php esc_html_e( 'Made to order', 'harmarium' ); ?>
				</span>
			</div>
		</div>

		<!-- Config JSON for JS -->
		<script type="application/json" class="hm-av__config-json"><?php echo wp_json_encode( $config ); ?></script>
	</div>
	<?php
}

/* ──────────────────────────────────────────────────────────────────
   Cart item data — save viewer selections
   ────────────────────────────────────────────────────────────────── */
add_filter( 'woocommerce_add_cart_item_data', function ( array $cart_item, int $product_id ): array {
	$fields = [ 'hm_frame' => 'Frame', 'hm_canvas_type' => 'Canvas', 'hm_packaging' => 'Packaging' ];
	foreach ( $fields as $field => $_ ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			$cart_item[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}
	return $cart_item;
}, 10, 2 );

add_filter( 'woocommerce_get_item_data', function ( array $item_data, array $cart_item ): array {
	$labels = [ 'hm_frame' => __( 'Frame', 'harmarium' ), 'hm_canvas_type' => __( 'Canvas', 'harmarium' ), 'hm_packaging' => __( 'Packaging', 'harmarium' ) ];
	$frame_labels   = HARMARIUM_MOCKUP_FRAMES;
	$canvas_labels  = wp_list_pluck( HARMARIUM_CANVAS_TYPES, 'label' );
	$pkg_labels     = wp_list_pluck( HARMARIUM_PACKAGING_TYPES, 'label' );
	$value_maps     = [ 'hm_frame' => $frame_labels, 'hm_canvas_type' => $canvas_labels, 'hm_packaging' => $pkg_labels ];

	foreach ( $labels as $key => $label ) {
		if ( ! empty( $cart_item[ $key ] ) ) {
			$raw   = $cart_item[ $key ];
			$value = $value_maps[ $key ][ $raw ] ?? ucfirst( str_replace( '-', ' ', $raw ) );
			$item_data[] = [ 'name' => $label, 'value' => $value ];
		}
	}
	return $item_data;
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order_line_item', function ( \WC_Order_Item_Product $item, string $_, array $cart_item ): void {
	$fields = [ 'hm_frame' => __( 'Frame', 'harmarium' ), 'hm_canvas_type' => __( 'Canvas', 'harmarium' ), 'hm_packaging' => __( 'Packaging', 'harmarium' ) ];
	foreach ( $fields as $key => $label ) {
		if ( ! empty( $cart_item[ $key ] ) ) {
			$item->add_meta_data( $label, sanitize_text_field( $cart_item[ $key ] ), true );
		}
	}
}, 10, 3 );

/* ──────────────────────────────────────────────────────────────────
   FSE block compatibility — intercept WooCommerce gallery block
   and replace with our artwork viewer on single product pages.
   ────────────────────────────────────────────────────────────────── */
add_filter( 'render_block_woocommerce/product-image-gallery', function ( string $html, array $block ): string {
	if ( ! is_singular( 'product' ) ) {
		return $html;
	}

	global $product;
	if ( ! $product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		return $html;
	}

	$d = harmarium_build_viewer_data( $product->get_id(), 'product' );
	if ( ! $d ) {
		return $html;
	}

	ob_start();
	harmarium_output_viewer( $d );
	return (string) ob_get_clean();
}, 10, 2 );

/* ──────────────────────────────────────────────────────────────────
   FSE block compatibility — intercept first post-featured-image
   block on single portfolio pages and render the artwork viewer.
   ────────────────────────────────────────────────────────────────── */
add_filter( 'render_block_core/post-featured-image', function ( string $html, array $block ): string {
	if ( ! is_singular( 'portfolio' ) ) {
		return $html;
	}

	static $done = false;
	if ( $done ) {
		return $html;
	}

	$pid = get_the_ID();
	if ( ! $pid ) {
		return $html;
	}

	$d = harmarium_build_viewer_data( (int) $pid, 'portfolio' );
	if ( ! $d ) {
		return $html;
	}

	$done = true;
	ob_start();
	harmarium_output_viewer( $d );
	return (string) ob_get_clean();
}, 10, 2 );
