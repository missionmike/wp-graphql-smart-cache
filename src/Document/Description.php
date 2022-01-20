<?php
/**
 * Save the persisted query description text in the post type excpert field.
 *
 * @package Wp_Graphql_Persisted_Queries
 */

namespace WPGraphQL\PersistedQueries\Document;

use WPGraphQL\PersistedQueries\Document;

class Description {

	public function init() {
		add_action(
			'graphql_register_types',
			function () {
				// We use the post type 'excerpt' field as the saved query document 'description'
				$register_type_name = ucfirst( Document::GRAPHQL_NAME );
				$config             = [
					'type'        => 'String',
					'description' => __( 'Description for the saved GraphQL document', 'wp-graphql-labs' ),
				];

				register_graphql_field( 'Create' . $register_type_name . 'Input', 'description', $config );
				register_graphql_field( 'Update' . $register_type_name . 'Input', 'description', $config );

				$config['resolve'] = function ( \WPGraphQL\Model\Post $post, $args, $context, $info ) {
					return get_the_excerpt( $post->ID );
				};
				register_graphql_field( $register_type_name, 'description', $config );
			}
		);

		add_filter(
			'graphql_post_object_insert_post_args',
			[
				$this,
				'mutation_filter_post_args',
			],
			10,
			4
		);
	}

	/**
	 * Run on mutation create/update.
	 */
	public function mutation_filter_post_args( $insert_post_args, $input, $post_type_object, $mutation_name ) {
		if ( in_array(
			$mutation_name,
			[
				'createGraphqlDocument',
				'updateGraphqlDocument',
			],
			true
		) ) {
			// Save the description in excerpt
			if ( isset( $input['description'] ) ) {
				$insert_post_args['post_excerpt'] = $input['description'];
			}
		}

		return $insert_post_args;
	}
}
