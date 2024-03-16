<?php
/**
 * Jbros_Film_Review_Settings class.
 */
class Jbros_Film_Review_Settings {
    /**
     * The plugin option group.
     *
     * @var string
     */
    private $option_group = 'jbros_film_review_settings';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // Register settings
        register_setting(
            $this->option_group,
            'jbros_film_review_anthropic_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        register_setting(
            $this->option_group,
            'jbros_film_review_anthropic_api_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        register_setting(
            $this->option_group,
            'jbros_film_review_tmdb_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        register_setting(
            $this->option_group,
            'jbros_film_review_tmdb_api_base_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        // Register settings sections and fields
        add_settings_section(
            'jbros_film_review_api_settings',
            'API Settings',
            array( $this, 'render_api_settings_section' ),
            'jbros-film-review'
        );

        add_settings_field(
            'jbros_film_review_anthropic_api_key',
            'Anthropic API Key',
            array( $this, 'render_anthropic_api_key_field' ),
            'jbros-film-review',
            'jbros_film_review_api_settings'
        );

        add_settings_field(
            'jbros_film_review_anthropic_api_url',
            'Anthropic API URL',
            array( $this, 'render_anthropic_api_url_field' ),
            'jbros-film-review',
            'jbros_film_review_api_settings'
        );

        add_settings_field(
            'jbros_film_review_tmdb_api_key',
            'TMDB API Key',
            array( $this, 'render_tmdb_api_key_field' ),
            'jbros-film-review',
            'jbros_film_review_api_settings'
        );

        add_settings_field(
            'jbros_film_review_tmdb_api_base_url',
            'TMDB API Base Url',
            array( $this, 'render_tmdb_api_base_url_field' ),
            'jbros-film-review',
            'jbros_film_review_api_settings'
        );
    }

    /**
     * Add the settings page to the admin menu.
     */
    public function add_settings_page() {
        add_options_page(
            'JBros Film Review Settings',
            'JBros Film Review',
            'manage_options',
            'jbros-film-review',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render the API settings section.
     */
    public function render_api_settings_section() {
        echo '<p>Configure the API settings for the JBros Film Review plugin.</p>';
    }

    /**
     * Render the Anthropic API key field.
     */
    public function render_anthropic_api_key_field() {
        $value = get_option( 'jbros_film_review_anthropic_api_key' );
        echo '<input type="text" name="jbros_film_review_anthropic_api_key" value="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render the Anthropic API URL field.
     */
    public function render_anthropic_api_url_field() {
        $value = get_option( 'jbros_film_review_anthropic_api_url' );
        echo '<input type="text" name="jbros_film_review_anthropic_api_url" value="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render the TMDB API key field.
     */
    public function render_tmdb_api_key_field() {
        $value = get_option( 'jbros_film_review_tmdb_api_key' );
        echo '<input type="text" name="jbros_film_review_tmdb_api_key" value="' . esc_attr( $value ) . '" />';
    }

        /**
     * Render the TMDB API url field.
     */
    public function render_tmdb_api_base_url_field() {
        $value = get_option( 'jbros_film_review_tmdb_api_base_url' );
        echo '<input type="text" name="jbros_film_review_tmdb_api_base_url" value="' . esc_attr( $value ) . '" />';
    }


    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( 'jbros-film-review' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the settings class
//$jbros_film_review_settings = new Jbros_Film_Review_Settings(); //initialised in main plugin file