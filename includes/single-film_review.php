<?php
/**
 * The template for displaying single Film Review posts
 *
 * @package jbros-film-review
 */

get_header();

$post = get_post();

//demo as these are user's values
$film_title = get_post_meta($post->ID, 'film_title', true);
$film_year = get_post_meta($post->ID, 'film_year', true);

//get real values from tmdb api
$backdrop_path = get_post_meta($post->ID, 'jbros_backdrop_path', true);
$poster_url = get_post_meta($post->ID, 'jbros_poster_url', true); // poster_path in tmdb api
$overview = get_post_meta($post->ID, 'jbros_overview', true);

?>

<div class="entry-content">
    <?php echo '<img src="' . $backdrop_path . '" alt="' . esc_html($film_title) . '" />'; ?>
    <?php the_content(); ?>
</div>

<?php

echo '<div class="film-details">';
echo '<img src="' . $poster_url . '" alt="' . esc_html($film_title) . '" />';
echo '<h2>Film Details</h2>';
echo '<p><strong>Title:</strong> ' . esc_html($film_title) . '</p>';
echo '<p><strong>Year:</strong> ' . esc_html($film_year) . '</p>';
echo '<p> ' . esc_html($overview) . '</p>';
echo '</div>';
?>

<?php
get_footer();