<?php

// Get it started.
add_action( 'plugins_loaded', function() {
	new Mai_Notice;
});

class Mai_Notice {

	function __construct() {
		add_action( 'wp_enqueue_scripts',                     array( $this, 'enqueue_style' ) );
		add_action( 'acf/input/admin_head',                   array( $this, 'custom_css' ) );
		add_action( 'acf/init',                               array( $this, 'register_block' ), 10, 3 );
		add_filter( 'acf/load_field/key=field_5dd6bca5fa5c6', array( $this, 'load_type_choices' ) );
		add_filter( 'acf/load_field/key=field_5dd6c75b0ea87', array( $this, 'load_icon_choices' ) );
	}

	function enqueue_style() {
		wp_register_style( 'mai-notices', MAI_NOTICES_PLUGIN_URL . "assets/css/mai-notices{$this->get_suffix()}.css", array(), MAI_NOTICES_VERSION );
	}

	function register_block() {
		// Bail if no ACF Pro >= 5.8.
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}
		// Register.
		acf_register_block_type( array(
			'name'            => 'mai-notice',
			'title'           => __( 'Mai Notice', 'mai-notices' ),
			'description'     => __( 'A callout notice block.', 'mai-notices' ),
			'icon'            => 'info',
			'category'        => 'formatting',
			'keywords'        => array( 'notice', 'callout', 'content' ),
			'mode'            => 'auto',
			'enqueue_style'   => MAI_NOTICES_PLUGIN_URL . "assets/css/mai-notices{$this->get_suffix()}.css",
			'render_callback' => array( $this, 'do_notice' ),
			'supports'        => array(
				'align'  => array( 'wide' ),
				'ancher' => true,
			),
		) );
	}

	function do_notice( $block, $content = '', $is_preview = false ) {
		echo $this->get_notice( $block );
	}

	function get_notice( $block ) {
		// Get type.
		$type = get_field( 'type' );
		// Bail if no type.
		if ( ! $type ) {
			return '';
		}
		// Get content.
		$html    = '';
		$name    = $this->get_name( $type );
		$color   = $this->get_color( $type );
		$icon    = $this->get_icon_html( $name );
		$content = wp_kses_post( get_field( 'content' ) );
		// Bail if no content.
		if ( ! $content ) {
			return $html;
		}
		// Build HTML.
		$html .= sprintf( '<div class="mai-notice mai-notice-%s" style="--mai-notice-color:%s;">', sanitize_html_class( $name ), esc_attr( $color ) );
			$html .= $this->get_icon_html( $name );
			$html .= wp_kses_post( get_field( 'content' ) );
		$html .= '</div>';
		return $html;
	}

	function load_type_choices( $field ) {
		$field['choices'] = array();
		$types = $this->get_types();
		foreach( $types as $name => $type ) {
			$field['choices'][ $name ] = $type['title'];
		}
		return $field;
	}

	function load_icon_choices( $field ) {
		$field['choices'] = array();
		// Bail if editing the field group.
		if ( 'acf-field-group' === get_post_type() ) {
			return $field;
		}
		foreach ( glob( $this->get_icons_dir() . 'svgs/*.svg' ) as $file ) {
			$name = basename( $file, '.svg' );
			$field['choices'][ $name ] = sprintf( '<svg class="mai-notice-icon-svg"><use xlink:href="%ssprites/regular.svg#%s"></use></svg><span class="mai-notice-icon-name">%s</span>', $this->get_icons_url(), $name, $name );
		}
		return $field;
	}

	function get_name( $type ) {
		$types = $this->get_types();
		return isset( $types[ $type ]['icon'] ) ? $types[ $type ]['icon'] : '';
	}

	function get_color( $type ) {
		$types = $this->get_types();
		return isset( $types[ $type ]['color'] ) ? $types[ $type ]['color'] : '';
	}

	function get_types() {
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

	function get_icon_html( $name ) {

		$html = '';

		// Bail if no name.
		if ( ! $name ) {
			return $html;
		}

		// Build path.
		$path = $this->get_icons_dir() . 'svgs/' . $name . '.svg';

		// Bail if no file.
		if ( ! ( $name && file_exists( $path ) ) ) {
			return $html;
		}

		// Get the icon.
		$icon = file_get_contents( $path );

		// Create the new document.
		$dom = new DOMDocument;

		// Modify state.
		$libxml_previous_state = libxml_use_internal_errors( true );

		// Load the content in the document HTML.
		$dom->loadHTML( mb_convert_encoding( $icon, 'HTML-ENTITIES', "UTF-8" ) );

		// Handle errors.
		libxml_clear_errors();

		// Restore.
		libxml_use_internal_errors( $libxml_previous_state );

		// Need to loop through, even thoguh there is only one item.
		foreach ( $dom->getElementsByTagName( 'svg' ) as $item ) {
			// Color.
			$item->setAttribute( 'fill', 'currentColor' );
			// Height & Width.
			$item->setAttribute( 'height', '1em' );
			$item->setAttribute( 'width', '1em' );
			// Accessibility.
			$item->setAttribute( 'aria-hidden', 'true' );
			$item->setAttribute( 'focusable', 'false' );
			$item->setAttribute( 'role', 'img' );
			// Replace the HTML.
			$html = $dom->saveHTML();
		}

		// Send it.
		return $html;
	}

	function get_icons_dir() {
		return MAI_NOTICES_PLUGIN_DIR . 'assets/icons/';
	}

	function get_icons_url() {
		return MAI_NOTICES_PLUGIN_URL . 'assets/icons/';
	}

	function get_suffix() {
		$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		return $debug ? '' : '.min';
	}

	function custom_css() {
		?>
		<style>
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

}
