<?php
/*
Plugin Name: RF WR CPT Relationship
Description: WordPress Custom Post Type Relationship Example.
Version: 1.0.0
Contributors: rakifsul
Author: RAKIFSUL
Author URI: https://rakifsul.taplink.ws
License: GPLv2 or later
Text Domain: rf-wr-plugin
*/

function rel111_create_custom_post_types() {
    // Register Custom Post Type: Books
    register_post_type('books', array(
        'labels'      => array(
            'name'          => __('Books'),
            'singular_name' => __('Book'),
        ),
        'public'      => true,
        'has_archive' => true,
        'rewrite'     => array('slug' => 'books'),
        'supports'    => array('title', 'editor', 'thumbnail'),
    ));

    // Register Custom Post Type: Authors
    register_post_type('authors', array(
        'labels'      => array(
            'name'          => __('Authors'),
            'singular_name' => __('Author'),
        ),
        'public'      => true,
        'has_archive' => true,
        'rewrite'     => array('slug' => 'authors'),
        'supports'    => array('title', 'editor', 'thumbnail'),
    ));
}
add_action('init', 'rel111_create_custom_post_types');

// Add meta box for selecting related authors
function rel111_add_author_meta_box() {
    add_meta_box(
        'related_authors',           // Unique ID
        'Related Authors',           // Box title
        'rel111_render_author_meta_box',    // Content callback, must be of type callable
        'books',                     // Post type
        'side',                      // Context (where the meta box will appear)
        'default'                    // Priority
    );
}
add_action('add_meta_boxes', 'rel111_add_author_meta_box');

// Render the meta box content
function rel111_render_author_meta_box($post) {
    // Get the existing value (related authors) if any
    $selected_authors = get_post_meta($post->ID, 'related_authors', true);

    // Get all authors to populate the select dropdown
    $authors = get_posts(array(
        'post_type'   => 'authors',
        'numberposts' => -1
    ));

    // Render a select dropdown with all authors
    echo '<select name="related_authors[]" multiple="multiple" style="width: 100%; height: 150px;">';
    foreach ($authors as $author) {
        $selected = (is_array($selected_authors) && in_array($author->ID, $selected_authors)) ? 'selected="selected"' : '';
        echo '<option value="' . $author->ID . '" ' . $selected . '>' . $author->post_title . '</option>';
    }
    echo '</select>';
}

// Save selected authors when the post is saved
function rel111_save_related_authors($post_id) {
    // Check if the current user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and save the selected authors
    if (isset($_POST['related_authors'])) {
        $authors = array_map('intval', $_POST['related_authors']);
        update_post_meta($post_id, 'related_authors', $authors);
    } else {
        // If no authors were selected, delete the meta key
        delete_post_meta($post_id, 'related_authors');
    }
}
add_action('save_post', 'rel111_save_related_authors');
