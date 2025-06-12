<?php
/**
 * Settings for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_articlecraft_ai_generate_persona', array($this, 'handle_generate_persona'));
    }
    
    public function init_settings() {
        register_setting('articlecraft_ai_settings', 'articlecraft_ai_settings', array($this, 'sanitize_settings'));
        
        add_settings_section(
            'articlecraft_ai_api_section',
            __('API Configuration', 'articlecraft-ai'),
            array($this, 'api_section_callback'),
            'articlecraft_ai_settings'
        );
        
        add_settings_field(
            'api_provider',
            __('AI Provider', 'articlecraft-ai'),
            array($this, 'api_provider_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_api_section'
        );
        
        add_settings_field(
            'openai_api_key',
            __('OpenAI API Key', 'articlecraft-ai'),
            array($this, 'openai_api_key_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_api_section'
        );
        
        add_settings_field(
            'claude_api_key',
            __('Claude API Key', 'articlecraft-ai'),
            array($this, 'claude_api_key_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_api_section'
        );
        
        add_settings_field(
            'gemini_api_key',
            __('Gemini API Key', 'articlecraft-ai'),
            array($this, 'gemini_api_key_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_api_section'
        );
        
        add_settings_field(
            'deepseek_api_key',
            __('DeepSeek API Key', 'articlecraft-ai'),
            array($this, 'deepseek_api_key_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_api_section'
        );
        
        add_settings_section(
            'articlecraft_ai_model_section',
            __('Model Configuration', 'articlecraft-ai'),
            array($this, 'model_section_callback'),
            'articlecraft_ai_settings'
        );
        
        add_settings_field(
            'model',
            __('Model', 'articlecraft-ai'),
            array($this, 'model_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_model_section'
        );
        
        add_settings_field(
            'max_tokens',
            __('Max Tokens', 'articlecraft-ai'),
            array($this, 'max_tokens_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_model_section'
        );
        
        add_settings_field(
            'temperature',
            __('Temperature', 'articlecraft-ai'),
            array($this, 'temperature_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_model_section'
        );
        
        add_settings_section(
            'articlecraft_ai_persona_section',
            __('Custom Persona & Context', 'articlecraft-ai'),
            array($this, 'persona_section_callback'),
            'articlecraft_ai_settings'
        );
        
        add_settings_field(
            'custom_persona',
            __('Custom Persona', 'articlecraft-ai'),
            array($this, 'custom_persona_callback'),
            'articlecraft_ai_settings',
            'articlecraft_ai_persona_section'
        );
    }
    
    public function api_section_callback() {
        echo '<p>' . __('Configure your AI provider API keys. You only need to configure the provider you plan to use.', 'articlecraft-ai') . '</p>';
    }
    
    public function model_section_callback() {
        echo '<p>' . __('Configure the AI model parameters for content generation.', 'articlecraft-ai') . '</p>';
    }
    
    public function persona_section_callback() {
        echo '<p>' . __('Customize the writing persona and context to match your publication style and audience. Leave empty to use the default general persona.', 'articlecraft-ai') . '</p>';
    }
    
    public function api_provider_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['api_provider'] ?? 'openai';
        ?>
        <select name="articlecraft_ai_settings[api_provider]" id="api_provider">
            <option value="openai" <?php selected($value, 'openai'); ?>>OpenAI (GPT)</option>
            <option value="claude" <?php selected($value, 'claude'); ?>>Anthropic Claude</option>
            <option value="gemini" <?php selected($value, 'gemini'); ?>>Google Gemini</option>
            <option value="deepseek" <?php selected($value, 'deepseek'); ?>>DeepSeek</option>
        </select>
        <p class="description"><?php _e('Select your preferred AI provider.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function openai_api_key_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['openai_api_key'] ?? '';
        ?>
        <input type="password" name="articlecraft_ai_settings[openai_api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function claude_api_key_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['claude_api_key'] ?? '';
        ?>
        <input type="password" name="articlecraft_ai_settings[claude_api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('Get your API key from <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function gemini_api_key_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['gemini_api_key'] ?? '';
        ?>
        <input type="password" name="articlecraft_ai_settings[gemini_api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('Get your API key from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function deepseek_api_key_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['deepseek_api_key'] ?? '';
        ?>
        <input type="password" name="articlecraft_ai_settings[deepseek_api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('Get your API key from <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek Platform</a>.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function model_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['model'] ?? 'gpt-3.5-turbo';
        ?>
        <select name="articlecraft_ai_settings[model]" id="model">
            <optgroup label="OpenAI">
                <option value="gpt-3.5-turbo" <?php selected($value, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                <option value="gpt-4" <?php selected($value, 'gpt-4'); ?>>GPT-4</option>
                <option value="gpt-4-turbo" <?php selected($value, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                <option value="gpt-4o" <?php selected($value, 'gpt-4o'); ?>>GPT-4o</option>
                <option value="gpt-4o-mini" <?php selected($value, 'gpt-4o-mini'); ?>>GPT-4o Mini</option>
            </optgroup>
            <optgroup label="Claude">
                <option value="claude-3-5-sonnet-20241022" <?php selected($value, 'claude-3-5-sonnet-20241022'); ?>>Claude 3.5 Sonnet</option>
                <option value="claude-3-5-haiku-20241022" <?php selected($value, 'claude-3-5-haiku-20241022'); ?>>Claude 3.5 Haiku</option>
                <option value="claude-3-opus-20240229" <?php selected($value, 'claude-3-opus-20240229'); ?>>Claude 3 Opus</option>
            </optgroup>
            <optgroup label="Gemini">
                <option value="gemini-2.0-flash-exp" <?php selected($value, 'gemini-2.0-flash-exp'); ?>>Gemini 2.0 Flash (Experimental)</option>
                <option value="gemini-1.5-pro" <?php selected($value, 'gemini-1.5-pro'); ?>>Gemini 1.5 Pro</option>
                <option value="gemini-1.5-flash" <?php selected($value, 'gemini-1.5-flash'); ?>>Gemini 1.5 Flash</option>
                <!-- REMOVED: gemini-pro (obsolete model) -->
            </optgroup>
            <optgroup label="DeepSeek">
                <option value="deepseek-chat" <?php selected($value, 'deepseek-chat'); ?>>DeepSeek Chat</option>
                <option value="deepseek-reasoner" <?php selected($value, 'deepseek-reasoner'); ?>>DeepSeek Reasoner</option>
            </optgroup>
        </select>
        <p class="description"><?php _e('Select the AI model to use for content generation.', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function max_tokens_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['max_tokens'] ?? 2000;
        ?>
        <input type="number" name="articlecraft_ai_settings[max_tokens]" value="<?php echo esc_attr($value); ?>" min="100" max="8000" />
        <p class="description"><?php _e('Maximum number of tokens to generate (100-8000).', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function temperature_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['temperature'] ?? 0.7;
        ?>
        <input type="number" name="articlecraft_ai_settings[temperature]" value="<?php echo esc_attr($value); ?>" min="0" max="1" step="0.1" />
        <p class="description"><?php _e('Controls randomness in the output (0.0 = deterministic, 1.0 = very creative).', 'articlecraft-ai'); ?></p>
        <?php
    }
    
    public function custom_persona_callback() {
        $settings = get_option('articlecraft_ai_settings', array());
        $value = $settings['custom_persona'] ?? '';
        ?>
        <div class="persona-generator">
            <div class="persona-actions">
                <button type="button" id="generate-persona-btn" class="button button-secondary">
                    <?php _e('Generate Custom Persona', 'articlecraft-ai'); ?>
                </button>
                <button type="button" id="clear-persona-btn" class="button button-link-delete">
                    <?php _e('Clear & Use Default', 'articlecraft-ai'); ?>
                </button>
            </div>
            
            <div id="persona-generator-form" style="display: none;">
                <h4><?php _e('Let\'s create your custom persona:', 'articlecraft-ai'); ?></h4>
                <div id="persona-questions"></div>
                <div class="persona-form-actions">
                    <button type="button" id="generate-persona-submit" class="button button-primary">
                        <?php _e('Generate Persona', 'articlecraft-ai'); ?>
                    </button>
                    <button type="button" id="cancel-persona-generation" class="button button-secondary">
                        <?php _e('Cancel', 'articlecraft-ai'); ?>
                    </button>
                </div>
            </div>
            
            <textarea name="articlecraft_ai_settings[custom_persona]" id="custom_persona" rows="10" cols="50" class="large-text"><?php echo esc_textarea($value); ?></textarea>
            <p class="description">
                <?php _e('Custom persona and context for your content generation. This will override the default persona. Leave empty to use the default general-purpose persona.', 'articlecraft-ai'); ?>
            </p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-persona-btn').click(function() {
                loadPersonaQuestions();
                $('#persona-generator-form').slideDown();
                $(this).prop('disabled', true);
            });
            
            $('#cancel-persona-generation').click(function() {
                $('#persona-generator-form').slideUp();
                $('#generate-persona-btn').prop('disabled', false);
            });
            
            $('#clear-persona-btn').click(function() {
                if (confirm('<?php _e('Are you sure you want to clear the custom persona and use the default one?', 'articlecraft-ai'); ?>')) {
                    $('#custom_persona').val('');
                }
            });
            
            $('#generate-persona-submit').click(function() {
                generatePersonaFromResponses();
            });
            
            function loadPersonaQuestions() {
                const questions = <?php echo json_encode((new ArticleCraftAI_AIService())->generate_persona_questions()); ?>;
                let html = '';
                
                $.each(questions, function(key, question) {
                    html += '<div class="persona-question">';
                    html += '<label><strong>' + question.question + '</strong></label>';
                    
                    if (question.type === 'select') {
                        html += '<select id="persona_' + key + '" class="regular-text">';
                        $.each(question.options, function(optKey, optValue) {
                            html += '<option value="' + optKey + '">' + optValue + '</option>';
                        });
                        html += '</select>';
                    } else if (question.type === 'textarea') {
                        html += '<textarea id="persona_' + key + '" class="regular-text" rows="3" placeholder="' + (question.placeholder || '') + '"></textarea>';
                    } else {
                        html += '<input type="text" id="persona_' + key + '" class="regular-text" placeholder="' + (question.placeholder || '') + '" />';
                    }
                    
                    html += '</div>';
                });
                
                $('#persona-questions').html(html);
            }
            
            function generatePersonaFromResponses() {
                const responses = {};
                $('#persona-questions input, #persona-questions select, #persona-questions textarea').each(function() {
                    const key = $(this).attr('id').replace('persona_', '');
                    responses[key] = $(this).val();
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'articlecraft_ai_generate_persona',
                        responses: responses,
                        nonce: '<?php echo wp_create_nonce('articlecraft_ai_persona_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#custom_persona').val(response.data.persona);
                            $('#persona-generator-form').slideUp();
                            $('#generate-persona-btn').prop('disabled', false);
                            alert('<?php _e('Custom persona generated successfully!', 'articlecraft-ai'); ?>');
                        } else {
                            alert('<?php _e('Error generating persona. Please try again.', 'articlecraft-ai'); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e('Error generating persona. Please try again.', 'articlecraft-ai'); ?>');
                    }
                });
            }
        });
        </script>
        
        <style>
        .persona-generator {
            margin-top: 10px;
        }
        .persona-actions {
            margin-bottom: 15px;
        }
        .persona-actions .button {
            margin-right: 10px;
        }
        #persona-generator-form {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .persona-question {
            margin-bottom: 15px;
        }
        .persona-question label {
            display: block;
            margin-bottom: 5px;
        }
        .persona-question input,
        .persona-question select,
        .persona-question textarea {
            width: 100%;
            max-width: 500px;
        }
        .persona-form-actions {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .persona-form-actions .button {
            margin-right: 10px;
        }
        </style>
        <?php
    }
    
    public function handle_generate_persona() {
        if (!wp_verify_nonce($_POST['nonce'], 'articlecraft_ai_persona_nonce')) {
            wp_die(__('Security check failed', 'articlecraft-ai'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'articlecraft-ai'));
        }
        
        try {
            $responses = $_POST['responses'] ?? array();
            $ai_service = new ArticleCraftAI_AIService();
            $persona = $ai_service->generate_persona_from_responses($responses);
            
            wp_send_json_success(array('persona' => $persona));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['api_provider'])) {
            $sanitized['api_provider'] = sanitize_text_field($input['api_provider']);
        }
        
        if (isset($input['openai_api_key'])) {
            $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key']);
        }
        
        if (isset($input['claude_api_key'])) {
            $sanitized['claude_api_key'] = sanitize_text_field($input['claude_api_key']);
        }
        
        if (isset($input['gemini_api_key'])) {
            $sanitized['gemini_api_key'] = sanitize_text_field($input['gemini_api_key']);
        }
        
        if (isset($input['deepseek_api_key'])) {
            $sanitized['deepseek_api_key'] = sanitize_text_field($input['deepseek_api_key']);
        }
        
        if (isset($input['model'])) {
            $sanitized['model'] = sanitize_text_field($input['model']);
        }
        
        if (isset($input['max_tokens'])) {
            $sanitized['max_tokens'] = intval($input['max_tokens']);
            if ($sanitized['max_tokens'] < 100) $sanitized['max_tokens'] = 100;
            if ($sanitized['max_tokens'] > 8000) $sanitized['max_tokens'] = 8000;
        }
        
        if (isset($input['temperature'])) {
            $sanitized['temperature'] = floatval($input['temperature']);
            if ($sanitized['temperature'] < 0) $sanitized['temperature'] = 0;
            if ($sanitized['temperature'] > 1) $sanitized['temperature'] = 1;
        }
        
        if (isset($input['custom_persona'])) {
            $sanitized['custom_persona'] = wp_kses_post($input['custom_persona']);
        }
        
        return $sanitized;
    }
}