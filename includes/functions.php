<?php

function mai_get_notice( $args ) {
	$notice = new Mai_Notice( $args );
	return $notice->get();
}

function mai_notice_get_types() {
	$types = null;

	if ( ! is_null( $types ) ) {
		return $types;
	}

	$types = array(
		'info' => array(
			'title' => __( 'Info', 'mai-notices' ),
			'icon'  => 'info-circle',
			'color' => '#0da7e4',
		),
		'note' => array(
			'title' => __( 'Note', 'mai-notices' ),
			'icon'  => 'pencil',
			'color' => '#0da7e4',
		),
		'bookmark' => array(
			'title' => __( 'Bookmark', 'mai-notices' ),
			'icon'  => 'bookmark',
			'color' => '#055e9a',
		),
		'idea' => array(
			'title' => __( 'Idea', 'mai-notices' ),
			'icon'  => 'lightbulb-on',
			'color' => '#f7cf00',
		),
		'alert'   => array(
			'title' => __( 'Alert', 'mai-notices' ),
			'icon'  => 'exclamation-circle',
			'color' => '#fea320',
		),
		'success' => array(
			'title' => __( 'Success', 'mai-notices' ),
			'icon'  => 'check-circle',
			'color' => '#00cd51',
		),
		'error'   => array(
			'title' => __( 'Error', 'mai-notices' ),
			'icon'  => 'times-circle',
			'color' => '#fd0010',
		),
	);

	// Filter types.
	$types = apply_filters( 'mai_notices_types', $types );

	// Add Custom to the end.
	$types['custom'] = array(
		'title' => __( 'Custom', 'mai-notices' ),
		'icon'  => get_field( 'icon' ),
		'color' => get_field( 'color' ),
	);

	return $types;
}

function mai_notice_get_icons_dir() {
	return MAI_NOTICES_PLUGIN_DIR . 'assets/icons/';
}

function mai_notice_get_icons_url() {
	return MAI_NOTICES_PLUGIN_URL . 'assets/icons/';
}

// add_action( 'admin_enqueue_scripts', 'mai_notice_enqueue_style' );
// add_action( 'wp_enqueue_scripts', 'mai_notice_register_style' );
function mai_notice_register_style() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	wp_register_style( 'mai-notices', MAI_NOTICES_PLUGIN_URL . "assets/css/mai-notices{$suffix}.css", [], MAI_NOTICES_VERSION );
}

function mai_notice_enqueue_style() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	wp_register_style( 'mai-notices', MAI_NOTICES_PLUGIN_URL . "assets/css/mai-notices{$suffix}.css", [], MAI_NOTICES_VERSION );
	wp_enqueue_style( 'mai-notices' );
}

add_action( 'acf/input/admin_head', 'mai_notice_custom_css' );
function mai_notice_custom_css() {
	?>
	<style class="mai-notice-editor-css">
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-ms-flex-wrap: wrap;
			flex-wrap: wrap;
		}
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results .select2-results__option:not(.loading-results):not(.select2-results__message) {
			-webkit-box-flex: 1;
			-ms-flex: 1 1 72px;
			flex: 1 1 72px;
			max-width: 72px;
			text-align: center;
		}
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results .select2-results__option.loading-results,
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results .select2-results__option.select2-results__message {
			-webkit-box-flex: 1;
			-ms-flex: 1 1 100%;
			flex: 1 1 100%;
			max-width: 100%;
		}
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results .select2-results__option .mai-notice-icon-svg {
			max-width: 36px;
			max-height: 36px;
		}
		#select2-acf-block_5dd6bce9af42c-field_5dd6c75b0ea87-results .select2-results__option .mai-notice-icon-name {
			display:block;
			margin: 4px auto 8px;
			opacity: .6;
		}
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
	</style>
	<?php
}
