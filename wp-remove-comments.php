<?php

/**
 * Remove and disable all comments from Wordpress.
 * Just copy the functions below to the end of your theme's functions.php
 *
 * Author: Tiago Faucz
 *
 */

// Disable and Delete Comments 
add_action('admin_init', function () 
{
    global $pagenow;
    global $wpdb;
    
    // Redirect any user trying to access comments page
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    // Deletes all comments from the database and cleans up comment count
    if($wpdb->query("DELETE FROM $wpdb->comments WHERE 1 = 1") != FALSE)
    {
        $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
        $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
        
        if ( get_option( '_transient_as_comment_count' ) !== false ) 
        {
          update_option( '_transient_as_comment_count', "" );
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});