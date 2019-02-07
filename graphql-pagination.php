<?php
/**
 * Plugin Name: WP GraphQL Pagination
 * Description: Adds paged and posts_per_page where arguments.
 * Author: Matt Thomas
 * Version: 0.0.1
 */

 /**
  * Register Post where arguments.
  */
add_action(
	'graphql_register_types', function() {
		register_graphql_field(
			'RootQueryToPostConnectionWhereArgs', 'paged', [
				'type'        => 'Int',
				'description' => __( 'Page number.', 'guggenheim' ),
			]
		);

		register_graphql_field(
			'RootQueryToPostConnectionWhereArgs', 'ignore_stickiness', [
				'type'        => 'Boolean',
				'description' => __( 'Ignore post stickiness. If false, sticky posts will be at the start of the set. Defaults to true if not passed with paged.', 'guggenheim' ),
			]
		);
	}
);

/**
 * Act on query arguments.
 */
add_filter(
	'graphql_post_object_connection_query_args', function( $query_args, $source, $args, $context, $info ) {
		$paged  = $args['where']['paged'];
		$sticky = $args['where']['ignore_stickiness'];

		if ( isset( $paged ) ) {
			$query_args['paged'] = $paged;
		}

		if ( isset( $sticky ) ) {
			$query_args['ignore_sticky_posts'] = $sticky;
		}

		if ( isset( $paged ) && ! isset( $sticky ) ) {
			$query_args['ignore_sticky_posts'] = 1;
		}

		return $query_args;
	}, 10, 5
);

/**
 * Register new total count root query.
 */
add_action(
	'graphql_register_types', function() {
		register_graphql_field(
			'RootQuery', 'totalCount', [
				'args'        => [
					'type' => [
						'description' => __( 'Post type to get a total count of.', 'guggenheim' ),
						'type'        => WPGraphQL\Types::string(),
					],
				],
				'type'        => \WPGraphQL\Types::string(),
				'description' => __( 'Returns the total count of a post type.', 'guggenheim' ),
				'resolve'     => function( $root, $args, $context, $info ) {
					$args['type'];

					$args  = array(
						'post_type' => $args['type'],
					);
					$query = new WP_Query( $args );

					return $query->found_posts;
				},
			]
		);
	}
);

