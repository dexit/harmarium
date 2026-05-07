<?php
/**
 * Real-life product mockup integration.
 *
 * Each WooCommerce product can be linked to a portfolio (artwork) item and
 * configured with a mockup scene (room, frame, mat, dimensions). The single
 * product page renders an interactive preview that composites the artwork
 * onto the chosen scene; all options are configurable per-product and the
 * defaults are configurable site-wide via the Customizer.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const HARMARIUM_MOCKUP_SCENES = array(
	'living-room'   => array( 'label' => 'Living room',     'aspect' => '16/10', 'wall' => '#efe5d4' ),
	'minimal-loft'  => array( 'label' => 'Minimal loft',    'aspect' => '16/10', 'wall' => '#f6f3ec' ),
	'studio-wall'   => array( 'label' => 'Studio wall',     'aspect' => '4/3',   'wall' => '#e8e0d2' ),
	'concrete-loft' => array( 'label' => 'Concrete loft',   'aspect' => '16/9',  'wall' => '#cfcdc7' ),
	'dark-gallery'  => array( 'label' => 'Dark gallery',    'aspect' => '16/10', 'wall' => '#22201d' ),
	'sunlit-room'   => array( 'label' => 'Sunlit room',     'aspect' => '4/3',   'wall' => '#f1e5cf' ),
);

const HARMARIUM_MOCKUP_FRAMES = array(
	'none'    => 'No frame',
	'thin-black' => 'Thin black',
	'thick-black' => 'Thick black',
	'thin-white' => 'Thin white',
	'oak'     => 'Natural oak',
	'walnut'  => 'Walnut',
	'gold'    => 'Gold leaf',
);

/**
 * Register product meta + REST exposure.
 */
function harmarium_register_product_mockup_meta() {
	$keys = array(
		'_harmarium_artwork_id'   => array( 'type' => 'integer' ),
		'_harmarium_mockup_scene' => array( 'type' => 'string'  ),
		'_harmarium_mockup_frame' => array( 'type' => 'string'  ),
		'_harmarium_mockup_mat'   => array( 'type' => 'string'  ),
		'_harmarium_mockup_width' => array( 'type' => 'number'  ),
		'_harmarium_mockup_height'=> array( 'type' => 'number'  ),
		'_harmarium_mockup_scale' => array( 'type' => 'number'  ),
		'_harmarium_mockup_x'     => array( 'type' => 'number'  ),
		'_harmarium_mockup_y'     => array( 'type' => 'number'  ),
		'_harmarium_mockup_image' => array( 'type' => 'integer' ),
	);
	foreach ( $keys as $key => $args ) {
		register_post_meta( 'product', $key, array_merge( $args, array(
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_products' ); },
		) ) );
	}
}
add_action( 'init', 'harmarium_register_product_mockup_meta', 7 );

/**
 * Customizer defaults so the mockup is "all configurable".
 */
function harmarium_mockup_customizer( $wp_customize ) {
	$wp_customize->add_section( 'harmarium_mockup', array(
		'title'    => __( 'Harmarium · Product mockup', 'harmarium' ),
		'priority' => 35,
	) );

	$controls = array(
		'harmarium_mockup_default_scene' => array(
			'label'   => __( 'Default mockup scene', 'harmarium' ),
			'type'    => 'select',
			'choices' => wp_list_pluck( HARMARIUM_MOCKUP_SCENES, 'label' ),
			'default' => 'living-room',
		),
		'harmarium_mockup_default_frame' => array(
			'label'   => __( 'Default frame', 'harmarium' ),
			'type'    => 'select',
			'choices' => HARMARIUM_MOCKUP_FRAMES,
			'default' => 'thin-black',
		),
		'harmarium_mockup_default_mat' => array(
			'label'   => __( 'Default mat (white border) %', 'harmarium' ),
			'type'    => 'number',
			'default' => 6,
		),
		'harmarium_mockup_show_picker' => array(
			'label'   => __( 'Allow visitors to switch scene/frame', 'harmarium' ),
			'type'    => 'checkbox',
			'default' => 1,
		),
		'harmarium_mockup_realtime' => array(
			'label'   => __( 'Live re-render on option change', 'harmarium' ),
			'type'    => 'checkbox',
			'default' => 1,
		),
	);
	foreach ( $controls as $id => $args ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $args['default'] ?? '',
			'transport'         => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array_merge( $args, array(
			'section' => 'harmarium_mockup',
		) ) );
	}
}
add_action( 'customize_register', 'harmarium_mockup_customizer' );

