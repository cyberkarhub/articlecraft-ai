<?php
/**
 * SEO Plugin Integration for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_SEOIntegration {
    
    public function __construct() {
        add_action('init', array($this, 'init_seo_integration'));
    }
    
    public function init_seo_integration() {
        // Hook into post save to apply SEO meta data
        add_action('wp_ajax_articlecraft_ai_apply_seo_meta', array($this, 'apply_seo_meta'));
    }
    
    public function apply_seo_meta() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'articlecraft_ai_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'articlecraft-ai')));
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'articlecraft-ai')));
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $meta_title = sanitize_text_field($_POST['meta_title'] ?? '');
        $meta_description = sanitize_textarea_field($_POST['meta_description'] ?? '');
        
        if (!$post_id || !$meta_title || !$meta_description) {
            wp_send_json_error(array('message' => __('Missing required data.', 'articlecraft-ai')));
        }
        
        $success = $this->set_seo_meta($post_id, $meta_title, $meta_description);
        
        if ($success) {
            wp_send_json_success(array(
                'message' => __('SEO meta data applied successfully!', 'articlecraft-ai'),
                'plugin' => $success
            ));
        } else {
            wp_send_json_error(array('message' => __('No compatible SEO plugin found.', 'articlecraft-ai')));
        }
    }
    
    public function set_seo_meta($post_id, $meta_title, $meta_description) {
        $applied_plugin = '';
        
        // Yoast SEO
        if ($this->is_yoast_active()) {
            update_post_meta($post_id, '_yoast_wpseo_title', $meta_title);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
            $applied_plugin = 'Yoast SEO';
        }
        // RankMath SEO
        elseif ($this->is_rankmath_active()) {
            update_post_meta($post_id, 'rank_math_title', $meta_title);
            update_post_meta($post_id, 'rank_math_description', $meta_description);
            $applied_plugin = 'RankMath SEO';
        }
        // SEOPress
        elseif ($this->is_seopress_active()) {
            update_post_meta($post_id, '_seopress_titles_title', $meta_title);
            update_post_meta($post_id, '_seopress_titles_desc', $meta_description);
            $applied_plugin = 'SEOPress';
        }
        // All in One SEO Pack
        elseif ($this->is_aioseo_active()) {
            update_post_meta($post_id, '_aioseo_title', $meta_title);
            update_post_meta($post_id, '_aioseo_description', $meta_description);
            $applied_plugin = 'All in One SEO';
        }
        // The SEO Framework
        elseif ($this->is_seo_framework_active()) {
            update_post_meta($post_id, '_genesis_title', $meta_title);
            update_post_meta($post_id, '_genesis_description', $meta_description);
            $applied_plugin = 'The SEO Framework';
        }
        // Squirrly SEO
        elseif ($this->is_squirrly_active()) {
            update_post_meta($post_id, '_sq_title', $meta_title);
            update_post_meta($post_id, '_sq_description', $meta_description);
            $applied_plugin = 'Squirrly SEO';
        }
        
        return $applied_plugin;
    }
    
    public function get_active_seo_plugin() {
        if ($this->is_yoast_active()) {
            return 'Yoast SEO';
        } elseif ($this->is_rankmath_active()) {
            return 'RankMath SEO';
        } elseif ($this->is_seopress_active()) {
            return 'SEOPress';
        } elseif ($this->is_aioseo_active()) {
            return 'All in One SEO';
        } elseif ($this->is_seo_framework_active()) {
            return 'The SEO Framework';
        } elseif ($this->is_squirrly_active()) {
            return 'Squirrly SEO';
        }
        
        return false;
    }
    
    private function is_yoast_active() {
        return class_exists('WPSEO_Options') || is_plugin_active('wordpress-seo/wp-seo.php');
    }
    
    private function is_rankmath_active() {
        return class_exists('RankMath') || is_plugin_active('seo-by-rank-math/rank-math.php');
    }
    
    private function is_seopress_active() {
        return class_exists('SEOPress') || is_plugin_active('wp-seopress/seopress.php');
    }
    
    private function is_aioseo_active() {
        return class_exists('AIOSEO\\Plugin\\AIOSEO') || is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php');
    }
    
    private function is_seo_framework_active() {
        return class_exists('The_SEO_Framework\\Load') || is_plugin_active('autodescription/autodescription.php');
    }
    
    private function is_squirrly_active() {
        return class_exists('SQ_Classes_Helpers_Tools') || is_plugin_active('squirrly-seo/squirrly.php');
    }
}