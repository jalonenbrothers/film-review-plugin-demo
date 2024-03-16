<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package jbros-film-review
 */

class Jbros_Film_Review_Public {
    /**
     * Enqueue block assets.
     */
    public function enqueue_block_assets() {
        $block_path = plugin_dir_path( __FILE__ ) . 'blocks/jbros-film-review-block/build/';

        // Enqueue block scripts and styles.
        wp_enqueue_script(
            'jbros-film-review-block',
            plugins_url( 'blocks/jbros-film-review-block/build/index.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( $block_path . 'index.js' )
        );

        wp_enqueue_style(
            'jbros-film-review-block-editor',
            plugins_url( 'blocks/jbros-film-review-block/build/index.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( $block_path . 'index.css' )
        );

        $movie_info_block_path = plugin_dir_path(__FILE__) . 'blocks/jbros-movie-info-block/build/';
        wp_enqueue_script(
            'jbros-movie-info-block', 
            plugins_url('blocks/jbros-movie-info-block/build/index.js', __FILE__), 
            array('wp-blocks', 'wp-element', 'wp-editor'), 
            filemtime($movie_info_block_path . 'index.js')
        );

        wp_enqueue_style(
            'jbros-movie-info-block-editor', 
            plugins_url('blocks/jbros-movie-info-block/build/index.css', __FILE__), 
            array('wp-edit-blocks'), 
            filemtime($movie_info_block_path . 'index.css')
        );
    }

    /**
     * Register custom API endpoints.
     * 
     * POST http://myfirstlocaltestdev.local/wp-json/jbros-film-review/v1/get-film-review?title=Jaws&year=1975
     * Query params title: Jaws year: 1975
     * 
     */
    public function register_api_endpoints() {
        add_action(
            'rest_api_init',
            function () {

                register_rest_route(
                    'jbros-film-review/v1',
                    '/get-film-review',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'get_film_review' ),
                        'args' => array(
                            'title' => array(
                                'required' => true,
                                'type' => 'string',
                            ),
                            'year' => array(
                                'required' => true,
                                'type' => 'integer',
                            ),
                            'action' => array(
                                'required' => false,
                                'type' => 'string',
                                'default' => 'create_or_update', // Set a default value
                                'enum' => array('create_or_update', 'get_existing'), // Allowed values
                            ),
                        ),
                        //'permission_callback' => fn() => current_user_can( 'edit_others_posts' ),
                        'permission_callback' => '__return_true',
                    )
                );

                register_rest_route(
                    'jbros-film-review/v1',
                    '/get-movie-info',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'get_movie_info' ),
                        'args' => array(
                            'title' => array(
                                'required' => true,
                                'type' => 'string',
                            ),
                            'year' => array(
                                'required' => true,
                                'type' => 'integer',
                            ),
                            'action' => array(
                                'required' => false,
                                'type' => 'string',
                                'default' => 'create_or_update', // Set a default value
                                'enum' => array('create_or_update', 'get_existing'), // Allowed values
                            ),
                        ),
                        //'permission_callback' => fn() => current_user_can( 'edit_others_posts' ),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );
    }

    /**
     * Get a film review from the Anthropic API.
     * 
     * The $request contains film-title $title and release-year $year, 
     * which Anthropic API needs in order to figure out what film we are
     * asking to review.
     * 
     * In the $request we also pass $action, which is used to decide if 
     * we should try to get an existing review already in the database,
     * "get_existing", or create_or_update a new review, which will then
     * call Anthropic API.
     *
     * @param WP_REST_Request $request The REST API request.
     *
     * @return WP_REST_Response The API response.
     */
    public function get_film_review( WP_REST_Request $request ) : WP_REST_Response
    {
        $title = sanitize_text_field( $request->get_param( 'title' ) );
        $year  = absint( $request->get_param( 'year' ) );
        $action = sanitize_text_field($request->get_param('action'));

        if ( empty( $title ) || empty( $year ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Invalid request parameters.',
            ), 400 );
        }

        //TODO: fix the case where there are multiple posts with same title and year
        $existing_post_id = $this->get_film_review_post_id($title, $year);

        if ($action === 'get_existing' && $existing_post_id) {
            $existing_post = get_post($existing_post_id);
            return new WP_REST_Response(array(
                'success' => true,
                'data' => array(
                    'title' => $title,
                    'year' => $year,
                    'film_review' => $existing_post->post_content,
                    'post_id' => $existing_post_id,
                ),
            ), 200);
        }

        /*
         * Let's first get the movie data from TMDB API - this verifies that what
         * we're going to ask Anthropic API is valid
         * 
         */
        $tmdb_api = new TMDB_API();
        $movie_data = $tmdb_api->get_movie_data_by_title_and_year($title, $year);

        if (is_wp_error($movie_data) || empty($movie_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $movie_data->get_error_message(),
            ), 500);
        }

        $anthropic_api = new Anthropic_API();
        $film_review   = $anthropic_api->get_film_review( $title, $year, $movie_data );

        if ( is_wp_error( $film_review ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => $film_review->get_error_message(),
            ), 500 );
        }

