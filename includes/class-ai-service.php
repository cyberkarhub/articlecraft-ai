<?php
/**
 * AI Service handler for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_AIService {
    
    private $settings;
    private $custom_instructions;
    
    public function __construct() {
        $this->settings = get_option('articlecraft_ai_settings', array());
        $this->custom_instructions = $this->get_custom_instructions();
    }
    
    private function get_custom_instructions() {
        $persona_context = $this->get_persona_context();
        
        return $persona_context . "\n\n" . $this->get_fixed_instructions();
    }
    
    private function get_fixed_instructions() {
        return "IMPORTANT: Output ONLY HTML markup, NO MARKDOWN. Use proper HTML tags like <h2>, <h3>, <p>, <strong>, <em>, <ul>, <li>, etc.

Output Format & Style:
Article Length equals to Reading time 5 min max.
Structure: Use clear, concise section headings where appropriate to break up the text and improve readability.
Opening Hook: Craft a strong, engaging opening paragraph (the \"lede\") that immediately grabs the reader's attention and clearly introduces the article's core subject or question, avoiding generic introductions.
Concluding Thought: End the article with a concise, impactful closing. This could be a forward-looking statement, a thought-provoking question related to the topic, or a final key takeaway, rather than a simple summary repeating points already made.
Varied Paragraph Length: In addition to varied sentence structures, also vary paragraph lengths. Utilize shorter paragraphs (even a single sentence) for emphasis or to break up longer passages, enhancing readability and flow.
Title: Craft a compelling and intriguing title that grabs attention and hints at the article's core value. Avoid generic clickbait, hyperbole, or misleading phrasing. Titles MUST be a single phrase only. ABSOLUTELY NO colons or any other punctuation that divides them into two parts.
Tone: Professional, informative, and engaging. Maintain journalistic integrity: focus on facts, provide balanced perspectives, and avoid hype or overly strong opinions unless clearly framed as analysis.
Language: Use clear, precise language. Explain technical concepts simply if they are necessary. Avoid unnecessary jargon. Prioritize accessibility for a broad, intelligent audience.

Critical Constraints - Avoiding AI Tropes & Sounding Human:
Avoid using Em Dash.
No Exaggeration: Ground your writing in facts. Avoid sensationalism or overly dramatic narrative storytelling.
Show, Don't Tell: Where appropriate and while adhering to 'No Exaggeration,' illustrate points with specific (even if hypothetical and clearly stated as such) examples, brief relevant anecdotes, or well-chosen data points to make the content more tangible and engaging. This helps ground the analysis in concrete details.
Prioritize Active Voice: Generally, favor active voice over passive voice for more direct, concise, and engaging prose. Use passive voice selectively and purposefully.
Avoid AI Clichés: Actively steer clear of common generative AI phrases and sentence structures. Examples to AVOID include:
\"In today's fast-paced world...\", \"In the digital age...\", \"In an increasingly digital world...\", \"It is important to note that...\", \"It's crucial to note...\", \"It should be noted that...\", \"It goes without saying that...\", \"Needless to say...\", \"Furthermore...\", \"Moreover...\", \"In addition...\", \"First and foremost...\", \"As mentioned earlier...\", \"When it comes to...\", \"The fact of the matter is...\", \"It is interesting to note that...\",
\"It seems that...\", \"It could be argued that...\", \"One might say that...\", \"There is a possibility that...\", \"This may suggest that...\", \"Can be seen as...\", \"Plays a crucial role in...\", \"Serves as a testament to...\",
\"Unlock the potential of...\", \"Dive deep into...\", \"Taking a deep dive...\", \"Game-changer\", \"Revolutionize\", \"Revolutionary\", \"The landscape of...\", \"Navigating\", \"Seamless integration\", \"Paradigm shift\", \"Synergy\", \"Synergistic\", \"Robust solution\", \"State-of-the-art\", \"Next-generation\", \"Cutting-edge\", \"Empower\", \"Empowering\", \"Leverage\" (as a verb, e.g., \"leverage technology\"),
\"This isn't just about X, it's about Y...\", \"Think of X as Y...\", \"In essence...\", \"Essentially...\", \"At its core...\", \"Basically...\", \"Fundamentally...\", \"To put it simply...\", \"In other words...\",
\"In conclusion...\", \"To sum up...\", \"In summary...\", \"All in all...\", \"In the final analysis...\", \"Looking ahead...\", \"The future is bright for...\", \"Not only X but Y\",
Excessive use of rhetorical questions, overly formal tone when conversational is better, stating the obvious, \"It's a win-win situation.\", \"At the end of the day...\", \"Moving forward...\", \"Key takeaway(s)\" (as a crutch in main body), \"It is clear that...\", \"Clearly...\", \"The world of...\", \"Pave the way for...\", \"Stay ahead of the curve...\", \"In the realm of...\".
Use fresh, specific, and varied language.
Natural Variability: Employ varied sentence lengths and structures. Don't fall into repetitive patterns. Write like a human, with natural flow and cadence.
Authenticity Goal: The primary goal is to produce text indistinguishable from that written by a skilled human journalist. Focus on originality, specific details, and a natural writing voice. This focus on authentic writing quality is the key to passing AI detection.

IMPORTANT HTML FORMATTING REQUIREMENTS:
- Use <h2> and <h3> tags for section headings to break up the text
- Use <p> tags for paragraphs
- Use <strong> tags to highlight key points, statistics, important concepts, and significant findings
- Use <em> tags for emphasis when appropriate
- Use <ul> and <li> for lists when appropriate
- NO MARKDOWN formatting - only HTML tags

QUOTE PRESERVATION REQUIREMENT:
- ALWAYS preserve and maintain any direct quotes, citations, or quoted statements from the original text
- Present quotes in their original form using proper HTML quote formatting: <blockquote> for longer quotes, quotation marks for shorter ones
- Ensure quotes are accurately attributed to their original sources
- Do not modify, paraphrase, or alter the content of any quotes

At the end of the article generation suggest a meta title (under 50 characters) and a meta description (concise, compelling, and ideally between 120-155 characters, accurately reflecting the article's content).";
    }
    
    private function get_persona_context() {
        $custom_persona = $this->settings['custom_persona'] ?? '';
        
        if (!empty($custom_persona)) {
            return "Persona & Context:\n" . $custom_persona;
        }
        
        // Default persona if no custom one is set
        return "Persona & Context:
You are an experienced content writer and journalist with expertise in creating engaging, informative articles across various topics and industries. You write for a diverse, intelligent audience that appreciates well-researched content with clear explanations and practical insights. Your writing style adapts to the subject matter while maintaining professional standards and accessibility.";
    }
    
    public function generate_persona_questions() {
        return array(
            'publication_name' => array(
                'question' => __('What is the name of your publication or website?', 'articlecraft-ai'),
                'type' => 'text',
                'placeholder' => __('e.g., TechBlog, Business Insights, etc.', 'articlecraft-ai')
            ),
            'target_audience' => array(
                'question' => __('Who is your target audience?', 'articlecraft-ai'),
                'type' => 'textarea',
                'placeholder' => __('Describe your readers (e.g., tech professionals, small business owners, general consumers)', 'articlecraft-ai')
            ),
            'industry_focus' => array(
                'question' => __('What industry or topics do you primarily cover?', 'articlecraft-ai'),
                'type' => 'textarea',
                'placeholder' => __('e.g., Technology, Business, Health, Finance, etc.', 'articlecraft-ai')
            ),
            'writing_style' => array(
                'question' => __('What writing style best describes your publication?', 'articlecraft-ai'),
                'type' => 'select',
                'options' => array(
                    'professional' => __('Professional and formal', 'articlecraft-ai'),
                    'conversational' => __('Conversational and friendly', 'articlecraft-ai'),
                    'authoritative' => __('Authoritative and expert-focused', 'articlecraft-ai'),
                    'casual' => __('Casual and approachable', 'articlecraft-ai'),
                    'analytical' => __('Analytical and data-driven', 'articlecraft-ai')
                )
            ),
            'content_depth' => array(
                'question' => __('What level of technical detail do your readers prefer?', 'articlecraft-ai'),
                'type' => 'select',
                'options' => array(
                    'beginner' => __('Beginner-friendly with simple explanations', 'articlecraft-ai'),
                    'intermediate' => __('Intermediate with moderate technical detail', 'articlecraft-ai'),
                    'advanced' => __('Advanced with comprehensive technical depth', 'articlecraft-ai'),
                    'mixed' => __('Mixed - adapt based on topic complexity', 'articlecraft-ai')
                )
            ),
            'unique_angle' => array(
                'question' => __('What makes your publication unique or different from others?', 'articlecraft-ai'),
                'type' => 'textarea',
                'placeholder' => __('Your unique perspective, expertise, or approach', 'articlecraft-ai')
            )
        );
    }
    
    public function generate_persona_from_responses($responses) {
        $publication_name = $responses['publication_name'] ?? 'your publication';
        $target_audience = $responses['target_audience'] ?? 'a diverse, intelligent audience';
        $industry_focus = $responses['industry_focus'] ?? 'various topics and industries';
        $writing_style = $responses['writing_style'] ?? 'professional';
        $content_depth = $responses['content_depth'] ?? 'intermediate';
        $unique_angle = $responses['unique_angle'] ?? '';
        
        $style_descriptions = array(
            'professional' => 'maintaining a professional, authoritative tone',
            'conversational' => 'using a conversational, friendly approach that connects with readers',
            'authoritative' => 'establishing authority through expert insights and comprehensive analysis',
            'casual' => 'keeping the tone casual and approachable for easy readability',
            'analytical' => 'focusing on data-driven analysis and evidence-based conclusions'
        );
        
        $depth_descriptions = array(
            'beginner' => 'Explain technical concepts in simple, accessible terms suitable for beginners.',
            'intermediate' => 'Provide moderate technical detail while keeping content accessible to most readers.',
            'advanced' => 'Include comprehensive technical depth for expert-level audiences.',
            'mixed' => 'Adapt the technical complexity based on the topic and context.'
        );
        
        $persona = "You are an experienced content writer and journalist writing for \"{$publication_name},\" ";
        $persona .= "a publication focused on {$industry_focus}. ";
        $persona .= "Your audience consists of {$target_audience}, and you write with ";
        $persona .= $style_descriptions[$writing_style] ?? $style_descriptions['professional'];
        $persona .= ". ";
        $persona .= $depth_descriptions[$content_depth] ?? $depth_descriptions['intermediate'];
        
        if (!empty($unique_angle)) {
            $persona .= " Your unique perspective is: {$unique_angle}.";
        }
        
        $persona .= " You value accuracy, engaging storytelling, and providing practical value to your readers.";
        
        return $persona;
    }
    
    public function generate_from_url($url, $structure_mode = 'maintain') {
        $start_time = microtime(true);
        
        try {
            // Scrape URL content
            $scraper = new ArticleCraftAI_URLScraper();
            $scraped_content = $scraper->scrape_url($url);
            
            if (!$scraped_content) {
                throw new Exception(__('Unable to scrape content from the provided URL.', 'articlecraft-ai'));
            }
            
            // Generate article using AI
            $prompt = $this->build_url_prompt($scraped_content, $url, $structure_mode);
            $result = $this->call_ai_api($prompt);
            
            // Parse the result
            $parsed_result = $this->parse_ai_response($result);
            
            // Log the operation
            $this->log_operation('generate_from_url', $url, $scraped_content, $result, $parsed_result, microtime(true) - $start_time);
            
            return $parsed_result;
            
        } catch (Exception $e) {
            error_log('ArticleCraft AI Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // Enhanced to auto-detect and preserve metaphors and special terms
    public function rewrite_content($content, $structure_mode = 'maintain', $keywords_to_preserve = array()) {
        $start_time = microtime(true);
        
        try {
            // AUTO-DETECT special terms, metaphors, and literary devices
            $auto_detected_terms = $this->auto_detect_special_terms($content);
            
            // Merge auto-detected terms with manually provided keywords
            $all_keywords = array_merge($keywords_to_preserve, $auto_detected_terms);
            
            // Remove duplicates and empty values
            $all_keywords = array_unique(array_filter($all_keywords));
            
            // Build prompt based on structure mode
            $prompt = $this->build_rewrite_prompt($content, $structure_mode, $all_keywords);
            $result = $this->call_ai_api($prompt);
            
            $parsed_result = $this->parse_ai_response($result);
            
            // CLEAN content to ensure no meta bleeding
            $parsed_result['content'] = $this->clean_content($parsed_result['content']);
            
            $this->log_operation('rewrite_content', null, $content, $result, $parsed_result, microtime(true) - $start_time);
            
            return $parsed_result;
            
        } catch (Exception $e) {
            error_log('ArticleCraft AI Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // NEW: Clean content to remove any meta data that bleeds through
    private function clean_content($content) {
        // Remove any meta title/description that appears in content
        $content = preg_replace('/META[-_]TITLE:\s*[^\n\r]*[\n\r]*/i', '', $content);
        $content = preg_replace('/META[-_]DESCRIPTION:\s*[^\n\r]*[\n\r]*/i', '', $content);
        
        // Remove any stray meta formatting
        $content = preg_replace('/META[-_][A-Z_]*:\s*[^\n\r]*[\n\r]*/i', '', $content);
        
        // Clean up extra whitespace
        $content = trim($content);
        
        return $content;
    }
    
    // Auto-detect metaphors, literary devices, and special terms
    private function auto_detect_special_terms($content) {
        $special_terms = array();
        
        // Remove HTML tags for analysis
        $clean_content = strip_tags($content);
        
        // 1. SPECIFIC METAPHORICAL WORDS
        $metaphorical_words = array(
            'echo', 'mirror', 'reflection', 'shadow', 'light', 'darkness', 'bridge', 'path', 'journey', 
            'river', 'stream', 'ocean', 'wave', 'storm', 'calm', 'fire', 'flame', 'spark', 'ember',
            'seed', 'root', 'branch', 'fruit', 'bloom', 'garden', 'desert', 'mountain', 'valley',
            'door', 'window', 'wall', 'foundation', 'cornerstone', 'pillar', 'anchor', 'compass',
            'thread', 'tapestry', 'fabric', 'weave', 'knot', 'chain', 'link', 'bond', 'web',
            'dance', 'rhythm', 'melody', 'harmony', 'symphony', 'note', 'chord', 'silence'
        );
        
        foreach ($metaphorical_words as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $clean_content)) {
                $special_terms[] = $word;
            }
        }
        
        // 2. POETIC AND SPIRITUAL TERMS
        $poetic_terms = array(
            'soul', 'spirit', 'essence', 'truth', 'wisdom', 'grace', 'divine', 'sacred', 'holy',
            'eternal', 'infinite', 'transcendent', 'enlightenment', 'awakening', 'consciousness',
            'meditation', 'prayer', 'blessing', 'miracle', 'faith', 'hope', 'love', 'peace',
            'harmony', 'balance', 'unity', 'oneness', 'wholeness', 'completeness', 'perfection',
            'beauty', 'sublime', 'profound', 'mystical', 'mysterious'
        );
        
        foreach ($poetic_terms as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $clean_content)) {
                $special_terms[] = $term;
            }
        }
        
        // 3. QUOTED PHRASES
        if (preg_match_all('/"([^"]+)"/i', $clean_content, $matches)) {
            foreach ($matches[1] as $quote) {
                if (strlen(trim($quote)) > 3) {
                    $special_terms[] = trim($quote);
                }
            }
        }
        
        return array_unique($special_terms);
    }
    
    private function build_url_prompt($scraped_content, $url, $structure_mode) {
        $structure_instruction = $this->get_structure_instruction($structure_mode);
        
        return $this->custom_instructions . "\n\n" .
               $structure_instruction . "\n\n" .
               "Based on the content from this URL: {$url}\n\n" .
               "Original Content:\n{$scraped_content}\n\n" .
               "Please create a new article following the custom instructions above. " .
               "Rewrite and transform this content into an engaging, professional article that follows " .
               "all the specified guidelines. Do not simply copy or paraphrase - create original content " .
               "inspired by the source material.\n\n" .
               "CRITICAL: Output ONLY HTML markup. Do NOT include the title in the content body.\n\n" .
               "REMEMBER: Preserve all quotes exactly as they appear in the original content using proper HTML formatting.\n\n" .
               "Format your response as:\n" .
               "TITLE: [Your article title]\n" .
               "CONTENT: [Your article content using HTML tags - NO title repetition]\n" .
               "META_TITLE: [Meta title under 50 characters]\n" .
               "META_DESCRIPTION: [Meta description 120-155 characters]";
    }

    // SIMPLE LOGIC: Two completely different approaches
    private function build_rewrite_prompt($content, $structure_mode, $keywords_to_preserve) {
        
        if ($structure_mode === 'maintain') {
            // MAINTAIN: Rewrite content while preserving structure
            return $this->build_maintain_prompt($content, $keywords_to_preserve);
        } else {
            // REVAMP: Total rewrite preserving spirit and keywords
            return $this->build_revamp_prompt($content, $keywords_to_preserve);
        }
    }
    
    // MAINTAIN MODE: Rewrite content while preserving structure
    private function build_maintain_prompt($content, $keywords_to_preserve) {
        
        $rules_string = "KEYWORD PRESERVATION RULES:\n";
        
        if (!empty($keywords_to_preserve)) {
            foreach ($keywords_to_preserve as $keyword) {
                $keyword_safe = htmlspecialchars(trim($keyword), ENT_QUOTES, 'UTF-8');
                if (!empty($keyword_safe)) {
                    $rules_string .= "- PRESERVE EXACTLY: \"{$keyword_safe}\" - Keep this exact word/phrase in the rewritten content.\n";
                }
            }
        } else {
            $rules_string .= "- Preserve any metaphors, spiritual terms, and unique expressions found in the content.\n";
        }
        
        return "MAINTAIN STRUCTURE MODE:\n\n" .
               "TASK: Rewrite the content below to improve clarity, flow, and language while keeping the EXACT same structure.\n\n" .
               "STRUCTURE REQUIREMENTS:\n" .
               "- If the original is one paragraph, keep it as one paragraph\n" .
               "- If the original has multiple paragraphs, maintain the same paragraph breaks\n" .
               "- If the original has headings, keep the same headings in the same positions\n" .
               "- Do NOT add new headings, sections, or structural elements\n" .
               "- Do NOT reorganize or reorder content\n" .
               "- Focus ONLY on improving word choice, sentence flow, and clarity\n\n" .
               $rules_string . "\n" .
               "Original Content:\n{$content}\n\n" .
               "CRITICAL CONTENT REQUIREMENTS:\n" .
               "- Output clean HTML content ONLY\n" .
               "- Do NOT include ANY meta information in the content\n" .
               "- Do NOT include title, meta_title, or meta_description in the content body\n" .
               "- Content should be pure article text with HTML formatting only\n\n" .
               "Format your response as:\n" .
               "TITLE: [Keep original title or improve slightly]\n" .
               "CONTENT: [Clean rewritten content - NO meta data, NO title repetition]\n" .
               "META_TITLE: [Meta title under 50 characters]\n" .
               "META_DESCRIPTION: [Meta description 120-155 characters]";
    }
    
    // REVAMP MODE: Total rewrite preserving spirit and keywords
    private function build_revamp_prompt($content, $keywords_to_preserve) {
        
        $rules_string = "KEYWORD PRESERVATION RULES:\n";
        
        if (!empty($keywords_to_preserve)) {
            foreach ($keywords_to_preserve as $keyword) {
                $keyword_safe = htmlspecialchars(trim($keyword), ENT_QUOTES, 'UTF-8');
                if (!empty($keyword_safe)) {
                    $rules_string .= "- PRESERVE: \"{$keyword_safe}\" - Include this exact word/phrase in your rewrite.\n";
                }
            }
        } else {
            $rules_string .= "- Preserve any metaphors, spiritual terms, and unique expressions found in the content.\n";
        }
        
        // Check persona for heading guidance
        $persona = $this->get_persona_context();
        $is_author_writer = (stripos($persona, 'author') !== false || 
                           stripos($persona, 'writer') !== false || 
                           stripos($persona, 'poetry') !== false || 
                           stripos($persona, 'spiritual') !== false ||
                           stripos($persona, 'philosophy') !== false);
        
        $heading_guidance = "";
        if ($is_author_writer) {
            $heading_guidance = "HEADING GUIDANCE FOR AUTHOR/WRITER PERSONA:\n" .
                              "- Do NOT add sections or headings unless the content is very long (500+ words)\n" .
                              "- Focus on flowing, narrative-style writing\n" .
                              "- Preserve the literary/spiritual essence\n" .
                              "- Use simple paragraph structure with <p> tags\n\n";
        } else {
            $heading_guidance = "HEADING GUIDANCE FOR JOURNALISTIC PERSONA:\n" .
                              "- Add clear headings (h2, h3) if content is substantial enough\n" .
                              "- Structure for readability and engagement\n" .
                              "- Use journalistic organization principles\n\n";
        }
        
        return "REVAMP MODE - TOTAL REWRITE:\n\n" .
               $this->custom_instructions . "\n\n" .
               $heading_guidance .
               "TASK: Completely rewrite the content below while preserving its core spirit, key ideas, and specified keywords.\n\n" .
               "REWRITE REQUIREMENTS:\n" .
               "- Transform the content completely while keeping the essence\n" .
               "- Preserve the core message and spirit of the original\n" .
               "- Improve structure, flow, and engagement\n" .
               "- Maintain the tone appropriate to your persona\n" .
               "- You may reorganize, expand, or restructure as needed\n\n" .
               $rules_string . "\n" .
               "Original Content:\n{$content}\n\n" .
               "CRITICAL CONTENT REQUIREMENTS:\n" .
               "- Output clean, block-editor friendly content\n" .
               "- Use simple HTML: <p>, <h2>, <h3>, <strong>, <em>, <ul>, <li>\n" .
               "- Do NOT include ANY meta information in the content\n" .
               "- Do NOT include title, meta_title, or meta_description in the content body\n" .
               "- Content should be pure article text with clean HTML formatting\n" .
               "- Ensure content works perfectly in WordPress block editor\n\n" .
               "Format your response as:\n" .
               "TITLE: [Your new engaging title]\n" .
               "CONTENT: [Your completely rewritten, clean content - NO meta data, NO title repetition]\n" .
               "META_TITLE: [Meta title under 50 characters]\n" .
               "META_DESCRIPTION: [Meta description 120-155 characters]";
    }

    // Simple structure instructions (not used in new logic, kept for compatibility)
    private function get_structure_instruction($structure_mode) {
        if ($structure_mode === 'revamp') {
            return "STRUCTURE MODE: FULL REVAMP - Complete rewrite preserving spirit and keywords";
        } else {
            return "STRUCTURE MODE: MAINTAIN ORIGINAL STRUCTURE - Rewrite content while preserving structure";
        }
    }

    private function call_ai_api($prompt) {
        $api_provider = $this->settings['api_provider'] ?? 'openai';
        
        switch ($api_provider) {
            case 'openai':
                return $this->call_openai_api($prompt);
            case 'claude':
                return $this->call_claude_api($prompt);
            case 'gemini':
                return $this->call_gemini_api($prompt);
            case 'deepseek':
                return $this->call_deepseek_api($prompt);
            default:
                throw new Exception(__('Invalid AI provider selected.', 'articlecraft-ai'));
        }
    }
    
    private function call_openai_api($prompt) {
        $api_key = $this->settings['openai_api_key'] ?? '';
        if (empty($api_key)) {
            throw new Exception(__('OpenAI API key is not configured.', 'articlecraft-ai'));
        }
        
        $model = $this->settings['model'] ?? 'gpt-3.5-turbo';
        $max_tokens = $this->settings['max_tokens'] ?? 2000;
        $temperature = $this->settings['temperature'] ?? 0.7;
        
        $body = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => intval($max_tokens),
            'temperature' => floatval($temperature)
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 120,
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('API request failed: ', 'articlecraft-ai') . $response->get_error_message());
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if (isset($response_data['error'])) {
            throw new Exception(__('API error: ', 'articlecraft-ai') . $response_data['error']['message']);
        }
        
        return $response_data['choices'][0]['message']['content'] ?? '';
    }
    
    private function call_claude_api($prompt) {
        $api_key = $this->settings['claude_api_key'] ?? '';
        if (empty($api_key)) {
            throw new Exception(__('Claude API key is not configured.', 'articlecraft-ai'));
        }
        
        $model = $this->settings['model'] ?? 'claude-3-5-sonnet-20241022';
        $max_tokens = $this->settings['max_tokens'] ?? 2000;
        
        $body = array(
            'model' => $model,
            'max_tokens' => intval($max_tokens),
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            )
        );
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ),
            'body' => json_encode($body),
            'timeout' => 120,
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('API request failed: ', 'articlecraft-ai') . $response->get_error_message());
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if (isset($response_data['error'])) {
            throw new Exception(__('API error: ', 'articlecraft-ai') . $response_data['error']['message']);
        }
        
        return $response_data['content'][0]['text'] ?? '';
    }
    
    private function call_gemini_api($prompt) {
        $api_key = $this->settings['gemini_api_key'] ?? '';
        if (empty($api_key)) {
            throw new Exception(__('Gemini API key is not configured.', 'articlecraft-ai'));
        }
        
        $model = $this->settings['model'] ?? 'gemini-2.0-flash-exp';
        $max_tokens = $this->settings['max_tokens'] ?? 2000;
        $temperature = $this->settings['temperature'] ?? 0.7;
        
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => floatval($temperature),
                'maxOutputTokens' => intval($max_tokens),
            )
        );
        
        $response = wp_remote_post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}", array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 120,
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('API request failed: ', 'articlecraft-ai') . $response->get_error_message());
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if (isset($response_data['error'])) {
            throw new Exception(__('API error: ', 'articlecraft-ai') . $response_data['error']['message']);
        }
        
        return $response_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    private function call_deepseek_api($prompt) {
        $api_key = $this->settings['deepseek_api_key'] ?? '';
        if (empty($api_key)) {
            throw new Exception(__('DeepSeek API key is not configured.', 'articlecraft-ai'));
        }
        
        $model = $this->settings['model'] ?? 'deepseek-chat';
        $max_tokens = $this->settings['max_tokens'] ?? 2000;
        $temperature = $this->settings['temperature'] ?? 0.7;
        
        $body = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => intval($max_tokens),
            'temperature' => floatval($temperature),
            'stream' => false
        );
        
        $response = wp_remote_post('https://api.deepseek.com/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 120,
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('API request failed: ', 'articlecraft-ai') . $response->get_error_message());
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if (isset($response_data['error'])) {
            throw new Exception(__('API error: ', 'articlecraft-ai') . $response_data['error']['message']);
        }
        
        return $response_data['choices'][0]['message']['content'] ?? '';
    }
    
    private function parse_ai_response($response) {
        $title = '';
        $content = '';
        $meta_title = '';
        $meta_description = '';
        
        // Extract title
        if (preg_match('/TITLE:\s*(.+?)(?=\n|$)/i', $response, $matches)) {
            $title = trim($matches[1]);
        }
        
        // Extract content - more precise regex to avoid meta bleeding
        if (preg_match('/CONTENT:\s*(.*?)(?=\n\s*META_TITLE:|$)/s', $response, $matches)) {
            $content = trim($matches[1]);
        }
        
        // Extract meta title - if empty, use the main title
        if (preg_match('/META_TITLE:\s*(.+?)(?=\n|$)/i', $response, $matches)) {
            $meta_title = trim($matches[1]);
            // Ensure meta title is under 50 characters
            if (strlen($meta_title) > 50) {
                $meta_title = substr($meta_title, 0, 47) . '...';
            }
        } else {
            // Fallback: use main title if meta title is not found
            $meta_title = $title;
            if (strlen($meta_title) > 50) {
                $meta_title = substr($meta_title, 0, 47) . '...';
            }
        }
        
        // Extract meta description
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?=\n|$)/i', $response, $matches)) {
            $meta_description = trim($matches[1]);
        }
        
        return array(
            'title' => $title,
            'content' => $content,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'raw_response' => $response
        );
    }
    
    private function log_operation($action_type, $source_url, $input_content, $generated_content, $parsed_result, $processing_time) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'articlecraft_ai_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => get_the_ID(),
                'action_type' => $action_type,
                'source_url' => $source_url,
                'input_content' => wp_trim_words($input_content, 100),
                'generated_content' => wp_trim_words($generated_content, 100),
                'meta_title' => $parsed_result['meta_title'] ?? '',
                'meta_description' => $parsed_result['meta_description'] ?? '',
                'tokens_used' => $this->estimate_tokens($generated_content),
                'processing_time' => $processing_time,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s')
        );
    }
    
    private function estimate_tokens($text) {
        // Rough estimation: 1 token ≈ 4 characters
        return intval(strlen($text) / 4);
    }
}
?>