<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Notice {
	protected $types;
	protected $args;
	protected $block;

	/**
	 * Construct the class.
	 *
	 * @param array $args
	 * @param bool  $block
	 *
	 * @return void
	 */
	function __construct( $args, $block = false ) {
		if ( ! ( function_exists( 'genesis_markup' ) && function_exists( 'mai_get_svg_icon' ) ) ) {
			return;
		}

		$this->types = mai_notice_get_types();
		$this->args  = wp_parse_args( $args, $this->get_defaults() );
		$this->block = $block;
	}

	/**
	 * Get default args.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function get_defaults() {
		return [
			'type'    => '', // Required.
			'content' => '', // Required.
			'style'   => 'light',
			'icon'    => '',
			'color'   => '',
			'class'   => '',
		];
	}

	/**
	 * Get notice.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function get() {
		if ( ! ( $this->args['type'] && $this->args['content'] ) ) {
			return '';
		}

		mai_notice_enqueue_style();

		$icon    = $this->get_icon_html();
		$color   = $this->get_color();
		$content = $this->args['content'];

		if ( ! $this->block ) {
			$content = function_exists( 'mai_get_processed_content' ) ? mai_get_processed_content( $this->args['content'] ) : mai_notice_get_processed_content( $this->args['content'] );
		}

		$atts = [
			'class' => sprintf( 'mai-notice mai-notice-%s', sanitize_html_class( $this->args['type'] ) ),
			'style' => sprintf( '--mai-notice-color:%s;', esc_attr( $color ) ),
		];

		if ( $icon ) {
			$atts['class'] .= ' mai-notice-has-icon';
		}

		if ( $this->args['class'] ) {
			$atts['class'] .= ' ' . sanitize_html_class( $this->args['class'] );
		}

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

	/**
	 * Get icon markup.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function get_icon_html() {
		$html = '';
		$icon = $this->get_icon();

		if ( ! $icon ) {
			return $html;
		}

		$html = mai_get_svg_icon( $icon, $this->args['style'],
			[
				'class'       => 'mai-notice-icon',
				'fill'        => 'currentColor',
				'height'      => '1em',
				'width'       => '1em',
				'aria-hidden' => 'true',
				'focusable'   => 'false',
				'role'        => 'img',
			]
		);

		return $html;
	}

	/**
	 * Gets the selected icon.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Converted to clone fields which returns an array of data.
	 *
	 * @return string
	 */
	function get_icon() {
		if ( $this->args['icon'] ) {
			$icon = $this->args['icon'];
		} else {
			$icon = isset( $this->types[ $this->args['type'] ]['icon'] ) ? $this->types[ $this->args['type'] ]['icon'] : '';
		}

		return $icon;
	}

	/**
	 * Gets the notice color.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function get_color() {
		if ( $this->args['color'] ) {
			return $this->args['color'];
		}

		return isset( $this->types[ $this->args['type'] ]['color'] ) ? $this->types[ $this->args['type'] ]['color'] : '';
	}
}