        //successful response demo
        /*
        
        {
            "success": true,
            "data": "\"Chariots of Fire\" is a 1981 British historical drama film that has become a beloved classic. Directed by Hugh Hudson, the film tells the true story of two British athletes, Eric Liddell and Harold Abrahams, who competed in the 1924 Paris Olympics.\n\nThe film's strength lies in its powerful storytelling and exceptional performances. Ben Cross as Harold Abrahams and Ian Charleson as Eric Liddell deliver standout performances, capturing the determination, struggles, and personal beliefs of these real-life athletes. Their characters are complex and well-developed, allowing the audience to connect with their journeys on a deeper level.\n\nThe film's visual style is simply stunning. The cinematography by David Watkin is breathtaking, with the iconic beach running scenes set to Vangelis' mesmerizing score becoming one of the most memorable moments in cinema. The use of slow-motion and natural lighting creates a sense of elegance and grace, perfectly capturing the spirit of the sport and the era.\n\nBeyond the athletic achievements, \"Chariots of Fire\" explores themes of prejudice, faith, and the pursuit of excellence. Eric Liddell's unwavering commitment to his religious beliefs and his refusal to compete on Sundays adds a compelling ethical dimension to the story. The film also addresses the anti-Semitism faced by Harold Abrahams, highlighting the societal challenges of the time.\n\nWhile the pacing may feel a bit slow at times, the film's emotional depth and powerful performances more than make up for it. \"Chariots of Fire\" is a triumph of storytelling, capturing the essence of sportsmanship, perseverance, and the human spirit's ability to overcome adversity.\n\nOverall, \"Chariots of Fire\" is a masterpiece that has stood the test of time. Its inspirational message, combined with its visual and musical artistry, make it a must-watch for anyone who appreciates well-crafted and meaningful cinema."
        }
        
        */

