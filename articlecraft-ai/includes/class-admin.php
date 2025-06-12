<?php
/**
 * Admin functionality for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_Admin {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_head', array($this, 'add_admin_head_styles'));
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'articlecraft-ai-generator',
            __('ArticleCraft AI Generator', 'articlecraft-ai'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'high'
        );
    }
    
    public function add_admin_head_styles() {
        global $typenow;
        if ($typenow === 'post') {
            ?>
            <style>
            #articlecraft-ai-generator .postbox-header {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                color: #ffffff;
                border-radius: 8px 8px 0 0;
            }
            
            #articlecraft-ai-generator .postbox-header h2 {
                color: #ffffff;
                font-weight: 600;
            }
            
            #articlecraft-ai-generator .postbox-header .handle-order-higher,
            #articlecraft-ai-generator .postbox-header .handle-order-lower,
            #articlecraft-ai-generator .postbox-header .handlediv {
                color: #ffffff;
            }
            
            #articlecraft-ai-generator {
                border: 2px solid #3b82f6;
                border-radius: 8px;
                box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
            }
            </style>
            <?php
        }
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('articlecraft_ai_meta_box', 'articlecraft_ai_nonce');
        ?>
        <div class="articlecraft-ai-meta-box">
            <div class="articlecraft-ai-section">
                <h4>🔗 Generate from URL</h4>
                <p class="description"><?php _e('Paste any URL to generate a professional article based on the content with AI-powered analysis.', 'articlecraft-ai'); ?></p>
                <input type="url" 
                       id="articlecraft-ai-url" 
                       class="widefat" 
                       placeholder="https://example.com/article"
                       data-tooltip="<?php esc_attr_e('Enter a valid URL to scrape and generate content from', 'articlecraft-ai'); ?>" />
                
                <!-- Structure Options -->
                <div class="articlecraft-ai-structure-options">
                    <label style="font-weight: 600; margin: 10px 0 8px 0; display: block;"><?php _e('Structure Options:', 'articlecraft-ai'); ?></label>
                    <div class="structure-radio-group">
                        <label class="structure-option">
                            <input type="radio" name="articlecraft_structure_mode" value="maintain" checked>
                            <span class="radio-label">
                                <strong><?php _e('Maintain Structure', 'articlecraft-ai'); ?></strong>
                                <small><?php _e('Keep the original text structure and format', 'articlecraft-ai'); ?></small>
                            </span>
                        </label>
                        <label class="structure-option">
                            <input type="radio" name="articlecraft_structure_mode" value="revamp">
                            <span class="radio-label">
                                <strong><?php _e('Full Revamp', 'articlecraft-ai'); ?></strong>
                                <small><?php _e('Completely restructure and reformat the content', 'articlecraft-ai'); ?></small>
                            </span>
                        </label>
                    </div>
                    <p class="description" style="font-style: italic; color: #666; margin-top: 8px;">
                        <?php _e('💬 Quotes from the original text will be preserved in both options.', 'articlecraft-ai'); ?>
                    </p>
                </div>
                
                <button type="button" 
                        id="articlecraft-ai-generate-from-url" 
                        class="button button-primary"
                        data-tooltip="<?php esc_attr_e('Generate a new article from the URL content', 'articlecraft-ai'); ?>">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php _e('Generate Article', 'articlecraft-ai'); ?>
                </button>
            </div>
            
            <div class="articlecraft-ai-section">
                <h4>✨ Rewrite Current Content</h4>
                <p class="description"><?php _e('Enhance and rewrite your existing content using advanced AI to improve quality, readability, and engagement.', 'articlecraft-ai'); ?></p>
                
                <!-- Structure Options for Rewrite -->
                <div class="articlecraft-ai-structure-options">
                    <div class="structure-radio-group">
                        <label class="structure-option">
                            <input type="radio" name="articlecraft_rewrite_structure_mode" value="maintain" checked>
                            <span class="radio-label">
                                <strong><?php _e('Maintain Structure', 'articlecraft-ai'); ?></strong>
                                <small><?php _e('Keep the original text structure and format', 'articlecraft-ai'); ?></small>
                            </span>
                        </label>
                        <label class="structure-option">
                            <input type="radio" name="articlecraft_rewrite_structure_mode" value="revamp">
                            <span class="radio-label">
                                <strong><?php _e('Full Revamp', 'articlecraft-ai'); ?></strong>
                                <small><?php _e('Completely restructure and reformat the content', 'articlecraft-ai'); ?></small>
                            </span>
                        </label>
                    </div>
                    <p class="description" style="font-style: italic; color: #666; margin-top: 8px;">
                        <?php _e('💬 Quotes from the original text will be preserved in both options.', 'articlecraft-ai'); ?>
                    </p>
                </div>
                
                <button type="button" 
                        id="articlecraft-ai-rewrite-content" 
                        class="button button-secondary"
                        data-tooltip="<?php esc_attr_e('Improve your current article content with AI enhancement', 'articlecraft-ai'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                    <?php _e('Rewrite Article', 'articlecraft-ai'); ?>
                </button>
            </div>
            
            <!-- Progress box positioned here -->
            <div id="articlecraft-ai-progress" class="articlecraft-ai-progress" style="display: none;">
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-spinner">
                        <div class="spinner"></div>
                    </div>
                </div>
                <p class="progress-text"><?php _e('Processing your request...', 'articlecraft-ai'); ?></p>
                <div class="progress-messages">
                    <div class="message" data-step="1">🔍 <?php _e('Analyzing content and extracting key information...', 'articlecraft-ai'); ?></div>
                    <div class="message" data-step="2">🤖 <?php _e('Generating high-quality content with AI...', 'articlecraft-ai'); ?></div>
                    <div class="message" data-step="3">✨ <?php _e('Finalizing content and optimizing for SEO...', 'articlecraft-ai'); ?></div>
                </div>
            </div>
            
            <div class="articlecraft-ai-section">
                <h4>📊 Generated Meta Data</h4>
                <p class="description"><?php _e('SEO-optimized meta information automatically generated by AI.', 'articlecraft-ai'); ?></p>
                
                <label for="articlecraft-ai-meta-title">
                    <span class="dashicons dashicons-tag"></span>
                    <?php _e('Meta Title:', 'articlecraft-ai'); ?>
                </label>
                <input type="text" 
                       id="articlecraft-ai-meta-title" 
                       class="widefat" 
                       maxlength="50" 
                       readonly 
                       placeholder="<?php esc_attr_e('Auto-generated SEO title will appear here', 'articlecraft-ai'); ?>" />
                <small class="description"><?php _e('Generated meta title (max 50 characters)', 'articlecraft-ai'); ?></small>
                
                <label for="articlecraft-ai-meta-description">
                    <span class="dashicons dashicons-text-page"></span>
                    <?php _e('Meta Description:', 'articlecraft-ai'); ?>
                </label>
                <textarea id="articlecraft-ai-meta-description" 
                          class="widefat" 
                          rows="3" 
                          maxlength="155" 
                          readonly 
                          placeholder="<?php esc_attr_e('Auto-generated SEO description will appear here', 'articlecraft-ai'); ?>"></textarea>
                <small class="description"><?php _e('Generated meta description (120-155 characters)', 'articlecraft-ai'); ?></small>
            </div>
            
            <div class="articlecraft-ai-help-section">
                <a href="<?php echo admin_url('options-general.php?page=articlecraft-ai-settings&tab=help'); ?>" 
                   class="button button-link" 
                   target="_blank"
                   data-tooltip="<?php esc_attr_e('Open comprehensive help documentation', 'articlecraft-ai'); ?>">
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('Help & Documentation', 'articlecraft-ai'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('ArticleCraft AI Settings', 'articlecraft-ai'),
            __('ArticleCraft AI', 'articlecraft-ai'),
            'manage_options',
            'articlecraft-ai-settings',
            array($this, 'settings_page')
        );
        
        add_management_page(
            __('ArticleCraft AI Logs', 'articlecraft-ai'),
            __('ArticleCraft AI Logs', 'articlecraft-ai'),
            'manage_options',
            'articlecraft-ai-logs',
            array($this, 'logs_page')
        );
    }
    
    public function settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
        
        if (!in_array($active_tab, array('settings', 'help'))) {
            $active_tab = 'settings';
        }
        ?>
        <div class="wrap articlecraft-ai-settings-wrap">
            <h1 style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;">✨</span>
                <?php _e('ArticleCraft AI', 'articlecraft-ai'); ?>
                <span style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">v<?php echo ARTICLECRAFT_AI_VERSION; ?></span>
            </h1>
            
            <h2 class="nav-tab-wrapper" style="border-bottom: 2px solid #e2e8f0; margin-bottom: 20px;">
                <a href="?page=articlecraft-ai-settings&tab=settings" 
                   class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"
                   style="<?php echo $active_tab == 'settings' ? 'background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border-color: #3b82f6;' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help" 
                   class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>"
                   style="<?php echo $active_tab == 'help' ? 'background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border-color: #3b82f6;' : ''; ?>">
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('Help & Documentation', 'articlecraft-ai'); ?>
                </a>
            </h2>
            
            <?php if ($active_tab == 'settings'): ?>
                <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('articlecraft_ai_settings');
                        do_settings_sections('articlecraft_ai_settings');
                        ?>
                        <p class="submit">
                            <button type="submit" class="button-primary" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                                <span class="dashicons dashicons-yes"></span>
                                <?php _e('Save Changes', 'articlecraft-ai'); ?>
                            </button>
                        </p>
                    </form>
                </div>
            <?php elseif ($active_tab == 'help'): ?>
                <?php $this->render_help_content(); ?>
            <?php endif; ?>
        </div>
        
        <style>
        .articlecraft-ai-settings-wrap .nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s ease;
        }
        
        .articlecraft-ai-settings-wrap .nav-tab:not(.nav-tab-active):hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        </style>
        <?php
    }
    
    public function render_help_content() {
        $active_help_tab = isset($_GET['help_tab']) ? $_GET['help_tab'] : 'getting-started';
        
        if (!in_array($active_help_tab, array('getting-started', 'usage', 'features', 'troubleshooting', 'api-setup', 'blocks'))) {
            $active_help_tab = 'getting-started';
        }
        ?>
        <div class="articlecraft-ai-help">
            <div class="nav-tab-wrapper" style="margin-bottom: 0;">
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=getting-started" 
                   class="nav-tab <?php echo $active_help_tab == 'getting-started' ? 'nav-tab-active' : ''; ?>">
                    ✨ <?php _e('Getting Started', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=usage" 
                   class="nav-tab <?php echo $active_help_tab == 'usage' ? 'nav-tab-active' : ''; ?>">
                    📝 <?php _e('Usage Guide', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=blocks" 
                   class="nav-tab <?php echo $active_help_tab == 'blocks' ? 'nav-tab-active' : ''; ?>">
                    🧱 <?php _e('Gutenberg Blocks', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=features" 
                   class="nav-tab <?php echo $active_help_tab == 'features' ? 'nav-tab-active' : ''; ?>">
                    🌟 <?php _e('Features', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=troubleshooting" 
                   class="nav-tab <?php echo $active_help_tab == 'troubleshooting' ? 'nav-tab-active' : ''; ?>">
                    🛠️ <?php _e('Troubleshooting', 'articlecraft-ai'); ?>
                </a>
                <a href="?page=articlecraft-ai-settings&tab=help&help_tab=api-setup" 
                   class="nav-tab <?php echo $active_help_tab == 'api-setup' ? 'nav-tab-active' : ''; ?>">
                    🔑 <?php _e('API Setup', 'articlecraft-ai'); ?>
                </a>
            </div>

            <?php if ($active_help_tab == 'getting-started'): ?>
            <div class="tab-content">
                <h2><?php _e('✨ Getting Started with ArticleCraft AI', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <h3><?php _e('Welcome to the Future of Content Creation!', 'articlecraft-ai'); ?></h3>
                    <p style="font-size: 16px; line-height: 1.6; color: #64748b;"><?php _e('ArticleCraft AI revolutionizes how you create content by leveraging advanced artificial intelligence to generate professional, engaging articles that match your unique writing style and audience.', 'articlecraft-ai'); ?></p>
                    
                    <h4><?php _e('🎯 Quick Setup Steps:', 'articlecraft-ai'); ?></h4>
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 25px; border-radius: 12px; border-left: 5px solid #3b82f6; margin: 20px 0;">
                        <ol style="font-size: 15px; line-height: 1.8;">
                            <li><strong><?php _e('🔑 Configure API Keys:', 'articlecraft-ai'); ?></strong> <?php _e('Visit the Settings tab and add your preferred AI provider API key (OpenAI, Claude, Gemini, or DeepSeek)', 'articlecraft-ai'); ?></li>
                            <li><strong><?php _e('🤖 Choose AI Provider:', 'articlecraft-ai'); ?></strong> <?php _e('Select the AI model that best fits your needs and budget', 'articlecraft-ai'); ?></li>
                            <li><strong><?php _e('🎭 Customize Persona (Optional):', 'articlecraft-ai'); ?></strong> <?php _e('Create a custom writing persona that perfectly matches your publication style', 'articlecraft-ai'); ?></li>
                            <li><strong><?php _e('📝 Start Creating:', 'articlecraft-ai'); ?></strong> <?php _e('Edit any post and use the ArticleCraft AI Generator to create amazing content', 'articlecraft-ai'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="notice notice-info" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #10b981; padding: 20px; border-radius: 8px;">
                        <h4 style="margin: 0 0 10px 0; color: #065f46;">📊 Plugin Information</h4>
                        <p style="margin: 5px 0;"><strong><?php _e('Version:', 'articlecraft-ai'); ?></strong> <?php echo ARTICLECRAFT_AI_VERSION; ?></p>
                        <p style="margin: 5px 0;"><strong><?php _e('Last Updated:', 'articlecraft-ai'); ?></strong> 2025-06-12 04:58:58</p>
                        <p style="margin: 5px 0;"><strong><?php _e('Developed by:', 'articlecraft-ai'); ?></strong> <a href="https://www.wizconsults.com" target="_blank" style="color: #3b82f6; font-weight: 600;">Wiz Consults</a></p>
                        <p style="margin: 5px 0;"><strong><?php _e('Support:', 'articlecraft-ai'); ?></strong> <a href="https://www.wizconsults.com/contact" target="_blank" style="color: #3b82f6;">The Wiz Support</a></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_help_tab == 'usage'): ?>
            <div class="tab-content">
                <h2><?php _e('📝 Complete Usage Guide', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <h3><?php _e('🗺️ Admin Menu Locations', 'articlecraft-ai'); ?></h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <h4 style="color: #3b82f6; margin: 0 0 10px 0;">⚙️ Settings</h4>
                            <p><code>Settings > ArticleCraft AI</code></p>
                            <p style="font-size: 13px; color: #64748b;">Configure API keys, model settings, and custom persona</p>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
                            <h4 style="color: #10b981; margin: 0 0 10px 0;">📊 Logs</h4>
                            <p><code>Tools > ArticleCraft AI Logs</code></p>
                            <p style="font-size: 13px; color: #64748b;">View processing history and token usage</p>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <h4 style="color: #f59e0b; margin: 0 0 10px 0;">✨ Generator</h4>
                            <p><code>Post Editor Sidebar</code></p>
                            <p style="font-size: 13px; color: #64748b;">Main content generation interface</p>
                        </div>
                    </div>
                    
                    <h3><?php _e('📝 How to Use the Meta Box Generator', 'articlecraft-ai'); ?></h3>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h4><?php _e('1. Generate from URL:', 'articlecraft-ai'); ?></h4>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('Enter any URL in the input field', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Choose structure mode (Maintain or Full Revamp)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Click "Generate Article" button', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Wait for AI processing to complete', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Review generated content and apply SEO meta to your plugin', 'articlecraft-ai'); ?></li>
                        </ul>
                        
                        <h4><?php _e('2. Rewrite Current Content:', 'articlecraft-ai'); ?></h4>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('Add content to your editor first', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Choose structure mode (Maintain or Full Revamp)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Click "Rewrite Article" button', 'articlecraft-ai'); ?></li>
                            <li><?php _e('AI will enhance your existing content', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <h3><?php _e('🎭 Custom Persona & Context', 'articlecraft-ai'); ?></h3>
                    <p><?php _e('Create a personalized writing style by answering a few questions about your publication:', 'articlecraft-ai'); ?></p>
                    <ol style="font-size: 15px; line-height: 1.6;">
                        <li><?php _e('Go to Settings > ArticleCraft AI', 'articlecraft-ai'); ?></li>
                        <li><?php _e('Scroll to "Custom Persona & Context" section', 'articlecraft-ai'); ?></li>
                        <li><?php _e('Click "Generate Custom Persona"', 'articlecraft-ai'); ?></li>
                        <li><?php _e('Answer questions about your publication, audience, and style', 'articlecraft-ai'); ?></li>
                        <li><?php _e('Click "Generate Persona" to create your custom instructions', 'articlecraft-ai'); ?></li>
                    </ol>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_help_tab == 'blocks'): ?>
            <div class="tab-content">
                <h2><?php _e('🧱 Gutenberg Blocks Usage Guide', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <h3><?php _e('📋 What are Gutenberg Blocks?', 'articlecraft-ai'); ?></h3>
                    <p><?php _e('Gutenberg blocks are interactive components that can be added directly to your WordPress editor. ArticleCraft AI provides specialized blocks to enhance your content creation workflow within the Gutenberg editor.', 'articlecraft-ai'); ?></p>
                    
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 25px; border-radius: 12px; border-left: 5px solid #3b82f6; margin: 20px 0;">
                        <h4 style="color: #1e40af; margin: 0 0 15px 0;">🚀 ArticleCraft AI Generator Block</h4>
                        <p><strong><?php _e('Purpose:', 'articlecraft-ai'); ?></strong> <?php _e('Interactive content generation directly within the Gutenberg editor without leaving the editing interface.', 'articlecraft-ai'); ?></p>
                        
                        <h5><?php _e('Key Features:', 'articlecraft-ai'); ?></h5>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('URL-based content generation - paste any web URL to create articles', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Current content rewriting with structure options (Maintain/Revamp)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Real-time progress indicator with animated feedback', 'articlecraft-ai'); ?></li>
                            <li><?php _e('SEO meta information display and management', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Direct SEO plugin integration for immediate optimization', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Professional styling that matches your admin theme', 'articlecraft-ai'); ?></li>
                        </ul>
                        
                        <h5><?php _e('When to Use:', 'articlecraft-ai'); ?></h5>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('You prefer working entirely within the Gutenberg editor', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You want to see the generation process inline with your content', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You\'re creating content from multiple sources in one session', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You want to keep the generator accessible throughout editing', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 25px; border-radius: 12px; border-left: 5px solid #10b981; margin: 20px 0;">
                        <h4 style="color: #065f46; margin: 0 0 15px 0;">📝 ArticleCraft AI Generated Content Block</h4>
                        <p><strong><?php _e('Purpose:', 'articlecraft-ai'); ?></strong> <?php _e('Professional display and management of AI-generated content with full metadata tracking.', 'articlecraft-ai'); ?></p>
                        
                        <h5><?php _e('Key Features:', 'articlecraft-ai'); ?></h5>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('Professional content display with ArticleCraft AI styling', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Generation timestamp tracking for content history', 'articlecraft-ai'); ?></li>
                            <li><?php _e('SEO meta information display in organized format', 'articlecraft-ai'); ?></li>
                            <li><?php _e('ArticleCraft AI branding and attribution for transparency', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Enhanced readability with optimized typography', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Mobile-responsive design for all device types', 'articlecraft-ai'); ?></li>
                        </ul>
                        
                        <h5><?php _e('When to Use:', 'articlecraft-ai'); ?></h5>
                        <ul style="line-height: 1.6;">
                            <li><?php _e('You want to showcase AI-generated content with proper attribution', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You need to track when and how content was generated', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You\'re building content archives with generation metadata', 'articlecraft-ai'); ?></li>
                            <li><?php _e('You want professional presentation of AI-enhanced content', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <h3><?php _e('🎯 Step-by-Step: Using the Generator Block', 'articlecraft-ai'); ?></h3>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin: 20px 0;">
                        <h4><?php _e('Step 1: Adding the Block', 'articlecraft-ai'); ?></h4>
                        <ol style="line-height: 1.8; font-size: 15px;">
                            <li><?php _e('Open your post/page in the Gutenberg editor', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Click the "+" (Add Block) button where you want to insert the generator', 'articlecraft-ai'); ?></li>
                            <li><?php _e('In the block search, type "ArticleCraft AI" or look for the ArticleCraft AI category', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Select "ArticleCraft AI Generator" from the available blocks', 'articlecraft-ai'); ?></li>
                            <li><?php _e('The generator block will appear with all controls ready to use', 'articlecraft-ai'); ?></li>
                        </ol>
                        
                        <h4><?php _e('Step 2: Configuring Generation Settings', 'articlecraft-ai'); ?></h4>
                        <ol style="line-height: 1.8; font-size: 15px;">
                            <li><strong><?php _e('For URL-based Generation:', 'articlecraft-ai'); ?></strong>
                                <ul style="margin-top: 8px;">
                                    <li><?php _e('Enter any valid URL in the input field (news articles, blog posts, research papers)', 'articlecraft-ai'); ?></li>
                                    <li><?php _e('The URL must be publicly accessible (no login required)', 'articlecraft-ai'); ?></li>
                                    <li><?php _e('Both HTTP and HTTPS URLs are supported', 'articlecraft-ai'); ?></li>
                                </ul>
                            </li>
                            <li><strong><?php _e('Choose Structure Mode:', 'articlecraft-ai'); ?></strong>
                                <ul style="margin-top: 8px;">
                                    <li><strong><?php _e('Maintain Structure:', 'articlecraft-ai'); ?></strong> <?php _e('Keeps original organization exactly the same - perfect for rewriting while preserving the author\'s intended flow', 'articlecraft-ai'); ?></li>
                                    <li><strong><?php _e('Full Revamp:', 'articlecraft-ai'); ?></strong> <?php _e('Completely restructures content for optimal readability and professional presentation', 'articlecraft-ai'); ?></li>
                                </ul>
                            </li>
                        </ol>
                        
                        <h4><?php _e('Step 3: Content Generation Process', 'articlecraft-ai'); ?></h4>
                        <ol style="line-height: 1.8; font-size: 15px;">
                            <li><?php _e('Click "Generate Article" (for URL) or "Rewrite Current Content" (for existing text)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Watch the animated progress indicator showing the AI processing stages', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Wait for completion - processing typically takes 15-45 seconds depending on content length', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Review the generated content that appears in your editor', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check the automatically generated SEO meta title and description', 'articlecraft-ai'); ?></li>
                        </ol>
                        
                        <h4><?php _e('Step 4: Post-Generation Actions', 'articlecraft-ai'); ?></h4>
                        <ol style="line-height: 1.8; font-size: 15px;">
                            <li><?php _e('Review and edit the generated content as needed', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Click "Apply to SEO Plugin" to automatically set meta data in your SEO plugin', 'articlecraft-ai'); ?></li>
                            <li><?php _e('The system will detect and integrate with: Yoast SEO, RankMath, SEOPress, AIOSEO, SEO Framework, or Squirrly SEO', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Save your post to preserve all changes and generated content', 'articlecraft-ai'); ?></li>
                        </ol>
                    </div>
                    
                    <h3><?php _e('🔧 Block vs. Meta Box: When to Use Which?', 'articlecraft-ai'); ?></h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <h4 style="color: #1e40af; margin: 0 0 15px 0;">🧱 Use Gutenberg Blocks When:</h4>
                            <ul style="line-height: 1.6; font-size: 14px;">
                                <li><?php _e('You prefer the Gutenberg editor', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You want inline content generation', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You\'re creating multiple pieces of content', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You like visual workflow integration', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You want the generator always visible', 'articlecraft-ai'); ?></li>
                            </ul>
                        </div>
                        <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
                            <h4 style="color: #065f46; margin: 0 0 15px 0;">📋 Use Meta Box When:</h4>
                            <ul style="line-height: 1.6; font-size: 14px;">
                                <li><?php _e('You prefer the Classic Editor', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You want sidebar-based controls', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You need quick, one-time generation', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You like traditional WordPress workflows', 'articlecraft-ai'); ?></li>
                                <li><?php _e('You want compact, space-saving interface', 'articlecraft-ai'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_help_tab == 'features'): ?>
            <div class="tab-content">
                <h2><?php _e('🌟 Powerful Features', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin: 25px 0;">
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #3b82f6; border-radius: 8px;">
                            <h4 style="color: #3b82f6; margin: 0 0 12px 0;">🤖 Multiple AI Providers</h4>
                            <p><?php _e('Choose from OpenAI GPT, Anthropic Claude, Google Gemini, and DeepSeek based on your needs and budget.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #10b981; border-radius: 8px;">
                            <h4 style="color: #10b981; margin: 0 0 12px 0;">🎭 Custom Persona Generator</h4>
                            <p><?php _e('AI-powered persona creation that adapts to your publication style, audience, and unique voice.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #f59e0b; border-radius: 8px;">
                            <h4 style="color: #f59e0b; margin: 0 0 12px 0;">📰 Professional Standards</h4>
                            <p><?php _e('Built-in journalism best practices ensure high-quality, engaging, and human-like content.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #ef4444; border-radius: 8px;">
                            <h4 style="color: #ef4444; margin: 0 0 12px 0;">✨ Smart Formatting</h4>
                            <p><?php _e('Automatically highlights key points, statistics, and important information with strategic HTML formatting.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #8b5cf6; border-radius: 8px;">
                            <h4 style="color: #8b5cf6; margin: 0 0 12px 0;">🔗 URL Content Extraction</h4>
                            <p><?php _e('Intelligent web scraping to extract meaningful content from any publicly accessible URL.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #06b6d4; border-radius: 8px;">
                            <h4 style="color: #06b6d4; margin: 0 0 12px 0;">📊 Activity Logging</h4>
                            <p><?php _e('Comprehensive tracking of all AI operations, token usage, and processing times for optimization.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #ec4899; border-radius: 8px;">
                            <h4 style="color: #ec4899; margin: 0 0 12px 0;">🔌 SEO Plugin Integration</h4>
                            <p><?php _e('Automatic integration with Yoast SEO, RankMath, SEOPress, AIOSEO, SEO Framework, and Squirrly SEO.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #84cc16; border-radius: 8px;">
                            <h4 style="color: #84cc16; margin: 0 0 12px 0;">🧱 Gutenberg Blocks</h4>
                            <p><?php _e('Native Gutenberg blocks for seamless content generation directly within the block editor.', 'articlecraft-ai'); ?></p>
                        </div>
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-left: 5px solid #f97316; border-radius: 8px;">
                            <h4 style="color: #f97316; margin: 0 0 12px 0;">🎯 Structure Control</h4>
                            <p><?php _e('Choose between maintaining original structure or full content revamp with quote preservation.', 'articlecraft-ai'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_help_tab == 'troubleshooting'): ?>
            <div class="tab-content">
                <h2><?php _e('🛠️ Troubleshooting Guide', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 20px; margin: 20px 0; border-left: 5px solid #ef4444; border-radius: 8px;">
                        <h4 style="color: #dc2626; margin: 0 0 12px 0;">❓ Cannot find ArticleCraft AI in admin</h4>
                        <p><strong><?php _e('Solution:', 'articlecraft-ai'); ?></strong></p>
                        <ul>
                            <li><?php _e('Check: Settings > ArticleCraft AI for main configuration', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check: Tools > ArticleCraft AI Logs for activity history', 'articlecraft-ai'); ?></li>
                            <li><?php _e('In post editor: Look for "ArticleCraft AI Generator" meta box on sidebar', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Ensure plugin is activated and you have sufficient permissions', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; margin: 20px 0; border-left: 5px solid #f59e0b; border-radius: 8px;">
                        <h4 style="color: #d97706; margin: 0 0 12px 0;">🚫 API Error Messages</h4>
                        <p><strong><?php _e('Solution:', 'articlecraft-ai'); ?></strong></p>
                        <ul>
                            <li><?php _e('Verify your API key is correct in Settings > ArticleCraft AI', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check your API provider account has sufficient credits', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Ensure the selected model is available for your account', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Try reducing the max tokens setting if you get limit errors', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; margin: 20px 0; border-left: 5px solid #0ea5e9; border-radius: 8px;">
                        <h4 style="color: #0c4a6e; margin: 0 0 12px 0;">🔄 Content not updating in editor</h4>
                        <p><strong><?php _e('Solution:', 'articlecraft-ai'); ?></strong></p>
                        <ul>
                            <li><?php _e('Make sure you\'re using the Classic Editor or TinyMCE', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Try refreshing the page and regenerating content', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check browser console for JavaScript errors', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Disable other plugins temporarily to check for conflicts', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 20px; margin: 20px 0; border-left: 5px solid #16a34a; border-radius: 8px;">
                        <h4 style="color: #15803d; margin: 0 0 12px 0;">⚙️ SEO meta not applying to plugin</h4>
                        <p><strong><?php _e('Solution:', 'articlecraft-ai'); ?></strong></p>
                        <ul>
                            <li><?php _e('Ensure you have a supported SEO plugin installed (Yoast, RankMath, etc.)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Save the post before applying SEO meta data', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check if the SEO plugin is properly activated', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Verify you have permission to edit post meta data', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); padding: 20px; margin: 20px 0; border-left: 5px solid #a855f7; border-radius: 8px;">
                        <h4 style="color: #7c3aed; margin: 0 0 12px 0;">🧱 Gutenberg blocks not working</h4>
                        <p><strong><?php _e('Solution:', 'articlecraft-ai'); ?></strong></p>
                        <ul>
                            <li><?php _e('Make sure you\'re using Gutenberg editor (not Classic Editor)', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Check if blocks appear in the "ArticleCraft AI" category', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Clear browser cache and refresh the editor', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Ensure WordPress version is 5.0 or higher', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($active_help_tab == 'api-setup'): ?>
            <div class="tab-content">
                <h2><?php _e('🔑 API Setup Guide', 'articlecraft-ai'); ?></h2>
                <div class="help-section">
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 25px; border-radius: 12px; margin: 20px 0;">
                        <h3><?php _e('✨ OpenAI (Recommended)', 'articlecraft-ai'); ?></h3>
                        <ol style="font-size: 15px; line-height: 1.6;">
                            <li><?php _e('Visit', 'articlecraft-ai'); ?> <a href="https://platform.openai.com/api-keys" target="_blank" style="color: #3b82f6; font-weight: 600;">OpenAI Platform</a></li>
                            <li><?php _e('Create an account or sign in to your existing account', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Navigate to API Keys section and generate a new API key', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Copy the key and paste it in ArticleCraft AI settings', 'articlecraft-ai'); ?></li>
                        </ol>
                        <p><strong><?php _e('Available Models:', 'articlecraft-ai'); ?></strong> GPT-3.5 Turbo, GPT-4, GPT-4 Turbo, GPT-4o, GPT-4o Mini</p>
                        <p><strong><?php _e('Pricing:', 'articlecraft-ai'); ?></strong> <?php _e('Pay per token usage, competitive rates', 'articlecraft-ai'); ?></p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 25px; border-radius: 12px; margin: 20px 0;">
                        <h3><?php _e('🧠 Anthropic Claude', 'articlecraft-ai'); ?></h3>
                        <ol style="font-size: 15px; line-height: 1.6;">
                            <li><?php _e('Visit', 'articlecraft-ai'); ?> <a href="https://console.anthropic.com/" target="_blank" style="color: #d97706; font-weight: 600;">Anthropic Console</a></li>
                            <li><?php _e('Create an account or sign in to your existing account', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Generate a new API key in your account settings', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Copy the key and paste it in ArticleCraft AI settings', 'articlecraft-ai'); ?></li>
                        </ol>
                        <p><strong><?php _e('Available Models:', 'articlecraft-ai'); ?></strong> Claude 3.5 Sonnet, Claude 3.5 Haiku, Claude 3 Opus</p>
                        <p><strong><?php _e('Pricing:', 'articlecraft-ai'); ?></strong> <?php _e('Competitive token-based pricing with high quality output', 'articlecraft-ai'); ?></p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 25px; border-radius: 12px; margin: 20px 0;">
                        <h3><?php _e('💎 Google Gemini', 'articlecraft-ai'); ?></h3>
                        <ol style="font-size: 15px; line-height: 1.6;">
                            <li><?php _e('Visit', 'articlecraft-ai'); ?> <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: #16a34a; font-weight: 600;">Google AI Studio</a></li>
                            <li><?php _e('Create an account or sign in with your Google account', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Generate a new API key in the API Keys section', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Copy the key and paste it in ArticleCraft AI settings', 'articlecraft-ai'); ?></li>
                        </ol>
                        <p><strong><?php _e('Available Models:', 'articlecraft-ai'); ?></strong> Gemini 2.0 Flash (Experimental), Gemini 1.5 Pro, Gemini 1.5 Flash</p>
                        <p><strong><?php _e('Pricing:', 'articlecraft-ai'); ?></strong> <?php _e('Free tier available, very competitive pricing', 'articlecraft-ai'); ?></p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); padding: 25px; border-radius: 12px; margin: 20px 0;">
                        <h3><?php _e('🔮 DeepSeek', 'articlecraft-ai'); ?></h3>
                        <ol style="font-size: 15px; line-height: 1.6;">
                            <li><?php _e('Visit', 'articlecraft-ai'); ?> <a href="https://platform.deepseek.com/api_keys" target="_blank" style="color: #8b5cf6; font-weight: 600;">DeepSeek Platform</a></li>
                            <li><?php _e('Create an account or sign in to your existing account', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Generate a new API key in the API Keys section', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Copy the key and paste it in ArticleCraft AI settings', 'articlecraft-ai'); ?></li>
                        </ol>
                        <p><strong><?php _e('Available Models:', 'articlecraft-ai'); ?></strong> DeepSeek Chat, DeepSeek Reasoner</p>
                        <p><strong><?php _e('Pricing:', 'articlecraft-ai'); ?></strong> <?php _e('Very affordable pricing, excellent value for money', 'articlecraft-ai'); ?></p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h4 style="color: #dc2626; margin: 0 0 12px 0;">⚠️ Important Security Notes</h4>
                        <ul>
                            <li><?php _e('Never share your API keys publicly or in version control', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Monitor your API usage regularly to avoid unexpected charges', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Set usage limits in your API provider dashboard', 'articlecraft-ai'); ?></li>
                            <li><?php _e('Regenerate keys if you suspect they may be compromised', 'articlecraft-ai'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .articlecraft-ai-help .tab-content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            min-height: 600px;
        }
        
        .articlecraft-ai-help .help-section {
            margin-bottom: 40px;
        }
        
        .articlecraft-ai-help .help-section h3 {
            color: #1e293b;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 8px;
            font-size: 20px;
            font-weight: 700;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        
        .articlecraft-ai-help .help-section h4 {
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0 10px 0;
        }
        
        .articlecraft-ai-help .help-section h5 {
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            margin: 15px 0 8px 0;
        }
        
        .articlecraft-ai-help .help-section ul {
            margin: 10px 0 20px 20px;
            line-height: 1.6;
        }
        
        .articlecraft-ai-help .help-section ol {
            margin: 10px 0 20px 20px;
            line-height: 1.6;
        }
        
        .articlecraft-ai-help .help-section p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .articlecraft-ai-help .help-section code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .articlecraft-ai-help .nav-tab-wrapper {
            background: #f8fafc;
            border-radius: 8px 8px 0 0;
            padding: 10px;
            margin: 0;
        }
        
        .articlecraft-ai-help .nav-tab {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            margin-right: 5px;
            padding: 10px 15px;
            border-radius: 6px 6px 0 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .articlecraft-ai-help .nav-tab-active {
            background: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        </style>
        <?php
    }
    
    public function logs_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'articlecraft_ai_logs';
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100");
        
        ?>
        <div class="wrap">
            <h1 style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;">📊</span>
                <?php _e('ArticleCraft AI Logs', 'articlecraft-ai'); ?>
            </h1>
            
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 20px;">
                <table class="wp-list-table widefat fixed striped" style="border-radius: 8px; overflow: hidden;">
                    <thead style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white;">
                        <tr>
                            <th style="color: white; font-weight: 600;"><?php _e('Date', 'articlecraft-ai'); ?></th>
                            <th style="color: white; font-weight: 600;"><?php _e('Post ID', 'articlecraft-ai'); ?></th>
                            <th style="color: white; font-weight: 600;"><?php _e('Action', 'articlecraft-ai'); ?></th>
                            <th style="color: white; font-weight: 600;"><?php _e('Source URL', 'articlecraft-ai'); ?></th>
                            <th style="color: white; font-weight: 600;"><?php _e('Tokens Used', 'articlecraft-ai'); ?></th>
                            <th style="color: white; font-weight: 600;"><?php _e('Processing Time', 'articlecraft-ai'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📝</div>
                                    <?php _e('No logs found. Start generating content to see activity here!', 'articlecraft-ai'); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html(date('M j, Y H:i', strtotime($log->created_at))); ?></td>
                                    <td>
                                        <?php if ($log->post_id): ?>
                                            <a href="<?php echo get_edit_post_link($log->post_id); ?>" style="color: #3b82f6; font-weight: 600;">
                                                #<?php echo esc_html($log->post_id); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #64748b;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="background: #e0f2fe; color: #0c4a6e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $log->action_type))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log->source_url): ?>
                                            <a href="<?php echo esc_url($log->source_url); ?>" target="_blank" style="color: #3b82f6; text-decoration: none;" title="<?php echo esc_attr($log->source_url); ?>">
                                                <?php echo esc_html(wp_trim_words($log->source_url, 6, '...')); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #64748b;">Content Rewrite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="background: #f0fdf4; color: #15803d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <?php echo esc_html(number_format($log->tokens_used)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background: #fffbeb; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <?php echo esc_html(articlecraft_ai_format_processing_time($log->processing_time)); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if (!empty($logs)): ?>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <?php
                            $total_tokens = array_sum(array_column($logs, 'tokens_used'));
                            $avg_processing_time = array_sum(array_column($logs, 'processing_time')) / count($logs);
                            $total_operations = count($logs);
                            $url_operations = count(array_filter($logs, function($log) { return !empty($log->source_url); }));
                            $rewrite_operations = $total_operations - $url_operations;
                            ?>
                            
                            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 15px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #1e40af; margin-bottom: 5px;">
                                    <?php echo number_format($total_operations); ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                                    <?php _e('Total Operations', 'articlecraft-ai'); ?>
                                </div>
                            </div>
                            
                            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 15px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #15803d; margin-bottom: 5px;">
                                    <?php echo number_format($total_tokens); ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                                    <?php _e('Total Tokens Used', 'articlecraft-ai'); ?>
                                </div>
                            </div>
                            
                            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); padding: 15px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #d97706; margin-bottom: 5px;">
                                    <?php echo articlecraft_ai_format_processing_time($avg_processing_time); ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                                    <?php _e('Avg Processing Time', 'articlecraft-ai'); ?>
                                </div>
                            </div>
                            
                            <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); padding: 15px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 14px; font-weight: 700; color: #7c3aed; margin-bottom: 5px;">
                                    <?php echo number_format($url_operations); ?> / <?php echo number_format($rewrite_operations); ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                                    <?php _e('URL / Rewrite', 'articlecraft-ai'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .wp-list-table th,
        .wp-list-table td {
            padding: 12px 8px;
            vertical-align: middle;
        }
        
        .wp-list-table thead th {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
            color: white !important;
            font-weight: 600 !important;
            border-bottom: none !important;
        }
        
        .wp-list-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .wp-list-table tbody tr:hover {
            background: #f1f5f9;
        }
        
        .wp-list-table tbody td {
            border-top: 1px solid #e2e8f0;
        }
        
        .wp-list-table a {
            text-decoration: none;
            font-weight: 600;
        }
        
        .wp-list-table a:hover {
            text-decoration: underline;
        }
        </style>
        <?php
    }
    
    public function admin_init() {
        // This method can be used for additional admin initialization if needed
        // Currently handled by the Settings class
    }
}

// Additional utility functions for the admin class

/**
 * Format bytes to human readable format
 */
