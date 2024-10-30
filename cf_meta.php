<?php

/*
Plugin Name: Amplified Meta Tags
Description: Amplified Meta Tags is used to be able to add custom tags for bandsintown/tonefuse publishers. This allows for easy integration with worpress publishers.
Version: 2.1.0
Author: Bandsintown
License: GPL3

Amplified Meta Tags is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Amplified Meta Tags is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Amplified Meta Tags.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Add meta boxes for each tag and each post type.*/
function cf_create_meta_boxes() {
	/* retreive all post types */
	$screens = get_post_types();

	/* post types to avoid adding a metabox to */
	$exclude_screens = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset'
	);

	foreach($screens as $screen) {
		if(!in_array($screen, $exclude_screens)) {
			add_meta_box(
				'cf_meta_tags',
				'CF Tags',
				'cf_meta_boxes_function',
				$screen,
				'side',
				'high'
			);
		}
	}
}

/* Generate HTML for actual meta boxes */
function cf_meta_boxes_function($post) {

	$genres = array(
		"",
		"Acid Jazz",
		"Acoustic",
		"Adult Alternative",
		"Alternative Country",
		"Alternative Dance",
		"Alternative Metal",
		"Alternative Rock",
		"Bluegrass",
		"Blues",
		"Calypso",
		"Children",
		"Christian Hip Hop",
		"Christian Rap",
		"Christian Rock",
		"Classic Rock",
		"Classical",
		"Comedy",
		"Country",
		"Dance",
		"Dancehall",
		"Disco",
		"Downtempo",
		"Drum & Bass",
		"Dubstep",
		"EDM",
		"Electro/Electro pop",
		"Electronic",
		"Electronica",
		"Emo/Hardcore",
		"Europe",
		"Folk",
		"Folk Rock",
		"Funk",
		"Goth",
		"Grindcore",
		"Hard Rock",
		"Heavy Metal",
		"Holiday",
		"House",
		"Indie Pop",
		"Indie Rock",
		"Industrial Metal",
		"Jazz",
		"Korean",
		"Latin",
		"Mambo",
		"Movies/Musicals",
		"Musical",
		"Neo Soul",
		"Neo-Psychedelia",
		"New Age",
		"New Wave",
		"Pop",
		"Pop Punk",
		"Pop Rock",
		"Progressive House",
		"Progressive Metal",
		"Progressive Rock",
		"Psychedelic Rock",
		"Punk Rock",
		"R&B/Soul",
		"Rap Rock",
		"Rap/Hip Hop",
		"Reggae",
		"Reggaeton",
		"Religious/Gospel/CCM",
		"Rock",
		"Rock and Roll",
		"Showtunes",
		"Ska",
		"Ska Punk",
		"Soft Rock",
		"Standards",
		"Techno",
		"Teen Pop",
		"Traditional Country",
		"Trance",
		"TV Shows",
		"Videogames",
		"Vocal",
		"World"
	);

	// retrieve any existing meta data 
	$cf_meta_artist	= get_post_meta($post->ID, '_cf_meta_artist', true);
	$cf_meta_song	= get_post_meta($post->ID, '_cf_meta_song', true);
	$cf_meta_genre	= get_post_meta($post->ID, '_cf_meta_genre', true);
	$cf_meta_tv_term = get_post_meta($post->ID, '_cf_meta_tv_term', true);
	$cf_meta_album = get_post_meta($post->ID, '_cf_meta_album', true);
	$cf_meta_album_is = get_post_meta($post->ID, '_cf_meta_album_is', true);

	// nonce will be checked on save
	wp_nonce_field('cf_inner_custom_box', 'cf_inner_custom_box_nonce');

	$genres_str	= '';

	// create the genre options for the Select Box below
	foreach($genres as $genre) {
		$genres_str = $genres_str . '<option value="' . $genre . '" ' . ($cf_meta_genre == $genre ? 'selected' : '') . '>' . $genre . '</option>';
	}

	// how to display meta boxes
	echo '<div style="margin: 10px 10px; text-align: center">
			<table>
				<tr>
					<td><strong>Artist:</strong></td>
					<td>
						<input style="padding: 6px 4px; width: 80%;" type="text" name="cf_meta_artist" value="' . esc_attr($cf_meta_artist) . '" />
					</td>
				</tr>
				<tr>
					<td><strong>Song:</strong></td>
					<td>
						<input style="padding: 6px 4px; width: 80%;" type="text" name="cf_meta_song" value="' . esc_attr($cf_meta_song) . '" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<p>- OR -</p>
					</td>
				</tr>			
				<tr>
					<td><strong>Genre:</strong></td>
					<td>
						<select name="cf_meta_genre" style="width: 70%">' . $genres_str . '</select>
					</td>
				</tr>
				<tr>
					<td><strong>TV Term:</strong></td>
					<td>
						<input style="padding: 6px 4px; width: 80%;" type="text" name="cf_meta_tv_term" value="' . esc_attr($cf_meta_tv_term) . '" />
					</td>
				</tr>
				<tr>
					<td><strong>Album:</strong></td>
					<td>
						<input style="padding: 6px 4px; width: 80%;" type="text" name="cf_meta_album" value="' . esc_attr($cf_meta_album) . '" />
					</td>
				</tr>
				<tr>
					<td><strong>Album is a soundtrack:</strong></td>
					<td>
						<input type="checkbox" style="padding: 6px 4px; width: 80%;" type="text" name="cf_meta_album_is" ' . (empty($cf_meta_album_is) ? "" : "checked") . ' />
					</td>
				</tr>
			</table>
		</div>';
}

