<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'mai_notice', 'mai_notice_shortcode_callback' );
/**
 * Registers a shortcode for displaying a notice outside of the block editor.
 *
 * @since 1.0.0
 *
 * @param array $atts The shortcode attributes.
 *
 * @return string
 */
function mai_notice_shortcode_callback( $atts, $content = null ) {
	$atts['content'] = $content;

	return mai_get_notice( $atts );
}

/**
 * Returns a notice.
 *
 * @since 1.0.0
 *
 * @param array $args The notice args.
 * @param bool  $block If the notice is coming from a block. This disables the processing of incoming content.
 *
 * @return string
 */
function mai_get_notice( $args, $block = false ) {
	$notice = new Mai_Notice( $args, $block );

	return $notice->get();
}

/**
 * Gets all of the available notice types.
 *
 * @since 1.0.0
 *
 * @return array
 */
function mai_notice_get_types() {
	static $types = null;

	if ( ! is_null( $types ) ) {
		return $types;
	}

	$types = [
		'info' => [
			'title'   => __( 'Info', 'mai-notices' ),
			'style'   => 'light',
			'icon'    => 'info-circle',
			'color'   => '#0da7e4',
			'default' => true,
		],
		'note' => [
			'title' => __( 'Note', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'pencil',
			'color' => '#0da7e4',
		],
		'bookmark' => [
			'title' => __( 'Bookmark', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'bookmark',
			'color' => '#055e9a',
		],
		'idea' => [
			'title' => __( 'Idea', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'lightbulb-on',
			'color' => '#f7cf00',
		],
		'alert'   => [
			'title' => __( 'Alert', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'exclamation-circle',
			'color' => '#fea320',
		],
		'success' => [
			'title' => __( 'Success', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'check-circle',
			'color' => '#00cd51',
		],
		'error'   => [
			'title' => __( 'Error', 'mai-notices' ),
			'style' => 'light',
			'icon'  => 'times-circle',
			'color' => '#fd0010',
		],
	];

	// Filter types.
	$types = apply_filters( 'mai_notices_types', $types );

	// Add Custom to the end.
	$types['custom'] = [
		'title' => __( 'None/Custom', 'mai-notices' ),
		'style' => 'light',
		'icon'  => null,
		'color' => null,
	];

	return $types;
}

/**
 * Enqueues the notices styles.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mai_notice_enqueue_style() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	wp_register_style( 'mai-notices', MAI_NOTICES_PLUGIN_URL . "assets/css/mai-notices{$suffix}.css", [], MAI_NOTICES_VERSION );
	wp_enqueue_style( 'mai-notices' );
}

/**
 * Taken from `mai_get_processed_content()` in Mai Engine plugin.
 *
 * @since 1.0.0
 *
 * @param string $content The unprocessed content.
 *
 * @return string
 */
function mai_notice_get_processed_content( $content ) {
	/**
	 * Embed.
	 *
	 * @var WP_Embed $wp_embed Embed object.
	 */
	global $wp_embed;

	$content = $wp_embed->autoembed( $content );     // WP runs priority 8.
	$content = $wp_embed->run_shortcode( $content ); // WP runs priority 8.
	$content = do_blocks( $content );                // WP runs priority 9.
	$content = wptexturize( $content );              // WP runs priority 10.
	$content = wpautop( $content );                  // WP runs priority 10.
	$content = shortcode_unautop( $content );        // WP runs priority 10.
	$content = function_exists( 'wp_filter_content_tags' ) ? wp_filter_content_tags( $content ) : wp_make_content_images_responsive( $content ); // WP runs priority 10. WP 5.5 with fallback.
	$content = do_shortcode( $content );             // WP runs priority 11.
	$content = convert_smilies( $content );          // WP runs priority 20.

	return $content;
}

add_action( 'acf/input/admin_head', 'mai_notice_custom_css' );
/**
 * Adds custom admin CSS for the block fields.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mai_notice_custom_css() {
	?>
	<style class="mai-notice-editor-css">
		.acf-field-5dd6c75b0ea87 .select2-container .select2-selection--single .select2-selection__rendered {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-align: center;
			-ms-flex-align: center;
			align-items: center;
			height: 100%;
		}
		.acf-field-5dd6c75b0ea87 .mai-notice-icon-svg {
			max-width: 18px;
			max-height: 18px;
		}
		.acf-field-5dd6c75b0ea87 .mai-notice-icon-name {
			margin-left: 8px;
		}
		.editor-styles-wrapper .mai-notice p {
			margin-top: 0;
		}
		.editor-styles-wrapper .mai-notice p:last-of-type {
			margin-bottom: 0;
		}
		#select2-acf-block_5f4eac01d5191-field_5dd6c75b0ea87-results {
			display: grid;
			grid-template-columns: repeat(3, 33.33333%);
			overflow-x: hidden;
		}
		#select2-acf-block_5f4eac01d5191-field_5dd6c75b0ea87-results .select2-results__option:not(.loading-results):not(.select2-results__message) {
			margin: 0;
			padding: 8px 12px;
		}
		#select2-acf-block_5f4eac01d5191-field_5dd6c75b0ea87-results .select2-results__option .mai-notice-icon-svg {
			display: block;
			max-width: 36px;
			max-height: 36px;
			margin: auto;
		}
		#select2-acf-block_5f4eac01d5191-field_5dd6c75b0ea87-results .select2-results__option .mai-notice-icon-name {
			display:block;
			margin: 4px auto 8px;
			opacity: 0.6;
		}
	</style>
	<?php
}