function articlecraft_ai_format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Get operation type display name
 */
function articlecraft_ai_get_operation_display_name($operation_type) {
    $display_names = array(
        'generate_from_url' => __('Generate from URL', 'articlecraft-ai'),
        'rewrite_content' => __('Rewrite Content', 'articlecraft-ai'),
        'custom_generation' => __('Custom Generation', 'articlecraft-ai'),
        'block_generation' => __('Block Generation', 'articlecraft-ai')
    );
    
    return $display_names[$operation_type] ?? ucfirst(str_replace('_', ' ', $operation_type));
}

/**
 * Get operation status badge HTML
 */
function articlecraft_ai_get_status_badge($status) {
    $badges = array(
        'completed' => '<span style="background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">✅ Completed</span>',
        'failed' => '<span style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">❌ Failed</span>',
        'processing' => '<span style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">⏳ Processing</span>',
        'pending' => '<span style="background: #e0f2fe; color: #0c4a6e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">⏸️ Pending</span>'
    );
    
    return $badges[$status] ?? '<span style="background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">❓ Unknown</span>';
}

/**
 * Get time ago format
 */
function articlecraft_ai_time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return __('Just now', 'articlecraft-ai');
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'articlecraft-ai'), $minutes);
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'articlecraft-ai'), $hours);
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return sprintf(_n('%d day ago', '%d days ago', $days, 'articlecraft-ai'), $days);
    } else {
        return date('M j, Y', strtotime($datetime));
    }
}

