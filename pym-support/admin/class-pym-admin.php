<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin features
 */
class pym_Admin {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_support_ticket', array( $this, 'save_meta' ) );
        add_filter( 'manage_support_ticket_posts_columns', array( $this, 'columns' ) );
        add_action( 'manage_support_ticket_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box( 'pym_reply', 'پاسخ', array( $this, 'reply_box' ), 'support_ticket' );
    }

    public function reply_box( $post ) {
        echo '<textarea name="pym_reply" style="width:100%" placeholder="پاسخ"></textarea>';
    }

    /**
     * Save meta data
     */
    public function save_meta( $post_id ) {
        if ( isset( $_POST['pym_reply'] ) ) {
            $reply = sanitize_textarea_field( $_POST['pym_reply'] );
            if ( $reply ) {
                wp_insert_comment( array(
                    'comment_post_ID' => $post_id,
                    'comment_content' => $reply,
                    'user_id'         => get_current_user_id(),
                    'comment_type'    => 'reply',
                    'comment_approved'=> 1,
                ) );
            }
        }
    }

    /**
     * Manage columns
     */
    public function columns( $cols ) {
        $cols['status']   = 'وضعیت';
        $cols['priority'] = 'اولویت';
        return $cols;
    }

    public function column_content( $column, $post_id ) {
        if ( 'status' === $column ) {
            $terms = get_the_terms( $post_id, 'ticket_status' );
            if ( $terms ) {
                echo esc_html( $terms[0]->name );
            }
        }
        if ( 'priority' === $column ) {
            $terms = get_the_terms( $post_id, 'ticket_priority' );
            if ( $terms ) {
                echo esc_html( $terms[0]->name );
            }
        }
    }
}
