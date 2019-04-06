<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
/**
 * MobiConnector Post Pre Post Custom
 * 
 * Pre line data with Rest API and add new line data
 * 
 * @class MobiConnector_Pre_Post_Custom
 */
class BAMobile_Pre_Post_Custom extends WP_REST_Controller{
	
	/**
	 * post type 
	 */
	private $post_type;

	/**
	 * Meta of post
	 */
	private $meta;
	
	/**
	 * MobiConnector Post Pre Post Custom construct
	 * 
	 * @param string $type      post_type of post
	 */
    public function __construct($type){
        $this->post_type = $type;
        $this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}
	
	/**
	 * Pre line post and add new line
	 * 
	 * @param object $post      post with pre
	 * @param WP_REST_Request $request Full details about the request.
	 * 
	 * @return WP_REST_Response Response object.
	 */
    public function bamobile_mobiconnector_pre_post( $post, $request ) {
		if(!is_object($post) || empty($post)){
			return array();
		}
        setup_postdata( $post );
        $schema = $this->bamobile_get_item_schema();
        // Base fields for every post.
        $data = array();
        if ( ! empty( $schema['properties']['id'] ) ) {
            $data['id'] = $post->ID;
        }
        if ( ! empty( $schema['properties']['date'] ) ) {
            $data['date'] = $this->bamobile_prepare_date_response( $post->post_date_gmt, $post->post_date );
        }
        if ( ! empty( $schema['properties']['date_gmt'] ) ) {
            // For drafts, `post_date_gmt` may not be set, indicating that the
            // date of the draft should be updated each time it is saved (see
            // #38883).  In this case, shim the value based on the `post_date`
            // field with the site's timezone offset applied.
            if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
                $post_date_gmt = get_gmt_from_date( $post->post_date );
            } else {
                $post_date_gmt = $post->post_date_gmt;
            }
            $data['date_gmt'] = $this->bamobile_prepare_date_response( $post_date_gmt );
        }
        if ( ! empty( $schema['properties']['guid'] ) ) {
            $data['guid'] = array(
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            );
        }
        if ( ! empty( $schema['properties']['modified'] ) ) {
            $data['modified'] = $this->bamobile_prepare_date_response( $post->post_modified_gmt, $post->post_modified );
        }

        if ( ! empty( $schema['properties']['modified_gmt'] ) ) {
            // For drafts, `post_modified_gmt` may not be set (see
            // `post_date_gmt` comments above).  In this case, shim the value
            // based on the `post_modified` field with the site's timezone
            // offset applied.
            if ( '0000-00-00 00:00:00' === $post->post_modified_gmt ) {
                $post_modified_gmt = date( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
            } else {
                $post_modified_gmt = $post->post_modified_gmt;
            }
            $data['modified_gmt'] = $this->bamobile_prepare_date_response( $post_modified_gmt );
        }

        if ( ! empty( $schema['properties']['password'] ) ) {
            $data['password'] = $post->post_password;
        }

        if ( ! empty( $schema['properties']['slug'] ) ) {
            $data['slug'] = $post->post_name;
        }

        if ( ! empty( $schema['properties']['status'] ) ) {
            $data['status'] = $post->post_status;
        }

        if ( ! empty( $schema['properties']['type'] ) ) {
            $data['type'] = $post->post_type;
        }

        if ( ! empty( $schema['properties']['link'] ) ) {
            $data['link'] = get_permalink( $post->ID );
        }

        if ( ! empty( $schema['properties']['title'] ) ) {
            add_filter( 'protected_title_format', array( $this, 'bamobile_protected_title_format' ) );

            $data['title'] = array(
                'raw'      => $post->post_title,
                'rendered' => get_the_title( $post->ID ),
            );

            remove_filter( 'protected_title_format', array( $this, 'bamobile_protected_title_format' ) );
        }

        $has_password_filter = false;

        if ( $this->bamobile_can_access_password_content( $post, $request ) ) {
            // Allow access to the post, permissions already checked before.
            add_filter( 'post_password_required', '__return_false' );

            $has_password_filter = true;
        }

        if ( ! empty( $schema['properties']['content'] ) ) {
			$content = $post->post_content;
            $data['content'] = array(
                'raw'       => $content,
                'rendered'  => post_password_required( $post ) ? '' : apply_filters( 'the_content', $content  ),
                'protected' => (bool) $post->post_password,
            );
        }

        if ( ! empty( $schema['properties']['excerpt'] ) ) {
			$excerpto = $post->post_excerpt;
			$excerpt = apply_filters( 'the_excerpt', $excerpto, $post );
			if ( empty( $excerpt ) ) {
				$data['excerpt'] = array(
					'raw' => $excerpto,
					'rendered' => '',
					'protected' => (bool) $post->post_password,
				);
			}
            $data['excerpt'] = array(
                'raw'       => $excerpto,
                'rendered'  => post_password_required( $post ) ? '' : $excerpt,
                'protected' => (bool) $post->post_password,
            );
        }

        if ( $has_password_filter ) {
            // Reset filter.
            remove_filter( 'post_password_required', '__return_false' );
        }

        //if ( ! empty( $schema['properties']['author'] ) ) {
            $data['author'] = (int) $post->post_author;
        //}

        if ( ! empty( $schema['properties']['featured_media'] ) ) {
            $data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );
        }

