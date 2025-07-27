<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all tickets
$posts = get_posts( array( 'post_type' => 'support_ticket', 'numberposts' => -1 ) );
foreach ( $posts as $post ) {
    wp_delete_post( $post->ID, true );
}

// Delete terms
$taxes = array( 'ticket_status', 'ticket_priority' );
foreach ( $taxes as $tax ) {
    $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
    foreach ( $terms as $term ) {
        wp_delete_term( $term->term_id, $tax );
    }
}
