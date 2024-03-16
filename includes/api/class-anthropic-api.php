<?php
/**
 * Anthropic API integration.
 *
 * @package jbros-film-review
 */

/**
 * Anthropic API integration class.
 */
class Anthropic_API {
    /**
     * API endpoint URL.
     *
     * @var string
     */
    private $api_url = 'https://api.anthropic.com/v1/messages'; 

    /**
     * API key.
     *
     * @var string
     */
    private $api_key;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->api_key = get_option( 'jbros_film_review_anthropic_api_key' );
        $this->api_url = get_option( 'jbros_film_review_anthropic_api_url' );
    }

    /**
     * Get a film review from the Anthropic API.
     *
     * @param string $title The film title.
     * @param int    $year  The film release year.
     *
     * @return string|WP_Error The film review or a WP_Error object if the request fails.
     */
    public function get_film_review( $title, $year, $movie_data = false ) {
        $prompt = sprintf( 'Give a film review for the movie "%s" released in %d.', $title, $year );

        $message = new stdClass();
        $message->role = "user";
        $message->content = $prompt;

        if($movie_data != false) {
            // TODO: alternative prompt based on movie_data
        }

        $response = $this->make_api_request( $message );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $response['content'][0]['text'];
    }

    /**
     * Make a request to the Anthropic API.
     *
     * @param string $prompt The prompt for the request.
     *
     * @return array|WP_Error The API response or a WP_Error object if the request fails.
     */
    private function make_api_request( $message ) {
        $headers = array(
            'Content-Type' => 'application/json',
            'x-api-key' => $this->api_key,
            'anthropic-version' => '2023-06-01'
        );

        $body = array(
            'messages'      => [ $message ],
            'model'       => "claude-3-sonnet-20240229", 
            'max_tokens'  => 2048,
        );

        $response = wp_remote_post(
            $this->api_url,
            array(
                'headers' => $headers,
                'body'    => wp_json_encode( $body ),
                'timeout' => 300
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! $response_body || ! isset( $response_body['content'] ) ) {
            return new WP_Error( 'anthropic_api_error', 'Error retrieving response from the Anthropic API.' );
        }

        return $response_body;
    }
}