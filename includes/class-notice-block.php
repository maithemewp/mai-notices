<?php

class Mai_Notice_Block {

	function __construct() {
		add_action( 'acf/init', [ $this, 'register_block' ], 10, 3 );
		add_action( 'acf/init', [ $this, 'register_field_group' ], 10, 3 );
		add_filter( 'acf/load_field/key=field_5dd6bca5fa5c6', [ $this, 'load_type_choices' ] );
		// add_filter( 'acf/load_field/key=field_5dd6c75b0ea87', [ $this, 'load_icon_choices' ] );
		add_filter( 'acf/prepare_field/key=field_5dd6c3e627a83', [ $this, 'load_deprecated_content' ] );
	}

	function register_block() {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}

		// Register.
		acf_register_block_type( [
			'name'            => 'mai-notice',
			'title'           => __( 'Mai Notice', 'mai-notices' ),
			'description'     => __( 'A callout notice block.', 'mai-notices' ),
			'icon'            => 'info',
			'category'        => 'widgets',
			'keywords'        => [ 'notice', 'callout', 'content' ],
			'render_callback' => [ $this, 'do_notice' ],
			'enqueue_assets'  => 'mai_notice_enqueue_style',
			'supports'        => [
				'align'  => [ 'wide' ],
				'ancher' => true,
				'mode'   => false,
				'jsx'    => true,
			],
		] );
	}

	function do_notice( $block, $content = '', $is_preview = false ) {
		$types    = mai_notice_get_types();
		$type     = get_field( 'type' );
		$type     = $type ?: $this->get_default_type();
		$style    = 'custom' === $type ? get_field( 'style' ) : ( isset( $types[ $type ]['style'] ) ? $types[ $type ]['style'] : '' );
		$icon     = 'custom' === $type ? get_field( 'icon' ) : ( isset( $types[ $type ]['icon'] ) ? $types[ $type ]['icon'] : '' );
		$color    = 'custom' === $type ? get_field( 'color' ) : ( isset( $types[ $type ]['color'] ) ? $types[ $type ]['color'] : '' );
		$existing = get_field( 'content' );
		$inner    = '';
		$inner   .= $is_preview && $existing ? sprintf( '<p style="padding:8px 16px;background-color:#fd0010;color:white;font-size:15px;border-radius:3px;">%s</p>', __( 'This block contains content in the old field in the sidebar. Please copy it out of there and paste into the new inner blocks editor!' , 'mai-notices' ) ) : '';
		$inner   .= $existing ?: $existing;
		$inner   .= $this->get_inner_blocks();

		$args     = [
			'type'    => $type,
			'style'   => $style,
			'icon'    => $icon,
			'color'   => $color,
			'content' => $inner,
		];

		// Swap for brand.
		if ( 'custom' === $type && 'brands' === $args['style'] ) {
			$args['icon'] = get_field( 'icon_brand' );
		}

		if ( isset( $block['className'] ) && ! empty( $block['className'] ) ) {
			$args['class'] = $block['className'];
		}

		echo mai_get_notice( $args, true );
	}

	function get_default_type() {
		$default = 'info';
		$types   = mai_notice_get_types();

		foreach( $types as $name => $type ) {
			if ( isset( $type['default'] ) && $type['default'] ) {
				$default = $name;
			}
		}

		return $default;
	}

	function get_inner_blocks() {
		$template = [
			[ 'core/paragraph', [], [] ],
		];

		return sprintf( '<InnerBlocks template="%s" />', esc_attr( wp_json_encode( $template ) ) );
	}

	function load_type_choices( $field ) {
		$field['choices'] = [];
		$types            = mai_notice_get_types();

		foreach( $types as $name => $type ) {
			$field['choices'][ $name ] = $type['title'];

			if ( isset( $type['default'] ) && $type['default'] ) {
				$field['default'] = $name;
			}
		}

		return $field;
	}

	function load_icon_choices( $field ) {
		$field['choices'] = [];

		// Bail if editing the field group.
		if ( 'acf-field-group' === get_post_type() ) {
			return $field;
		}

		foreach ( glob( mai_notice_get_icons_dir() . 'svgs/*.svg' ) as $file ) {
			$name = basename( $file, '.svg' );
			$field['choices'][ $name ] = sprintf( '<svg class="mai-notice-icon-svg"><use xlink:href="%ssprites/regular.svg#%s"></use></svg><span class="mai-notice-icon-name">%s</span>', mai_notice_get_icons_url(), $name, $name );
		}

		return $field;
	}

	function load_deprecated_content( $field ) {
		if ( ! $field['value'] ) {
			return [];
		}

		return $field;
	}

	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group( [
			'key'                   => 'group_5dd6bc04f2d4b',
			'title'                 => __( 'Mai Notice', 'mai-notices' ),
			'fields'                => [
				[
					'key'          => 'field_5dd6bca5fa5c6',
					'label'        => __( 'Type', 'mai-notices' ),
					'name'         => 'type',
					'type'         => 'radio',
					'instructions' => '',
					'required'     => 1,
					'choices'      => [], // Loaded via filter.
				],
				[
					'key'               => 'field_621fc57e6e76d',
					'label'             => __( 'Icon', 'mai-notices' ),
					'name'              => 'icon_clone',
					'type'              => 'clone',
					'display'           => 'group', // 'group' or 'seamless'. 'group' allows direct return of actual field names via get_field( 'style' ).
					'clone'             => [ 'mai_icon_style', 'mai_icon_choices', 'mai_icon_brand_choices' ],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_5dd6bca5fa5c6',
								'operator' => '==',
								'value'    => 'custom',
							],
						],
					],
				],
				[
					'key'                 => 'field_5dd6e200452f3',
					'label'               => __( 'Color', 'mai-notices' ),
					'name'                => 'color',
					'type'                => 'color_picker',
					'instructions'        => '',
					'conditional_logic'   => [
						[
							[
								'field'            => 'field_5dd6bca5fa5c6',
								'operator'         => '==',
								'value'            => 'custom',
							],
						],
					],
					'default_value'       => '#06a4e6',
				],
				[
					'key'                 => 'field_5dd6c3e627a83',
					'label'               => __( 'Content', 'mai-notices' ),
					'name'                => 'content',
					'type'                => 'wysiwyg',
					'tabs'                => 'all',
					'toolbar'             => 'basic',
					'media_upload'        => 0,
					'delay'               => 1,
				],
			],
			'location'              => [
				[
					[
						'param'              => 'block',
						'operator'           => '==',
						'value'              => 'acf/mai-notice',
					],
				],
			],
		] );
	}
}
