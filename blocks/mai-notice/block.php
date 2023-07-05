<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'acf/init', 'mai_register_notice_block' );
/**
 * Register block.
 *
 * @since 0.1.0
 *
 * @return void
 */
function mai_register_notice_block() {
	register_block_type( __DIR__ . '/block.json' );
}

/**
 * Callback function to render the block.
 *
 * @since 0.1.0
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 *
 * @return void
 */
function mai_do_notice_block( $block, $content = '', $is_preview = false, $post_id = 0 ) {
	$types    = mai_notice_get_types();
	$type     = get_field( 'type' );
	$type     = $type ?: mai_notice_get_default_type();
	$style    = 'custom' === $type ? get_field( 'style' ) : ( isset( $types[ $type ]['style'] ) ? $types[ $type ]['style'] : '' );
	$icon     = 'custom' === $type ? get_field( 'icon' ) : ( isset( $types[ $type ]['icon'] ) ? $types[ $type ]['icon'] : '' );
	$color    = 'custom' === $type ? get_field( 'color' ) : ( isset( $types[ $type ]['color'] ) ? $types[ $type ]['color'] : '' );
	$existing = get_field( 'content' );
	$inner    = '';
	$inner   .= $is_preview && $existing ? sprintf( '<p style="padding:8px 16px;background-color:#fd0010;color:white;font-size:15px;border-radius:3px;">%s</p>', __( 'This block contains content in the old field in the sidebar. Please copy it out of there and paste into the new inner blocks editor!' , 'mai-notices' ) ) : '';
	$inner   .= $existing ?: $existing;
	$inner   .= sprintf( '<InnerBlocks template="%s" />', esc_attr( wp_json_encode([ [ 'core/paragraph', [], [] ] ] ) ) );

	$args     = [
		'preview' => $is_preview,
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

/**
 * Get default type.
 *
 * @access private
 *
 * @since TBD
 *
 * @return string
 */
function mai_notice_get_default_type() {
	$default = 'info';
	$types   = mai_notice_get_types();

	foreach( $types as $name => $type ) {
		if ( isset( $type['default'] ) && $type['default'] ) {
			$default = $name;
			break;
		}
	}

	return $default;
}

add_filter( 'acf/load_field/key=field_5dd6bca5fa5c6', 'mai_notice_load_type_choices' );
/**
 * Load type choices.
 *
 * @since TBD
 *
 * @param array $field The field data.
 *
 * @return array
 */
function mai_notice_load_type_choices( $field ) {
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

add_filter( 'acf/prepare_field/key=field_5dd6c3e627a83', 'mai_notice_load_deprecated_content' );
/**
 * Register deprecated content field.
 *
 * @since TBD
 *
 * @param array $field The field data.
 *
 * @return array
 */
function mai_notice_load_deprecated_content( $field ) {
	if ( ! $field['value'] ) {
		return [];
	}

	return $field;
}

add_action( 'acf/init', 'mai_register_notice_field_group', 10, 3 );
/**
 * Registers field groups.
 *
 * @since 0.1.0
 *
 * @return void
 */
function mai_register_notice_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		[
			'key'    => 'group_5dd6bc04f2d4b',
			'title'  => __( 'Mai Notice', 'mai-notices' ),
			'fields' => [
				[
					'key'          => 'field_5dd6bca5fa5c6',
					'label'        => __( 'Type', 'mai-notices' ),
					'name'         => 'type',
					'type'         => 'radio',
					'instructions' => '',
					'required'     => 1,
					'choices'      => [],                            // Loaded via filter.
				],
				[
					'key'               => 'field_621fc57e6e76d',
					'label'             => __( 'Icon', 'mai-notices' ),
					'name'              => 'icon_clone',
					'type'              => 'clone',
					'display'           => 'group',                                                              // 'group' or 'seamless'. 'group' allows direct return of actual field names via get_field( 'style' ).
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
					'key'               => 'field_5dd6e200452f3',
					'label'             => __( 'Color', 'mai-notices' ),
					'name'              => 'color',
					'type'              => 'color_picker',
					'instructions'      => '',
					'default_value'     => '#06a4e6',
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
					'key'          => 'field_5dd6c3e627a83',
					'label'        => __( 'Content', 'mai-notices' ),
					'name'         => 'content',
					'type'         => 'wysiwyg',
					'tabs'         => 'all',
					'toolbar'      => 'basic',
					'media_upload' => 0,
					'delay'        => 1,
				],
			],
			'location' => [
				[
					[
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/mai-notice',
					],
				],
			],
		]
	);
}
