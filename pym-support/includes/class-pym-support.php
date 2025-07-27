<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class
 */
class pym_Support_Plugin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_menu_item' ) );
        add_action( 'init', array( $this, 'add_wc_endpoint' ) );
        add_action( 'woocommerce_account_pym-tickets_endpoint', array( $this, 'render_wc_endpoint' ) );
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'pym-support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Register custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => 'تیکت‌ها',
            'singular_name'      => 'تیکت',
            'menu_name'          => 'تیکت‌ها',
            'name_admin_bar'     => 'تیکت',
            'add_new'            => 'افزودن جدید',
            'add_new_item'       => 'تیکت جدید',
            'new_item'           => 'تیکت جدید',
            'edit_item'          => 'ویرایش تیکت',
            'view_item'          => 'مشاهده تیکت',
            'all_items'          => 'همه تیکت‌ها',
            'search_items'       => 'جستجوی تیکت',
            'not_found'          => 'موردی پیدا نشد',
            'not_found_in_trash' => 'در زباله دان چیزی نیست',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'post',
            'supports'           => array( 'title', 'editor', 'author', 'comments' ),
        );

        register_post_type( 'support_ticket', $args );
    }

    /**
     * Register taxonomies for status and priority
     */
    public function register_taxonomies() {
        // وضعیت
        $status_labels = array(
            'name'          => 'وضعیت',
            'singular_name' => 'وضعیت',
        );
        register_taxonomy( 'ticket_status', 'support_ticket', array(
            'labels'            => $status_labels,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
        ) );
        if ( ! term_exists( 'باز', 'ticket_status' ) ) {
            wp_insert_term( 'باز', 'ticket_status' );
            wp_insert_term( 'در حال بررسی', 'ticket_status' );
            wp_insert_term( 'بسته‌شده', 'ticket_status' );
        }

        // اولویت
        $priority_labels = array(
            'name'          => 'اولویت',
            'singular_name' => 'اولویت',
        );
        register_taxonomy( 'ticket_priority', 'support_ticket', array(
            'labels'            => $priority_labels,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
        ) );
        if ( ! term_exists( 'کم', 'ticket_priority' ) ) {
            wp_insert_term( 'کم', 'ticket_priority' );
            wp_insert_term( 'متوسط', 'ticket_priority' );
            wp_insert_term( 'زیاد', 'ticket_priority' );
        }
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'user_tickets', array( $this, 'shortcode_user_tickets' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'pym-support', plugins_url( '../public/style.css', __FILE__ ), array(), '1.0' );
        wp_enqueue_script( 'pym-support', plugins_url( '../public/script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
        wp_localize_script( 'pym-support', 'pym_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
    }

    /**
     * Shortcode output for user tickets
     */
    public function shortcode_user_tickets() {
        if ( ! is_user_logged_in() ) {
            return '<p>برای مشاهده تیکت‌ها لطفاً وارد شوید.</p>';
        }
        ob_start();
        ?>
        <div id="pym-ticket-form" class="rtl">
            <form id="pym-new-ticket" method="post">
                <label>موضوع</label>
                <input type="text" name="title" required />
                <label>پیام</label>
                <textarea name="message" required></textarea>
                <label>اولویت</label>
                <select name="priority">
                    <option value="کم">کم</option>
                    <option value="متوسط">متوسط</option>
                    <option value="زیاد">زیاد</option>
                </select>
                <input type="hidden" name="action" value="pym_new_ticket" />
                <?php wp_nonce_field( 'pym_new_ticket', 'pym_nonce' ); ?>
                <button type="submit">ارسال</button>
            </form>
        </div>
        <div id="pym-ticket-list" class="rtl">
            <?php
            $args   = array(
                'post_type'      => 'support_ticket',
                'posts_per_page' => -1,
                'author'         => get_current_user_id(),
                'post_status'    => 'publish',
            );
            $tickets = get_posts( $args );
            if ( $tickets ) {
                echo '<ul>';
                foreach ( $tickets as $ticket ) {
                    echo '<li>' . esc_html( $ticket->post_title ) . '</li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add WooCommerce my-account menu item
     */
    public function woocommerce_menu_item( $items ) {
        $new = array();
        foreach ( $items as $key => $item ) {
            $new[ $key ] = $item;
            if ( 'orders' === $key ) {
                $new['pym-tickets'] = 'تیکت‌های من';
            }
        }
        return $new;
    }

    /**
     * Add WooCommerce endpoint
     */
    public function add_wc_endpoint() {
        add_rewrite_endpoint( 'pym-tickets', EP_PAGES );
    }

    /**
     * Render WooCommerce endpoint
     */
    public function render_wc_endpoint() {
        echo do_shortcode( '[user_tickets]' );
    }
}
