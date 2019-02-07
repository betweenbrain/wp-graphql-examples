<?php
/**
 * Plugin Name: WP GraphQL Test
 * Description: WP GraphQL test plugin.
 * Author: Matt Thomas
 * Version: 0.0.1
 */

 /**
  * Add custom field to WP core posts.
  */
add_action(
	'fm_post_post', function() {
		$fm = new Fieldmanager_TextField(
			array(
				'name' => 'test_field',
			)
		);
		$fm->add_meta_box( 'Test Field', 'post' );
	}
);


/**
 * Set the context.
 *
 * Here we filter in to a hook that runs before a resolver is executed and check to see if the field has a specific arg we're looking for.
 * In this case, we check if the field is `posts` and if it has an arg of `where.foo` populated. If so, let's set the `$context->foo` to the value of that arg.
 */
add_action(
	'graphql_before_resolve_field',
	function( $source, $args, $context, $info, $field_resolver, $type_name, $field_key, $field ) {
		// $context->currentConnection            = $type_name;
		// $context->connectionArgs[ $type_name ] = $args;
		if ( 'posts' === $field_key && isset( $args['where']['foo'] ) ) {
			$context->foo = $args['where']['foo'];
		}

	}, 10, 8
);

/**
 * Use the context.
 */
add_action(
	'graphql_register_types', function() {
		register_graphql_field(
			'Post', 'testField', [
				'type'        => 'String',
				'description' => __( 'A test field.', 'guggenheim' ),
				'resolve'     => function( $post, $args, $context, $info ) {
					$field = get_post_meta( $post->ID, 'test_field', true );

					return $field;
				},
			]
		);

		register_graphql_field(
			'RootQueryToPostConnectionWhereArgs', 'foo', [
				'type'        => 'String',
				'description' => __( 'Foo of the bar.', 'guggenheim' ),
			]
		);
	}
);

/**
 * Unset the context (optional).
 */

 /*
add_filter(
	'graphql_before_resolve_field',
	function( $source, $args, $context, $info, $field_resolver, $type_name, $field_key, $field ) {
		if ( 'posts' === $field_key && isset( $context->foo ) ) {
			unset( $context->foo );
		}
	}, 10, 8
);
*/

add_action(
	'graphql_register_types', function() {
		register_graphql_field(
			/**
			 * Adds a new 'app_login' root query.
			 */
			'RootQuery', 'app_login', [
				'args'        => [
					'password' => [
						'type' => WPGraphQL\Types::string(),
					],
				],
				'type'        => \WPGraphQL\Types::string(),
				'description' => __( 'Application administration validation', 'guggenheim' ),
				'resolve'     => function( $root, $args, $context, $info ) {
					return ( get_option( 'app_admin_password' ) === $args['password'] ) ? true : false;
				},
			]
		);
	}
);

/**
 * Expose custom field via wp-json.
 */
add_action(
	'rest_api_init', function () {
		register_rest_route(
			'/test/', '(?P<foo>[a-zA-Z0-9]+)', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => function ( $request ) {
					$field = get_post_meta( $request['foo'], 'test_field', true );

					return $field;
				},
			)
		);
	}
);
