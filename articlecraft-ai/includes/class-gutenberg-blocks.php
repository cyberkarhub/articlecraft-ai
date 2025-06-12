<?php
/**
 * Gutenberg blocks integration for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_GutenbergBlocks {
    
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_filter('block_categories_all', array($this, 'add_block_category'), 10, 2);
    }
    
    public function register_blocks() {
        // Register ArticleCraft AI Generator block
        register_block_type('articlecraft-ai/generator', array(
            'render_callback' => array($this, 'render_generator_block'),
            'attributes' => array(
                'url' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'structureMode' => array(
                    'type' => 'string',
                    'default' => 'maintain'
                ),
                'showProgress' => array(
                    'type' => 'boolean',
                    'default' => false
                )
            )
        ));
        
        // Register ArticleCraft AI Content block
        register_block_type('articlecraft-ai/generated-content', array(
            'render_callback' => array($this, 'render_content_block'),
            'attributes' => array(
                'content' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'metaTitle' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'metaDescription' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'generatedAt' => array(
                    'type' => 'string',
                    'default' => ''
                )
            )
        ));
    }
    
    public function enqueue_block_editor_assets() {
        // Enqueue block editor JavaScript
        wp_enqueue_script(
            'articlecraft-ai-blocks',
            ARTICLECRAFT_AI_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            ARTICLECRAFT_AI_VERSION,
            true
        );
        
        // Enqueue block editor CSS
        wp_enqueue_style(
            'articlecraft-ai-blocks',
            ARTICLECRAFT_AI_PLUGIN_URL . 'assets/css/blocks.css',
            array('wp-edit-blocks'),
            ARTICLECRAFT_AI_VERSION
        );
        
        // Localize script for blocks
        wp_localize_script('articlecraft-ai-blocks', 'articlecraftAIBlocks', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('articlecraft_ai_nonce'),
            'isConfigured' => articlecraft_ai_is_configured(),
            'settingsUrl' => admin_url('options-general.php?page=articlecraft-ai-settings'),
            'strings' => array(
                'title' => __('ArticleCraft AI Generator', 'articlecraft-ai'),
                'description' => __('Generate AI-powered content from URLs or rewrite existing content', 'articlecraft-ai'),
                'urlPlaceholder' => __('Enter URL to generate content from...', 'articlecraft-ai'),
                'generateButton' => __('Generate Content', 'articlecraft-ai'),
                'rewriteButton' => __('Rewrite Content', 'articlecraft-ai'),
                'structureMode' => __('Structure Mode', 'articlecraft-ai'),
                'maintainStructure' => __('Maintain Structure', 'articlecraft-ai'),
                'fullRevamp' => __('Full Revamp', 'articlecraft-ai'),
                'generating' => __('Generating...', 'articlecraft-ai'),
                'success' => __('Content generated successfully!', 'articlecraft-ai'),
                'error' => __('Error generating content', 'articlecraft-ai'),
                'notConfigured' => __('ArticleCraft AI is not configured. Please set up your API keys in settings.', 'articlecraft-ai'),
                'configure' => __('Configure Settings', 'articlecraft-ai')
            )
        ));
    }
    
    public function add_block_category($categories, $post) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug' => 'articlecraft-ai',
                    'title' => __('ArticleCraft AI', 'articlecraft-ai'),
                    'icon' => 'admin-post'
                )
            )
        );
    }
    
    public function render_generator_block($attributes) {
        $url = $attributes['url'] ?? '';
        $structure_mode = $attributes['structureMode'] ?? 'maintain';
        $show_progress = $attributes['showProgress'] ?? false;
        
        ob_start();
        ?>
        <div class="articlecraft-ai-generator-block" data-structure-mode="<?php echo esc_attr($structure_mode); ?>">
            <div class="generator-header">
                <h3><?php _e('🚀 ArticleCraft AI Generator', 'articlecraft-ai'); ?></h3>
                <p><?php _e('Generate professional content from URLs or enhance existing text', 'articlecraft-ai'); ?></p>
            </div>
            
            <div class="generator-controls">
                <div class="url-input-section">
                    <label for="block-url-input"><?php _e('🔗 URL to Generate From:', 'articlecraft-ai'); ?></label>
                    <input type="url" 
                           id="block-url-input" 
                           class="url-input" 
                           value="<?php echo esc_attr($url); ?>"
                           placeholder="<?php esc_attr_e('https://example.com/article', 'articlecraft-ai'); ?>" />
                    <button type="button" class="generate-from-url-btn button button-primary">
                        <?php _e('Generate Article', 'articlecraft-ai'); ?>
                    </button>
                </div>
                
                <div class="structure-options">
                    <label><?php _e('Structure Mode:', 'articlecraft-ai'); ?></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="structure-mode" value="maintain" <?php checked($structure_mode, 'maintain'); ?>>
                            <span><?php _e('Maintain Structure', 'articlecraft-ai'); ?></span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="structure-mode" value="revamp" <?php checked($structure_mode, 'revamp'); ?>>
                            <span><?php _e('Full Revamp', 'articlecraft-ai'); ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="rewrite-section">
                    <button type="button" class="rewrite-content-btn button button-secondary">
                        <?php _e('✨ Rewrite Current Content', 'articlecraft-ai'); ?>
                    </button>
                </div>
            </div>
            
            <?php if ($show_progress): ?>
            <div class="block-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"><?php _e('Processing...', 'articlecraft-ai'); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="generator-results" style="display: none;">
                <h4><?php _e('📊 Generated Meta Information', 'articlecraft-ai'); ?></h4>
                <div class="meta-fields">
                    <div class="meta-field">
                        <label><?php _e('Meta Title:', 'articlecraft-ai'); ?></label>
                        <input type="text" class="meta-title" readonly />
                    </div>
                    <div class="meta-field">
                        <label><?php _e('Meta Description:', 'articlecraft-ai'); ?></label>
                        <textarea class="meta-description" readonly></textarea>
                    </div>
                </div>
                <button type="button" class="apply-seo-btn button button-primary">
                    <?php _e('Apply to SEO Plugin', 'articlecraft-ai'); ?>
                </button>
            </div>
        </div>
        
        <style>
        .articlecraft-ai-generator-block {
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .generator-header h3 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 18px;
        }
        
        .generator-header p {
            margin: 0 0 20px 0;
            color: #64748b;
            font-size: 14px;
        }
        
        .generator-controls {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .url-input-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .url-input-section label {
            font-weight: 600;
            color: #374151;
        }
        
        .url-input {
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .url-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .generate-from-url-btn {
            align-self: flex-start;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        
        .structure-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .structure-options label {
            font-weight: 600;
            color: #374151;
        }
        
        .radio-group {
            display: flex;
            gap: 15px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: normal;
        }
        
        .rewrite-content-btn {
            align-self: flex-start;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 6px;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
        }
        
        .block-progress {
            margin: 15px 0;
            padding: 15px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #0ea5e9;
            border-radius: 6px;
            text-align: center;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0f2fe;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9, #0284c7);
            width: 0%;
            transition: width 0.3s ease;
            animation: progress-animation 3s infinite;
        }
        
        @keyframes progress-animation {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        
        .progress-text {
            font-weight: 600;
            color: #0c4a6e;
            font-size: 14px;
        }
        
        .generator-results {
            margin-top: 20px;
            padding: 15px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        
        .generator-results h4 {
            margin: 0 0 15px 0;
            color: #1e293b;
        }
        
        .meta-fields {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .meta-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .meta-field label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }
        
        .meta-title,
        .meta-description {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 4px;
            font-size: 13px;
            background: #f8fafc;
        }
        
        .meta-description {
            resize: vertical;
            height: 60px;
        }
        
        .apply-seo-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public function render_content_block($attributes) {
        $content = $attributes['content'] ?? '';
        $meta_title = $attributes['metaTitle'] ?? '';
        $meta_description = $attributes['metaDescription'] ?? '';
        $generated_at = $attributes['generatedAt'] ?? '';
        
        if (empty($content)) {
            return '<div class="articlecraft-ai-placeholder">' . __('No generated content yet.', 'articlecraft-ai') . '</div>';
        }
        
        ob_start();
        ?>
        <div class="articlecraft-ai-generated-content">
            <?php if ($generated_at): ?>
            <div class="generation-info">
                <small><?php printf(__('Generated on %s by ArticleCraft AI', 'articlecraft-ai'), date('M j, Y \a\t g:i A', strtotime($generated_at))); ?></small>
            </div>
            <?php endif; ?>
            
            <div class="generated-content">
                <?php echo wp_kses_post($content); ?>
            </div>
            
            <?php if ($meta_title || $meta_description): ?>
            <div class="meta-info">
                <h4><?php _e('SEO Meta Information', 'articlecraft-ai'); ?></h4>
                <?php if ($meta_title): ?>
                <div class="meta-item">
                    <strong><?php _e('Title:', 'articlecraft-ai'); ?></strong> <?php echo esc_html($meta_title); ?>
                </div>
                <?php endif; ?>
                <?php if ($meta_description): ?>
                <div class="meta-item">
                    <strong><?php _e('Description:', 'articlecraft-ai'); ?></strong> <?php echo esc_html($meta_description); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .articlecraft-ai-generated-content {
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            background: #f8fafc;
            border-radius: 0 8px 8px 0;
        }
        
        .generation-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .generation-info small {
            color: #64748b;
            font-style: italic;
        }
        
        .generated-content {
            line-height: 1.7;
            color: #1e293b;
        }
        
        .generated-content h2,
        .generated-content h3 {
            color: #1e293b;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .generated-content p {
            margin-bottom: 16px;
        }
        
        .generated-content strong {
            color: #3b82f6;
            font-weight: 600;
        }
        
        .meta-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .meta-info h4 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 14px;
        }
        
        .meta-item {
            margin-bottom: 8px;
            font-size: 13px;
            color: #64748b;
        }
        
        .meta-item strong {
            color: #374151;
        }
        
        .articlecraft-ai-placeholder {
            padding: 40px;
            text-align: center;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            color: #64748b;
            font-style: italic;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}