/**
 * Product editor: a "Harmarium Mockup" tab.
 */
add_filter( 'woocommerce_product_data_tabs', function ( $tabs ) {
	$tabs['harmarium_mockup'] = array(
		'label'    => __( 'Harmarium Mockup', 'harmarium' ),
		'target'   => 'harmarium_mockup_data',
		'priority' => 65,
		'class'    => array(),
	);
	return $tabs;
} );

add_action( 'woocommerce_product_data_panels', function () {
	global $post;
	$artwork_id = (int) get_post_meta( $post->ID, '_harmarium_artwork_id', true );
	$scene      = get_post_meta( $post->ID, '_harmarium_mockup_scene', true ) ?: get_theme_mod( 'harmarium_mockup_default_scene', 'living-room' );
	$frame      = get_post_meta( $post->ID, '_harmarium_mockup_frame', true ) ?: get_theme_mod( 'harmarium_mockup_default_frame', 'thin-black' );
	$mat        = get_post_meta( $post->ID, '_harmarium_mockup_mat', true );
	$width      = get_post_meta( $post->ID, '_harmarium_mockup_width', true );
	$height     = get_post_meta( $post->ID, '_harmarium_mockup_height', true );
	$scale      = get_post_meta( $post->ID, '_harmarium_mockup_scale', true ) ?: 0.4;
	$x          = get_post_meta( $post->ID, '_harmarium_mockup_x', true ) ?: 0.5;
	$y          = get_post_meta( $post->ID, '_harmarium_mockup_y', true ) ?: 0.45;
	$image_id   = (int) get_post_meta( $post->ID, '_harmarium_mockup_image', true );

	$portfolio = get_posts( array( 'post_type' => 'portfolio', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	echo '<div id="harmarium_mockup_data" class="panel woocommerce_options_panel">';
	echo '<p class="form-field"><label for="_harmarium_artwork_id">' . esc_html__( 'Linked artwork', 'harmarium' ) . '</label>';
	echo '<select name="_harmarium_artwork_id" id="_harmarium_artwork_id"><option value="">' . esc_html__( '— None —', 'harmarium' ) . '</option>';
	foreach ( $portfolio as $p ) {
		echo '<option value="' . (int) $p->ID . '" ' . selected( $artwork_id, $p->ID, false ) . '>' . esc_html( get_the_title( $p ) ) . '</option>';
	}
	echo '</select></p>';

	echo '<p class="form-field"><label for="_harmarium_mockup_scene">' . esc_html__( 'Scene', 'harmarium' ) . '</label><select name="_harmarium_mockup_scene" id="_harmarium_mockup_scene">';
	foreach ( HARMARIUM_MOCKUP_SCENES as $k => $v ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $scene, $k, false ) . '>' . esc_html( $v['label'] ) . '</option>';
	}
	echo '</select></p>';

	echo '<p class="form-field"><label for="_harmarium_mockup_frame">' . esc_html__( 'Frame', 'harmarium' ) . '</label><select name="_harmarium_mockup_frame" id="_harmarium_mockup_frame">';
	foreach ( HARMARIUM_MOCKUP_FRAMES as $k => $v ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $frame, $k, false ) . '>' . esc_html( $v ) . '</option>';
	}
	echo '</select></p>';

	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_mat',    'label' => __( 'Mat (% of frame)',    'harmarium' ), 'type' => 'number', 'value' => $mat ) );
	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_width',  'label' => __( 'Real width (cm)',     'harmarium' ), 'type' => 'number', 'value' => $width ) );
	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_height', 'label' => __( 'Real height (cm)',    'harmarium' ), 'type' => 'number', 'value' => $height ) );
	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_scale',  'label' => __( 'Wall scale (0–1)',    'harmarium' ), 'type' => 'number', 'value' => $scale, 'custom_attributes' => array( 'step' => '0.01', 'min' => '0', 'max' => '1' ) ) );
	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_x',      'label' => __( 'X position (0–1)',    'harmarium' ), 'type' => 'number', 'value' => $x,     'custom_attributes' => array( 'step' => '0.01', 'min' => '0', 'max' => '1' ) ) );
	woocommerce_wp_text_input( array( 'id' => '_harmarium_mockup_y',      'label' => __( 'Y position (0–1)',    'harmarium' ), 'type' => 'number', 'value' => $y,     'custom_attributes' => array( 'step' => '0.01', 'min' => '0', 'max' => '1' ) ) );

	$preview_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	echo '<p class="form-field harmarium-media-picker"><label>' . esc_html__( 'Custom scene image', 'harmarium' ) . '</label>';
	echo '<span class="harmarium-media-picker__wrap">';
	if ( $preview_url ) {
		echo '<img src="' . esc_url( $preview_url ) . '" style="max-width:120px;height:auto;display:block;margin-bottom:.5rem" />';
	}
	echo '<input type="hidden" name="_harmarium_mockup_image" id="_harmarium_mockup_image" value="' . esc_attr( $image_id ) . '" />';
	echo '<button type="button" class="button harmarium-media-picker__choose">' . esc_html__( 'Choose image', 'harmarium' ) . '</button> ';
	echo '<button type="button" class="button harmarium-media-picker__remove"' . ( $image_id ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Remove', 'harmarium' ) . '</button>';
	echo '<span class="description">' . esc_html__( 'Optional custom room/scene. Overrides the preset above.', 'harmarium' ) . '</span></span></p>';
	echo '</div>';

	// Inline script — runs once inside the product editor panel.
	?>
	<script>
	jQuery(function($){
		$('.harmarium-media-picker__choose').on('click', function(){
			var $wrap = $(this).closest('.harmarium-media-picker__wrap');
			var frame = wp.media({ title: '<?php echo esc_js( __( 'Choose scene image', 'harmarium' ) ); ?>', button: { text: '<?php echo esc_js( __( 'Use this image', 'harmarium' ) ); ?>' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$wrap.find('input[type="hidden"]').val(att.id);
				$wrap.find('.harmarium-media-picker__remove').show();
				var preview = $wrap.find('img');
				var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				if (!preview.length) { $wrap.prepend('<img src="' + url + '" style="max-width:120px;height:auto;display:block;margin-bottom:.5rem" />'); } else { preview.attr('src', url); }
			});
			frame.open();
		});
		$('.harmarium-media-picker__remove').on('click', function(){
			var $wrap = $(this).closest('.harmarium-media-picker__wrap');
			$wrap.find('input[type="hidden"]').val('');
			$wrap.find('img').remove();
			$(this).hide();
		});
	});
	</script>
	<?php
} );

add_action( 'woocommerce_process_product_meta', function ( $post_id ) {
	$int_keys   = array( '_harmarium_artwork_id', '_harmarium_mockup_image' );
	$float_keys = array( '_harmarium_mockup_mat', '_harmarium_mockup_width', '_harmarium_mockup_height', '_harmarium_mockup_scale', '_harmarium_mockup_x', '_harmarium_mockup_y' );
	$text_keys  = array( '_harmarium_mockup_scene', '_harmarium_mockup_frame' );

	foreach ( $int_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, (int) $_POST[ $key ] );
		}
	}
	foreach ( $float_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, (float) wp_unslash( $_POST[ $key ] ) );
		}
	}
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