/**
 * Get AI provider icon
 */
function articlecraft_ai_get_provider_icon($provider) {
    $icons = array(
        'openai' => '🤖',
        'claude' => '🧠',
        'gemini' => '💎',
        'deepseek' => '🔮'
    );
    
    return $icons[$provider] ?? '🤖';
}

/**
 * Get model display name
 */
function articlecraft_ai_get_model_display_name($model) {
    $model_names = array(
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-4' => 'GPT-4',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'gpt-4o' => 'GPT-4o',
        'gpt-4o-mini' => 'GPT-4o Mini',
        'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
        'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku',
        'claude-3-opus-20240229' => 'Claude 3 Opus',
        'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
        'deepseek-chat' => 'DeepSeek Chat',
        'deepseek-reasoner' => 'DeepSeek Reasoner'
    );
    
    return $model_names[$model] ?? $model;
}

/**
 * Enhanced logs page with filters and pagination
 */
function articlecraft_ai_render_enhanced_logs() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'articlecraft_ai_logs';
    
    // Handle pagination
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Handle filters
    $filters = array();
    if (!empty($_GET['action_type'])) {
        $filters['action_type'] = sanitize_text_field($_GET['action_type']);
    }
    if (!empty($_GET['date_from'])) {
        $filters['date_from'] = sanitize_text_field($_GET['date_from']) . ' 00:00:00';
    }
    if (!empty($_GET['date_to'])) {
        $filters['date_to'] = sanitize_text_field($_GET['date_to']) . ' 23:59:59';
    }
    
    // Build WHERE clause
    $where_clauses = array('1=1');
    $where_values = array();
    
    if (!empty($filters['action_type'])) {
        $where_clauses[] = 'action_type = %s';
        $where_values[] = $filters['action_type'];
    }
    if (!empty($filters['date_from'])) {
        $where_clauses[] = 'created_at >= %s';
        $where_values[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $where_clauses[] = 'created_at <= %s';
        $where_values[] = $filters['date_to'];
    }
    
    $where_sql = implode(' AND ', $where_clauses);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";
    if (!empty($where_values)) {
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
    } else {
        $total_items = $wpdb->get_var($count_sql);
    }
    
    // Get logs
    $logs_sql = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $final_values = array_merge($where_values, array($per_page, $offset));
    $logs = $wpdb->get_results($wpdb->prepare($logs_sql, $final_values));
    
    // Calculate pagination
    $total_pages = ceil($total_items / $per_page);
    
    return array(
        'logs' => $logs,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'per_page' => $per_page
    );
}