        // Create or update a post with the film review
        $post_id = $this->create_or_update_film_review_post( $title, $year, $film_review, $movie_data );

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            ), 500);
        }

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => array(
                'title' => $title,
                'year' => $year,
                'film_review' => $film_review,
                'post_id' => $post_id,
                'movie_data' => $movie_data,
            ),
        ), 200 );
    }

    /**
     * Get the post ID of an existing film review post.
     *
     * @param string $title The movie title.
     * @param int    $year  The movie year.
     *
     * @return int|null The post ID if found, or null if not found.
     */
    private function get_film_review_post_id(string $title, $year, $movie_id = 0)
    {
        $query_args = array(
            'post_type'      => 'film_review', // Use the custom post type name
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => 'film_title',
                    'value' => $title,
                ),
                array(
                    'key'   => 'film_year',
                    'value' => $year,
                ),
                array(
                    'relation' => 'OR', //if either or both missing, we'll need to fetch from tmdbapi
                    array(
                        'key'     => 'jbros_movie_id',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'jbros_poster_url',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ),
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {

            //TODO: handle multiple results
            $post_id = $query->posts[0]->ID;

            // Fetch and store movie data if it doesn't exist
            $movie_id = get_post_meta($post_id, 'jbros_movie_id', true);
            $poster_url = get_post_meta($post_id, 'jbros_poster_url', true);

            //TODO: add other fields

            if (empty($movie_id) ) {
                $this->fetch_and_store_movie_data($post_id, $title, $year);
            } else if (empty($poster_url)) {
                $this->fetch_and_store_movie_data($post_id, $title, $year, $movie_id);
            }


            return $post_id;
        }

        return null;
    }

    /**
     * Get a movie info from current film review post, or from tmdb api
     *
     * @param WP_REST_Request $request The REST API request.
     *
     * @return WP_REST_Response The API response.
     */
    public function get_movie_info($request)
    {
        $title = sanitize_text_field($request->get_param('title'));
        $year = absint($request->get_param('year'));
        $action = sanitize_text_field($request->get_param('action'));

        if (empty($title) || empty($year)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid request parameters.',
            ), 400);
        }

        $post_id = $this->get_film_review_post_id($title, $year);

        if ($action === 'get_existing' && $post_id) {
            $movie_id = get_post_meta($post_id, 'jbros_movie_id', true);
            $poster_url = get_post_meta($post_id, 'jbros_poster_url', true);

            return new WP_REST_Response(array(
                'success' => true,
                'data' => array(
                    'movie_id' => $movie_id,
                    'poster_url' => $poster_url,
                ),
            ), 200);
        }

        $tmdb_api = new TMDB_API();
        $movie_data = $tmdb_api->get_movie_data_by_title_and_year($title, $year);

        if (is_wp_error($movie_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $movie_data->get_error_message(),
            ), 500);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'data' => $movie_data,
        ), 200);
    }

    /**
     * Create or update a film review post.
     *
     * @param string $title        The movie title.
     * @param int    $year         The movie year.
     * @param string $film_review  The film review content.
     * @param array  $movie_data   The movie data from TMDB API.
     *
     * @return int|WP_Error The post ID on success, or WP_Error on failure.
     */
    private function create_or_update_film_review_post($title, $year, $film_review, $movie_data = false)
    {
        // Check if a post already exists for the given title and year
        $existing_post_id = $this->get_film_review_post_id($title, $year);

        $post_data = array(
            'post_title'   => "{$title} ({$year})",
            'post_content' => $film_review,
            'post_type'    => 'film_review', // Use the custom post type name
            'post_status'  => 'publish',
        );

        if ($existing_post_id) {
            // Update the existing post
            $post_data['ID'] = $existing_post_id;
            $post_id = wp_update_post($post_data);
        } else {
            // Create a new post
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save post meta
        update_post_meta($post_id, 'film_title', $title);
        update_post_meta($post_id, 'film_year', $year);

        if(isset($movie_data['id']) && $movie_data['id']) {
            update_post_meta($post_id, 'jbros_movie_id', $movie_data['id']);
            update_post_meta($post_id, 'jbros_poster_url', $movie_data['poster_url']);
            update_post_meta($post_id, 'jbros_backdrop_path', $movie_data['backdrop_path']);
            update_post_meta($post_id, 'jbros_overview', $movie_data['overview']);
        }


        // Fetch and store movie data
        if($movie_data == false) {
            $this->fetch_and_store_movie_data($post_id, $title, $year);
        }

        return $post_id;
    }

    /**
     * Fetch and store movie data for a film review.
     *
     * @param int    $post_id The post ID of the film review.
     * @param string $title   The movie title.
     * @param int    $year    The movie year.
     *
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    private function fetch_and_store_movie_data($post_id, $title, $year, $movie_id = 0)
    {
        $tmdb_api = new TMDB_API();

        if ($movie_id) {
            $movie_data = $tmdb_api->get_movie_data_by_movie_id($movie_id);
        }
        else {
            $movie_data = $tmdb_api->get_movie_data_by_title_and_year($title, $year);
        }

        /*
        $movie_data = array(
            'id'         => 1234,
            'poster_url' => 'https://image.tmdb.org/t/p/w500' . '/test.jpg',
        );
        */
        
        if (is_wp_error($movie_data)) {
            return $movie_data;
        }

        update_post_meta($post_id, 'jbros_movie_id', $movie_data['id']); // 'id' (movie_id)
        update_post_meta($post_id, 'jbros_poster_url', $movie_data['poster_url']); // 'poster_path'
        update_post_meta($post_id, 'jbros_backdrop_path', $movie_data['backdrop_path']);
        update_post_meta($post_id, 'jbros_overview', $movie_data['overview']);

        return true;
    }

    /**
     * Initialize the class.
     */
    public function init() {
        $this->register_api_endpoints();
        // ...
    }
}