/**
 * Render the interactive mockup preview on the single product page.
 */
add_action( 'woocommerce_before_single_product_summary', 'harmarium_render_product_mockup', 5 );
function harmarium_render_product_mockup() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$pid          = $product->get_id();
	$artwork_id   = (int) get_post_meta( $pid, '_harmarium_artwork_id', true );
	$artwork_src  = '';
	if ( $artwork_id ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $artwork_id ), 'full' );
		if ( $src ) {
			$artwork_src = $src[0];
		}
	}
	if ( ! $artwork_src ) {
		$src = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
		if ( $src ) {
			$artwork_src = $src[0];
		}
	}
	if ( ! $artwork_src ) {
		return;
	}

	$scene      = get_post_meta( $pid, '_harmarium_mockup_scene', true ) ?: get_theme_mod( 'harmarium_mockup_default_scene', 'living-room' );
	$frame      = get_post_meta( $pid, '_harmarium_mockup_frame', true ) ?: get_theme_mod( 'harmarium_mockup_default_frame', 'thin-black' );
	$mat        = (float) ( get_post_meta( $pid, '_harmarium_mockup_mat', true ) ?: get_theme_mod( 'harmarium_mockup_default_mat', 6 ) );
	$scale      = (float) ( get_post_meta( $pid, '_harmarium_mockup_scale', true ) ?: 0.4 );
	$x          = (float) ( get_post_meta( $pid, '_harmarium_mockup_x', true ) ?: 0.5 );
	$y          = (float) ( get_post_meta( $pid, '_harmarium_mockup_y', true ) ?: 0.45 );
	$width      = get_post_meta( $pid, '_harmarium_mockup_width', true );
	$height     = get_post_meta( $pid, '_harmarium_mockup_height', true );
	$image_id   = (int) get_post_meta( $pid, '_harmarium_mockup_image', true );
	$picker     = (int) get_theme_mod( 'harmarium_mockup_show_picker', 1 );
	$realtime   = (int) get_theme_mod( 'harmarium_mockup_realtime', 1 );

	$scene_url  = '';
	if ( $image_id ) {
		$src = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $src ) {
			$scene_url = $src[0];
		}
	}
	if ( ! $scene_url ) {
		$scene_url = HARMARIUM_URI . '/assets/images/scenes/' . $scene . '.jpg';
	}

	$config = array(
		'artwork'   => $artwork_src,
		'scene'     => $scene_url,
		'sceneKey'  => $scene,
		'frame'     => $frame,
		'mat'       => $mat,
		'scale'     => $scale,
		'x'         => $x,
		'y'         => $y,
		'width'     => is_numeric( $width )  ? (float) $width  : null,
		'height'    => is_numeric( $height ) ? (float) $height : null,
		'scenes'    => HARMARIUM_MOCKUP_SCENES,
		'frames'    => HARMARIUM_MOCKUP_FRAMES,
		'picker'    => (bool) $picker,
		'realtime'  => (bool) $realtime,
		'i18n'      => array(
			'preview'    => __( 'See it on a wall', 'harmarium' ),
			'scene'      => __( 'Room', 'harmarium' ),
			'frame'      => __( 'Frame', 'harmarium' ),
			'reset'      => __( 'Reset', 'harmarium' ),
			'realSize'   => __( 'Actual size', 'harmarium' ),
		),
	);
	?>
	<figure class="harmarium-mockup" data-harmarium-mockup>
		<div class="harmarium-mockup__stage" aria-label="<?php esc_attr_e( 'Real-life preview', 'harmarium' ); ?>">
			<img class="harmarium-mockup__scene" src="<?php echo esc_url( $scene_url ); ?>" alt="" loading="lazy" decoding="async" />
			<div class="harmarium-mockup__art" data-harmarium-art>
				<img src="<?php echo esc_url( $artwork_src ); ?>" alt="" />
			</div>
		</div>
		<?php if ( $picker ) : ?>
		<div class="harmarium-mockup__controls">
			<label><span><?php esc_html_e( 'Room', 'harmarium' ); ?></span>
				<select data-harmarium-control="scene">
					<?php foreach ( HARMARIUM_MOCKUP_SCENES as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $scene, $k ); ?>><?php echo esc_html( $v['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label><span><?php esc_html_e( 'Frame', 'harmarium' ); ?></span>
				<select data-harmarium-control="frame">
					<?php foreach ( HARMARIUM_MOCKUP_FRAMES as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $frame, $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label><span><?php esc_html_e( 'Size', 'harmarium' ); ?></span>
				<input type="range" min="0.1" max="0.85" step="0.01" value="<?php echo esc_attr( $scale ); ?>" data-harmarium-control="scale" />
			</label>
			<button type="button" class="harmarium-mockup__reset" data-harmarium-control="reset"><?php esc_html_e( 'Reset', 'harmarium' ); ?></button>
		</div>
		<?php endif; ?>
		<script type="application/json" class="harmarium-mockup__config"><?php echo wp_json_encode( $config ); ?></script>
	</figure>
	<?php
}
