<?php
/**
 * Plugin Name: پشتیبانی پیشرفته
 * Description: افزونه حرفه‌ای تیکت و پشتیبانی برای کاربران فارسی
 * Version: 1.0.0
 * Author: peyman7575
 * Text Domain: pym-support
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-pym-support.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-pym-admin.php';

if ( class_exists( 'pym_Support_Plugin' ) ) {
    global $pym_support_plugin;
    $pym_support_plugin = new pym_Support_Plugin();
}

if ( is_admin() && class_exists( 'pym_Admin' ) ) {
    new pym_Admin();
}

/**
 * Handle AJAX create ticket
 */
function pym_ajax_new_ticket() {
    check_ajax_referer( 'pym_new_ticket', 'pym_nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'باید وارد شوید' );
    }

    $title    = sanitize_text_field( wp_unslash( $_POST['title'] ) );
    $message  = sanitize_textarea_field( wp_unslash( $_POST['message'] ) );
    $priority = sanitize_text_field( wp_unslash( $_POST['priority'] ) );

    $ticket_id = wp_insert_post( array(
        'post_type'   => 'support_ticket',
        'post_status' => 'publish',
        'post_title'  => $title,
        'post_content'=> $message,
        'post_author' => get_current_user_id(),
    ) );

    if ( $ticket_id ) {
        wp_set_object_terms( $ticket_id, $priority, 'ticket_priority' );
        wp_set_object_terms( $ticket_id, 'باز', 'ticket_status' );
        $user = wp_get_current_user();
        $admin_email = get_option( 'admin_email' );
        wp_mail( $admin_email, 'تیکت جدید', 'تیکت جدیدی ثبت شد.' );
        wp_mail( $user->user_email, 'ثبت تیکت', 'تیکت شما دریافت شد.' );
        wp_send_json_success( 'تیکت ثبت شد' );
    }
    wp_send_json_error( 'خطا در ثبت تیکت' );
}
add_action( 'wp_ajax_pym_new_ticket', 'pym_ajax_new_ticket' );
add_action( 'wp_ajax_nopriv_pym_new_ticket', 'pym_ajax_new_ticket' );

function pym_comment_notify( $comment_ID, $comment_approved, $commentdata ) {
    if ( $comment_approved && 'reply' === $commentdata['comment_type'] ) {
        $post = get_post( $commentdata['comment_post_ID'] );
        if ( $post && 'support_ticket' === $post->post_type ) {
            $author = get_user_by( 'id', $post->post_author );
            if ( $author ) {
                wp_mail( $author->user_email, 'پاسخ جدید', 'پاسخی برای تیکت شما ثبت شد.' );
            }
        }
    }
}
add_action( 'comment_post', 'pym_comment_notify', 10, 3 );
