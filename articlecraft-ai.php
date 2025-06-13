<?php
/**
 * Plugin Name: ArticleCraft AI
 * Plugin URI: https://www.wizconsults.com
 * Description: AI-powered content generator that transforms URLs into engaging, professional articles using advanced AI models.
 * Version: 1.0.0
 * Author: Wiz Consults
 * Author URI: https://www.wizconsults.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: articlecraft-ai
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('ARTICLECRAFT_AI_VERSION', '1.0.0');
define('ARTICLECRAFT_AI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARTICLECRAFT_AI_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main ArticleCraft AI Plugin Class
 */
class ArticleCraftAI {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('ArticleCraftAI', 'uninstall'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('articlecraft-ai', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize components
        $this->init_components();
        
        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    private function include_files() {
        $includes = array(
            'includes/class-database.php',
            'includes/class-settings.php',
            'includes/class-admin.php',
            'includes/class-ajax-handler.php',
            'includes/class-ai-service.php',
            'includes/class-url-scraper.php',
            'includes/class-seo-integration.php',
            'includes/class-gutenberg-blocks.php'
        );
        
        foreach ($includes as $file) {
            $file_path = ARTICLECRAFT_AI_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log('ArticleCraft AI: Missing file ' . $file_path);
            }
        }
    }
    
    private function init_components() {
        // Initialize database
        new ArticleCraftAI_Database();
        
        // Initialize admin interface
        if (is_admin()) {
            new ArticleCraftAI_Settings();
            new ArticleCraftAI_Admin();
            new ArticleCraftAI_AjaxHandler();
        }
        
        // Initialize SEO integration
        new ArticleCraftAI_SEOIntegration();
        
        // Initialize Gutenberg blocks
        new ArticleCraftAI_GutenbergBlocks();
    }
    
    public function enqueue_admin_assets($hook) {
        // Only load on post edit pages and plugin settings
        if (!in_array($hook, array('post.php', 'post-new.php', 'settings_page_articlecraft-ai-settings', 'tools_page_articlecraft-ai-logs'))) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'articlecraft-ai-admin',
            ARTICLECRAFT_AI_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ARTICLECRAFT_AI_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'articlecraft-ai-admin',
            ARTICLECRAFT_AI_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ARTICLECRAFT_AI_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('articlecraft-ai-admin', 'articlecraftAI', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('articlecraft_ai_nonce'),
            'strings' => array(
                'generating' => __('Generating content...', 'articlecraft-ai'),
                'rewriting' => __('Rewriting content...', 'articlecraft-ai'),
                'success' => __('Content generated successfully!', 'articlecraft-ai'),
                'error' => __('An error occurred. Please try again.', 'articlecraft-ai'),
                'invalid_url' => __('Please enter a valid URL.', 'articlecraft-ai'),
                'no_content' => __('Please add some content to rewrite.', 'articlecraft-ai'),
                'confirm_clear' => __('Are you sure you want to clear the generated content?', 'articlecraft-ai')
            )
        ));
    }
    
    public function activate() {
        // Ensure the Database class is loaded first.
        require_once ARTICLECRAFT_AI_PLUGIN_PATH . 'includes/class-database.php';

        // Check for cURL
        if (!function_exists('curl_init')) {
            // Deactivate the plugin
            deactivate_plugins(plugin_basename(__FILE__));

            // Suppress "Plugin activated" message
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            // We'll rely on the existing requirements_notice() to show the error.
            // No need to add a separate admin_notice action here if check_requirements() handles it.
            return; // Stop further activation
        }

        // Create database tables
        $database = new ArticleCraftAI_Database();
        $database->create_tables();
        
        // Set default options
        $default_settings = array(
            'api_provider' => 'openai',
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'custom_persona' => ''
        );
        
        add_option('articlecraft_ai_settings', $default_settings);
        
        // Set activation timestamp
        add_option('articlecraft_ai_activated', current_time('timestamp'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up temporary data
        delete_transient('articlecraft_ai_cache');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Remove all plugin options
        delete_option('articlecraft_ai_settings');
        delete_option('articlecraft_ai_activated');
        delete_option('articlecraft_ai_db_version');
        
        // Remove database tables
        global $wpdb;
        $table_name = $wpdb->prefix . 'articlecraft_ai_logs';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        
        // Clean up any remaining transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'articlecraft_ai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_articlecraft_ai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_articlecraft_ai_%'");
        
        // Remove user meta
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'articlecraft_ai_%'");
        
        // Remove post meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'articlecraft_ai_%'");
    }
    
    public function get_version() {
        return ARTICLECRAFT_AI_VERSION;
    }
    
    public function check_requirements() {
        $requirements = array();
        
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $requirements[] = sprintf(
                __('PHP version 7.4 or higher is required. You are running version %s.', 'articlecraft-ai'),
                PHP_VERSION
            );
        }
        
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            $requirements[] = sprintf(
                __('WordPress version 5.0 or higher is required. You are running version %s.', 'articlecraft-ai'),
                get_bloginfo('version')
            );
        }
        
        if (!extension_loaded('curl')) {
            $requirements[] = __('cURL extension is required for API communications.', 'articlecraft-ai');
        }
        
        if (!extension_loaded('json')) {
            $requirements[] = __('JSON extension is required for data processing.', 'articlecraft-ai');
        }
        
        return $requirements;
    }
    
