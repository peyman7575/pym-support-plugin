<?php
/**
 * Plugin Name: پشتیبانی پیشرفته
 * Description: افزونه حرفه‌ای پشتیبانی و تیکت با پنل مدیریت و کاربری فارسی
 * Version: 1.0.0
 * Author: peyman7575
 */

defined('ABSPATH') || exit;

// ساختار اولیه کلاس اصلی
class pym_Support {

    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
    }

    public function register_post_type() {
        // CPT later
    }
}

new pym_Support();