/**
 * Export logs to CSV
 */
function articlecraft_ai_export_logs_csv() {
    global $wpdb;
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $table_name = $wpdb->prefix . 'articlecraft_ai_logs';
    $logs = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC");
    
    $filename = 'articlecraft-ai-logs-' . date('Y-m-d-H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, array(
        'ID',
        'Post ID',
        'Action Type',
        'Source URL',
        'Input Content (Preview)',
        'Generated Content (Preview)',
        'Meta Title',
        'Meta Description',
        'Tokens Used',
        'Processing Time (seconds)',
        'Status',
        'Error Message',
        'Created At',
        'Updated At'
    ));
    
    // CSV data
    foreach ($logs as $log) {
        fputcsv($output, array(
            $log->id,
            $log->post_id,
            $log->action_type,
            $log->source_url,
            wp_trim_words($log->input_content, 20),
            wp_trim_words($log->generated_content, 20),
            $log->meta_title,
            $log->meta_description,
            $log->tokens_used,
            $log->processing_time,
            $log->status ?? 'completed',
            $log->error_message ?? '',
            $log->created_at,
            $log->updated_at ?? $log->created_at
        ));
    }
    
    fclose($output);
    exit;
}

// Add export action handler
add_action('wp_ajax_articlecraft_ai_export_logs', 'articlecraft_ai_export_logs_csv');

/**
 * AJAX handler for clearing old logs
 */
function articlecraft_ai_clear_old_logs() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'articlecraft_ai_admin_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'articlecraft-ai')));
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'articlecraft-ai')));
    }
    
    $days = intval($_POST['days'] ?? 30);
    if ($days < 1) {
        $days = 30;
    }
    
    $database = new ArticleCraftAI_Database();
    $deleted_count = $database->delete_old_logs($days);
    
    if ($deleted_count !== false) {
        wp_send_json_success(array(
            'message' => sprintf(__('Successfully deleted %d old log entries.', 'articlecraft-ai'), $deleted_count),
            'deleted_count' => $deleted_count
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to delete old logs.', 'articlecraft-ai')));
    }
}

add_action('wp_ajax_articlecraft_ai_clear_old_logs', 'articlecraft_ai_clear_old_logs');

?>