    public function requirements_notice() {
        $requirements = $this->check_requirements();
        
        if (!empty($requirements)) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>' . __('ArticleCraft AI Plugin Requirements:', 'articlecraft-ai') . '</strong><br>';
            echo implode('<br>', $requirements);
            echo '</p></div>';
        }
    }
    
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=articlecraft-ai-settings') . '">' . __('Settings', 'articlecraft-ai') . '</a>';
        $docs_link = '<a href="' . admin_url('options-general.php?page=articlecraft-ai-settings&tab=help') . '" target="_blank">' . __('Documentation', 'articlecraft-ai') . '</a>';
        
        array_unshift($links, $settings_link, $docs_link);
        
        return $links;
    }
    
    public function add_row_meta($links, $file) {
        if (plugin_basename(__FILE__) === $file) {
            $links[] = '<a href="https://github.com/cyberkarhub/articlecraft-ai" target="_blank">' . __('GitHub', 'articlecraft-ai') . '</a>';
            $links[] = '<a href="https://github.com/cyberkarhub/articlecraft-ai/issues" target="_blank">' . __('Support', 'articlecraft-ai') . '</a>';
            $links[] = '<a href="' . admin_url('tools.php?page=articlecraft-ai-logs') . '">' . __('Logs', 'articlecraft-ai') . '</a>';
        }
        
        return $links;
    }
}

// Initialize the plugin
function articlecraft_ai_init() {
    return ArticleCraftAI::get_instance();
}

// Start the plugin
add_action('init', 'articlecraft_ai_init');

// Add plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(ArticleCraftAI::get_instance(), 'add_action_links'));
add_filter('plugin_row_meta', array(ArticleCraftAI::get_instance(), 'add_row_meta'), 10, 2);

// Check requirements and show notices
add_action('admin_notices', array(ArticleCraftAI::get_instance(), 'requirements_notice'));

// Helper functions
function articlecraft_ai() {
    return ArticleCraftAI::get_instance();
}

function articlecraft_ai_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf('[ArticleCraft AI][%s] %s', strtoupper($level), $message));
    }
}

function articlecraft_ai_can_user_generate() {
    return current_user_can('edit_posts');
}

function articlecraft_ai_get_settings() {
    return get_option('articlecraft_ai_settings', array());
}

function articlecraft_ai_is_configured() {
    $settings = articlecraft_ai_get_settings();
    $provider = $settings['api_provider'] ?? 'openai';
    
    switch ($provider) {
        case 'openai':
            return !empty($settings['openai_api_key']);
        case 'claude':
            return !empty($settings['claude_api_key']);
        case 'gemini':
            return !empty($settings['gemini_api_key']);
        case 'deepseek':
            return !empty($settings['deepseek_api_key']);
        default:
            return false;
    }
}

function articlecraft_ai_format_processing_time($seconds) {
    if ($seconds < 1) {
        return round($seconds * 1000) . 'ms';
    } elseif ($seconds < 60) {
        return round($seconds, 2) . 's';
    } else {
        $minutes = floor($seconds / 60);
        $remaining_seconds = round($seconds % 60, 2);
        return $minutes . 'm ' . $remaining_seconds . 's';
    }
}

function articlecraft_ai_estimate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_speed = 200; // Average words per minute
    $minutes = ceil($word_count / $reading_speed);
    
    return $minutes;
}

function articlecraft_ai_clean_content($content) {
    $allowed_tags = array(
        'h1' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'p' => array(),
        'br' => array(),
        'strong' => array(),
        'em' => array(),
        'ul' => array(),
        'ol' => array(),
        'li' => array(),
        'blockquote' => array(),
        'a' => array('href' => array(), 'title' => array()),
        'img' => array('src' => array(), 'alt' => array(), 'title' => array()),
    );
    
    return wp_kses($content, $allowed_tags);
}