        if ( ! empty( $schema['properties']['parent'] ) ) {
            $data['parent'] = (int) $post->post_parent;
        }

        if ( ! empty( $schema['properties']['menu_order'] ) ) {
            $data['menu_order'] = (int) $post->menu_order;
        }

        if ( ! empty( $schema['properties']['comment_status'] ) ) {
            $data['comment_status'] = $post->comment_status;
        }

        if ( ! empty( $schema['properties']['ping_status'] ) ) {
            $data['ping_status'] = $post->ping_status;
        }

        if ( ! empty( $schema['properties']['sticky'] ) ) {
            $data['sticky'] = is_sticky( $post->ID );
        }

        if ( ! empty( $schema['properties']['template'] ) ) {
            if ( $template = get_page_template_slug( $post->ID ) ) {
                $data['template'] = $template;
            } else {
                $data['template'] = '';
            }
        }

        //if ( ! empty( $schema['properties']['format'] ) ) {
            $data['format'] = get_post_format( $post->ID );

            // Fill in blank post format.
            if ( empty( $data['format'] ) ) {
                $data['format'] = 'standard';
            }
        //}

        if ( ! empty( $schema['properties']['meta'] ) ) {
            $data['meta'] = $this->meta->get_value( $post->ID, $request );
        }

        $taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

