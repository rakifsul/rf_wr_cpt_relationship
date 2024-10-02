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
    // register Custom Post Type: Books
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

    // register Custom Post Type: Authors
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

// tambahkan meta box
function rel111_add_author_meta_box() {
    add_meta_box(
        'related_authors',
        'Related Authors',
        'rel111_render_author_meta_box',
        'books',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'rel111_add_author_meta_box');

// render konten meta box
function rel111_render_author_meta_box($post) {
    // dapatkan value (related authors) yang sudah ada jika ada
    $selected_authors = get_post_meta($post->ID, 'related_authors', true);

    // dapatkan semua authors untuk mengisi dropdown
    $authors = get_posts(array(
        'post_type'   => 'authors',
        'numberposts' => -1
    ));

    // render dropdown
    echo '<select name="related_authors[]" multiple="multiple" style="width: 100%; height: 150px;">';
    foreach ($authors as $author) {
        $selected = (is_array($selected_authors) && in_array($author->ID, $selected_authors)) ? 'selected="selected"' : '';
        echo '<option value="' . $author->ID . '" ' . $selected . '>' . $author->post_title . '</option>';
    }
    echo '</select>';
}

// save author terpilih
function rel111_save_related_authors($post_id) {
    // jika user bisa edit_post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // sanitize dan save selected authors
    if (isset($_POST['related_authors'])) {
        $authors = array_map('intval', $_POST['related_authors']);
        update_post_meta($post_id, 'related_authors', $authors);
    } else {
        // jika tidak ada author selected, delete meta key
        delete_post_meta($post_id, 'related_authors');
    }
}
add_action('save_post', 'rel111_save_related_authors');
