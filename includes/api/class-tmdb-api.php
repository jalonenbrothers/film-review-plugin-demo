<?php
/**
 * TMDB API Integration
 *
 * @package jbros-film-review
 */

/**
 * TMDB_API class
 */
class TMDB_API {
    /**
     * TMDB API key
     *
     * @var string
     */
    private $api_key;

    /**
     * TMDB API Read Access Token
     *
     * @var string
     */
    //private $api_read_access_token;

    /**
     * TMDB API base URL
     *
     * @var string
     */
    private $api_base_url;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option( 'jbros_film_review_tmdb_api_key' );
        $this->api_base_url = get_option( 'jbros_film_review_tmdb_api_base_url' );//'https://api.themoviedb.org/3';
    }

    /**
     * Get movie data by TMDB API movie_id
     *
     * @param int    $movie_id  The id of the movie on TMDB
     * 
     * e.g. https://api.themoviedb.org/3/search/movie?api_key=ef9d43610877ae1ca7dda61363c8ca65&movie_id=123456789&language=en-US
     *
     * @return array|WP_Error Movie data on success, WP_Error on failure.
     */
    public function get_movie_data_by_movie_id( $movie_id ) {
        $query_params = array(
            'api_key'   => $this->api_key,
            //'movie'      => $movie_id,
            'language'  => 'en-US',
            'append_to_response' => 'videos,images,reviews,similar,recommendations,watch-providers'
        );
        $query_url = add_query_arg( $query_params, $this->api_base_url . '/movie/' . $movie_id );
        $response = wp_remote_get( $query_url, ['timeout' => 300] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_body = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );

        if ( isset( $response_data['id'] ) && $response_data['id']  == $movie_id ) {

            $response_data['poster_url'] = 'https://image.tmdb.org/t/p/w500' . $response_data['poster_path'];
            $response_data['backdrop_path'] = 'https://image.tmdb.org/t/p/w500' . $response_data['backdrop_path'];

            return $response_data;
        }

        return new WP_Error( 'tmdb_api_error', 'No movie data found.' );        
    }

    /**
     * Get movie data by title and year
     *
     * @param string $title The movie title.
     * @param int    $year  The movie year.
     * 
     * e.g. https://api.themoviedb.org/3/search/movie?api_key=ef9d43610877ae1ca7dda61363c8ca65&query=Jaws&year=1975&language=en-US
     *
     * @return array|WP_Error Movie data on success, WP_Error on failure.
     */
    public function get_movie_data_by_title_and_year( $title, $year, $full_data = true ) {
        $query_params = array(
            'api_key'   => $this->api_key,
            'query'     => $title,
            'year'      => $year,
            'language'  => 'en-US', // Set the desired language for movie data
        );

        $query_url = add_query_arg( $query_params, $this->api_base_url . '/search/movie' );
        $response = wp_remote_get( $query_url, ['timeout' => 300] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_body = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );

        if ( isset( $response_data['results'] ) && ! empty( $response_data['results'] ) ) {
            $movie_data = $response_data['results'][0];

            if($full_data) {
                return $this->get_movie_data_by_movie_id($movie_data['id']);
            }

            $movie_info = array(
                'id'         => $movie_data['id'],
                'poster_url' => 'https://image.tmdb.org/t/p/w500' . $movie_data['poster_path'],
            );

            return $movie_info;
        }

        return new WP_Error( 'tmdb_api_error', 'No movie data found.' );
    }
}