        foreach ( $taxonomies as $taxonomy ) {
            $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

            if ( ! empty( $schema['properties'][ $base ] ) ) {
                $terms = get_the_terms( $post, $taxonomy->name );
                $data[ $base ] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
            }
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );
        /**
            * Filters the post data for a response.
            *
            * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
            *
            * @since 4.7.0
            *
            * @param WP_REST_Response $response The response object.
            * @param WP_Post          $post     Post object.
            * @param WP_REST_Request  $request  Request object.
            */
		if($this->post_type != 'post'){
			return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
		}else{
			return $response;
		}
    }
	
	/**
	 * Checks if the user can access password-protected content.
	 * 
	 * @param object $post  Post to check against.
	 * @param WP_REST_Request $request Request data to check.
	 * 
	 * @return bool True if the user can access password-protected content, otherwise false.
	 */
    private function bamobile_can_access_password_content( $post, $request ) {
		if ( empty( $post->post_password ) ) {
			// No filter required.
			return false;
		}

		// Edit context always gets access to password-protected posts.
		if ( 'edit' === $request['context'] ) {
			return true;
		}

		// No password, no auth.
		if ( empty( $request['password'] ) ) {
			return false;
		}

		// Double-check the request password.
		return hash_equals( $post->post_password, $request['password'] );
	}

	/**
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 * 
	 * @param string      $date_gmt GMT publication time.
	 * @param string|null $date     Optional. Local publication time. Default null.
	 * 
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
    private function bamobile_prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Overwrites the default protected title format.
	 */
    private function bamobile_protected_title_format() {
		return '%s';
	}

	/**
	 * Retrieves the post's schema, conforming to JSON Schema.
	 * 
	 * @return array Item schema data.
	 */
    public function bamobile_get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			// Base properties for every Post.
			'properties' => array(
				'date'            => array(
					'description' => __( "The date the object was published, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'date_gmt'        => array(
					'description' => __( 'The date the object was published, as GMT.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'guid'            => array(
					'description' => __( 'The globally unique identifier for the object.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'GUID for the object, as it exists in the database.' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
							'readonly'    => true,
						),
						'rendered' => array(
							'description' => __( 'GUID for the object, transformed for display.' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'id'              => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'            => array(
					'description' => __( 'URL to the object.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'modified'        => array(
					'description' => __( "The date the object was last modified, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt'    => array(
					'description' => __( 'The date the object was last modified, as GMT.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'slug'            => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				),
				'status'          => array(
					'description' => __( 'A named status for the object.' ),
					'type'        => 'string',
					'enum'        => array_keys( get_post_stati( array( 'internal' => false ) ) ),
					'context'     => array( 'view', 'edit' ),
				),
				'type'            => array(
					'description' => __( 'Type of Post for the object.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'password'        => array(
					'description' => __( 'A password to protect access to the content and excerpt.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
			),
		);

		$post_type_obj = get_post_type_object( $this->post_type );

		if ( $post_type_obj->hierarchical ) {
			$schema['properties']['parent'] = array(
				'description' => __( 'The ID for the parent of the object.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}

		$post_type_attributes = array(
			'title',
			'editor',
			'author',
			'excerpt',
			'thumbnail',
			'comments',
			'revisions',
			'page-attributes',
			'post-formats',
			'custom-fields',
		);
		$fixed_schemas = array(
			'post' => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'comments',
				'revisions',
				'post-formats',
				'custom-fields',
			),
			'page' => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'comments',
				'revisions',
				'page-attributes',
				'custom-fields',
			),
			'attachment' => array(
				'title',
				'author',
				'comments',
				'revisions',
				'custom-fields',
			),
		);
		foreach ( $post_type_attributes as $attribute ) {
			if ( isset( $fixed_schemas[ $this->post_type ] ) && ! in_array( $attribute, $fixed_schemas[ $this->post_type ], true ) ) {
				continue;
			} elseif ( ! isset( $fixed_schemas[ $this->post_type ] ) && ! post_type_supports( $this->post_type, $attribute ) ) {
				continue;
			}

			switch ( $attribute ) {

				case 'title':
					$schema['properties']['title'] = array(
						'description' => __( 'The title for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'arg_options' => array(
							'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
						),
						'properties'  => array(
							'raw' => array(
								'description' => __( 'Title for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML title for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					);
					break;

				case 'editor':
					$schema['properties']['content'] = array(
						'description' => __( 'The content for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'arg_options' => array(
							'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
						),
						'properties'  => array(
							'raw' => array(
								'description' => __( 'Content for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML content for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'protected'       => array(
								'description' => __( 'Whether the content is protected with a password.' ),
								'type'        => 'boolean',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					);
					break;

				case 'author':
					$schema['properties']['author'] = array(
						'description' => __( 'The ID for the author of the object.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
					);
					break;

				case 'excerpt':
					$schema['properties']['excerpt'] = array(
						'description' => __( 'The excerpt for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'arg_options' => array(
							'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
						),
						'properties'  => array(
							'raw' => array(
								'description' => __( 'Excerpt for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML excerpt for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'protected'       => array(
								'description' => __( 'Whether the excerpt is protected with a password.' ),
								'type'        => 'boolean',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					);
					break;

				case 'thumbnail':
					$schema['properties']['featured_media'] = array(
						'description' => __( 'The ID of the featured media for the object.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
					);
					break;

				case 'comments':
					$schema['properties']['comment_status'] = array(
						'description' => __( 'Whether or not comments are open on the object.' ),
						'type'        => 'string',
						'enum'        => array( 'open', 'closed' ),
						'context'     => array( 'view', 'edit' ),
					);
					$schema['properties']['ping_status'] = array(
						'description' => __( 'Whether or not the object can be pinged.' ),
						'type'        => 'string',
						'enum'        => array( 'open', 'closed' ),
						'context'     => array( 'view', 'edit' ),
					);
					break;

				case 'page-attributes':
					$schema['properties']['menu_order'] = array(
						'description' => __( 'The order of the object in relation to other object of its type.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					);
					break;

				case 'post-formats':
					// Get the native post formats and remove the array keys.
					$formats = array_values( get_post_format_slugs() );

					$schema['properties']['format'] = array(
						'description' => __( 'The format for the object.' ),
						'type'        => 'string',
						'enum'        => $formats,
						'context'     => array( 'view', 'edit' ),
					);
					break;
                case 'custom-fields':
					$schema['properties']['meta'] = $this->meta->get_field_schema();
					break;
			}
		}

		if ( 'post' === $this->post_type ) {
			$schema['properties']['sticky'] = array(
				'description' => __( 'Whether or not the object should be treated as sticky.' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
			);
		}

		$schema['properties']['template'] = array(
			'description' => __( 'The theme file to use to display the object.' ),
			'type'        => 'string',
			'enum'        => array_merge( array_keys( wp_get_theme()->get_page_templates( null, $this->post_type ) ), array( '' ) ),
			'context'     => array( 'view', 'edit' ),
		);

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
			$schema['properties'][ $base ] = array(
				/* translators: %s: taxonomy name */
				'description' => sprintf( __( 'The terms assigned to the object in the %s taxonomy.' ), $taxonomy->name ),
				'type'        => 'array',
				'items'       => array(
					'type'    => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			);
		}

		return $this->add_additional_fields_schema( $schema );
	}
}  
?>