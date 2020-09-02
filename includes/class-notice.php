<?php

class Mai_Notice {

	protected $types;
	protected $args;
	protected $block;

	function __construct( $args, $block = false ) {
		$this->types = mai_notice_get_types();
		$this->args  = $args;
		$this->args  = wp_parse_args( $this->args, $this->get_defaults() );
		$this->block = $block;
	}

	function get_defaults() {
		return [
			'type'    => '', // Required.
			'content' => '', // Required.
			'icon'    => '',
			'color'   => '',
		];
	}

	function get() {
		if ( ! ( $this->args['type'] && $this->args['content'] ) ) {
			return '';
		}

		mai_notice_enqueue_style();

		$icon    = $this->get_icon_html();
		$color   = $this->get_color();
		$content = function_exists( 'mai_get_processed_content' ) && ! $this->block ? mai_get_processed_content( $this->args['content'] ) : $this->args['content'];
		vd( $this->block );
		$atts    = [
			'class' => sprintf( 'mai-notice mai-notice-%s', sanitize_html_class( $this->args['type'] ) ),
			'style' => sprintf( '--mai-notice-color:%s;', esc_attr( $color ) ),
		];

		return genesis_markup(
			[
				'open'    => '<div %s>',
				'close'   => '</div>',
				'context' => 'mai-notice',
				'content' => $icon . $content,
				'echo'    => false,
				'atts'    => $atts,
			]
		);
	}

	function get_icon_html() {
		$html = '';
		$icon = $this->get_icon();

		if ( ! $icon ) {
			return $html;
		}

		// Build path.
		$path = mai_notice_get_icons_dir() . 'svgs/' . $icon . '.svg';

		// Bail if no file.
		if ( ! file_exists( $path ) ) {
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
			// Class.
			$item->setAttribute( 'class', 'mai-notice-icon' );
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

	function get_icon() {
		if ( $this->args['icon'] ) {
			return $this->args['icon'];
		}
		return isset( $this->types[ $this->args['type'] ]['icon'] ) ? $this->types[ $this->args['type'] ]['icon'] : '';
	}

	function get_color() {
		if ( $this->args['color'] ) {
			return $this->args['color'];
		}
		return isset( $this->types[ $this->args['type'] ]['color'] ) ? $this->types[ $this->args['type'] ]['color'] : '';
	}
}
