<?php
/*
Plugin Name: Syntax Highlighter
Description: Плагин для WordPress для добавления подсветки синтаксиса с помощью Highlight.js в блоки кода, с возможностью настройки для определённых типов страниц и записей.
Version: 1.0.0
Author: Artem Zenevich
Author URI: https://github.com/artixzenevich
*/

if (!defined('ABSPATH')) {
    exit; // Выход, если файл был вызван напрямую.
}

require_once plugin_dir_path(__FILE__) . 'settings.php';

// Подключаем Highlight.js только на определённых страницах/записях
function hjs_enqueue_highlightjs() {
    // Получаем типы записей, указанные в настройках.
    $enabled_post_types = get_option('hjs_enabled_post_types', []);
    $selected_theme = get_option('hjs_selected_theme', 'default');

    if (is_singular($enabled_post_types)) {
        wp_enqueue_style('highlightjs-style', plugin_dir_url(__FILE__) . 'assets/styles/' . $selected_theme);
        wp_enqueue_style('highlightjs-style-plugin', plugin_dir_url(__FILE__) . 'assets/style.css');
        wp_enqueue_script('highlightjs-script', plugin_dir_url(__FILE__) . 'assets/highlight.min.js', [], null, false);
        wp_add_inline_script('highlightjs-script', 'hljs.highlightAll();');
    }
}

add_action('wp_enqueue_scripts', 'hjs_enqueue_highlightjs');
function create_block_syntax_highlighter_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_syntax_highlighter_block_init' );