/* Save Meta Tags */
function cf_meta_boxes_save_data($post_id) {
	/*
     * We need to verify this came from the our screen and with proper authorization,
     * because save_post can be triggered at other times.
     */

	$edit_type = $_POST['post_type'] == 'page' ? 'edit_page' : 'edit_post';

	if (
		// Check if our nonce is set.
		!isset( $_POST['cf_inner_custom_box_nonce']) ||
		// Verify that the nonce is valid.
		!wp_verify_nonce($_POST['cf_inner_custom_box_nonce'], 'cf_inner_custom_box') ||
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		// Check the user's permissions.
		!current_user_can($edit_type, $post_id)
	) {
		return $post_id;
	}

	/* OK, it's safe for us to save the data now. */

	// If old entries exist, retrieve them
	$old_artist	= get_post_meta($post_id, '_cf_meta_artist', true);
	$old_song	= get_post_meta($post_id, '_cf_meta_song', true);
	$old_genre	= get_post_meta($post_id, '_cf_meta_genre', true);
	$old_tv_term	= get_post_meta($post_id, '_cf_meta_tv_term', true);
	$old_album	= get_post_meta($post_id, '_cf_meta_album', true);
	$old_album_is	= get_post_meta($post_id, '_cf_meta_album_is', true);

	// Sanitize user input.
	$artist	= sanitize_text_field($_POST['cf_meta_artist']);
	$song	= sanitize_text_field($_POST['cf_meta_song']);
	$genre	= $artist || $song ? "" : sanitize_text_field($_POST['cf_meta_genre']);
	$tv_term = sanitize_text_field($_POST['cf_meta_tv_term']);
	$album = sanitize_text_field($_POST['cf_meta_album']);
	$album_is = isset($_POST['cf_meta_album_is']) ? true : false;

	// Update the meta field in the database.
	update_post_meta($post_id, '_cf_meta_artist', $artist, $old_artist);
	update_post_meta($post_id, '_cf_meta_song', $song, $old_song);
	update_post_meta($post_id, '_cf_meta_genre', $genre, $old_genre);
	update_post_meta($post_id, '_cf_meta_tv_term', $tv_term, $old_tv_term);
	update_post_meta($post_id, '_cf_meta_album', $album, $old_album);
	update_post_meta($post_id, '_cf_meta_album_is', $album_is, $old_album_is);

}

/* Display meta tags in the head */
function cf_display() {

	$post_id = get_the_ID();

	// retrieve the metadata values if they exist
	$cf_meta_artist	= get_post_meta($post_id, '_cf_meta_artist', true);
	$cf_meta_song	= get_post_meta($post_id, '_cf_meta_song', true);
	$cf_meta_genre	= get_post_meta($post_id, '_cf_meta_genre', true);
	$cf_meta_tv_term	= get_post_meta($post_id, '_cf_meta_tv_term', true);
	$cf_meta_album	= get_post_meta($post_id, '_cf_meta_album', true);
	$cf_meta_album_is	= get_post_meta($post_id, '_cf_meta_album_is', true);

	echo '<!-- Clickfuse Meta Tags -->
	<meta property="cf:artist" content="' . $cf_meta_artist . '" />
	<meta property="cf:song" content="' . $cf_meta_song . '" />
	<meta property="cf:genre" content="' . $cf_meta_genre . '" />
	<meta property="cf:tv_term" content="' . $cf_meta_tv_term . '" />
	<meta property="cf:album" content="' . $cf_meta_album . '" />
	<meta property="cf:album_is" content="' . ($cf_meta_album_is == "1" ? "true" : "false") . '" />
	<!-- /Clickfuse Meta Tags -->
	';
}

/***************** Meta Boxes *********************/
/* This is how the functions in this file are mapped to wp hooks */

// Add meta boxes on 'add_meta_boxes' hook
add_action('add_meta_boxes', 'cf_create_meta_boxes');
// save metabox data on save
add_action('save_post', 'cf_meta_boxes_save_data');
// display the meta info
add_action('wp_head', 'cf_display');