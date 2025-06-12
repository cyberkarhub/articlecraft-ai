<?php
/**
 * AJAX handler for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_AjaxHandler {
    
    public function __construct() {
        add_action('wp_ajax_articlecraft_ai_generate_from_url', array($this, 'handle_generate_from_url'));
        add_action('wp_ajax_articlecraft_ai_rewrite_content', array($this, 'handle_rewrite_content'));
    }
    
    public function handle_generate_from_url() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'articlecraft_ai_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'articlecraft-ai')));
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'articlecraft-ai')));
        }
        
        try {
            $url = sanitize_url($_POST['url'] ?? '');
            $structure_mode = sanitize_text_field($_POST['structure_mode'] ?? 'maintain');
            
            if (empty($url)) {
                wp_send_json_error(array('message' => __('URL is required.', 'articlecraft-ai')));
            }
            
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                wp_send_json_error(array('message' => __('Please provide a valid URL.', 'articlecraft-ai')));
            }
            
            // Generate content using AI service
            $ai_service = new ArticleCraftAI_AIService();
            $result = $ai_service->generate_from_url($url, $structure_mode);
            
            if (!$result) {
                wp_send_json_error(array('message' => __('Failed to generate content. Please try again.', 'articlecraft-ai')));
            }
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            error_log('ArticleCraft AI Generate Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    public function handle_rewrite_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'articlecraft_ai_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'articlecraft-ai')));
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'articlecraft-ai')));
        }
        
        try {
            $content = wp_kses_post($_POST['content'] ?? '');
            $structure_mode = sanitize_text_field($_POST['structure_mode'] ?? 'maintain');
            
            if (empty(trim($content))) {
                wp_send_json_error(array('message' => __('Content is required for rewriting.', 'articlecraft-ai')));
            }
            
            // Rewrite content using AI service
            $ai_service = new ArticleCraftAI_AIService();
            $result = $ai_service->rewrite_content($content, $structure_mode);
            
            if (!$result) {
                wp_send_json_error(array('message' => __('Failed to rewrite content. Please try again.', 'articlecraft-ai')));
            }
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            error_log('ArticleCraft AI Rewrite Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
}