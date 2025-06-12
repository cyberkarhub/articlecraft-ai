(function($) {
    'use strict';
    
    let isGutenbergReady = false;
    let progressInterval = null;
    let currentStep = 0;
    let gutenbergAttempts = 0;
    
    // IMMEDIATELY inject critical CSS - before DOM ready
    injectCriticalCSS();
    
    $(document).ready(function() {
        console.log('ArticleCraft AI: Admin script loaded at', new Date().toISOString());
        
        // Register debug functions IMMEDIATELY
        registerDebugFunctions();
        
        // Initialize everything
        initializePlugin();
        
        // Log script state
        console.log('ArticleCraft AI: Script initialized. Debug functions available.');
    });
    
    function injectCriticalCSS() {
        // Force inject spinner CSS immediately - even before DOM ready
        const style = document.createElement('style');
        style.id = 'articlecraft-force-spinner-css';
        style.innerHTML = `
            /* FORCE SPINNER - HIGHER SPECIFICITY */
            .articlecraft-ai-meta-box .button.articlecraft-spinner-active,
            .articlecraft-ai-section .button.articlecraft-spinner-active {
                position: relative !important;
                color: rgba(0,0,0,0) !important;
                pointer-events: none !important;
                cursor: wait !important;
            }
            
            .articlecraft-ai-meta-box .button.articlecraft-spinner-active *,
            .articlecraft-ai-section .button.articlecraft-spinner-active * {
                opacity: 0 !important;
                visibility: hidden !important;
            }
            
            .articlecraft-ai-meta-box .button.articlecraft-spinner-active::before,
            .articlecraft-ai-section .button.articlecraft-spinner-active::before {
                content: "" !important;
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                width: 20px !important;
                height: 20px !important;
                margin-top: -10px !important;
                margin-left: -10px !important;
                border: 3px solid rgba(255,255,255,0.2) !important;
                border-top: 3px solid #ffffff !important;
                border-radius: 50% !important;
                animation: articlecraft-force-spin 0.8s linear infinite !important;
                z-index: 999999 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            @keyframes articlecraft-force-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        
        // Insert immediately
        if (document.head) {
            document.head.appendChild(style);
        } else {
            // If head doesn't exist yet, wait for it
            document.addEventListener('DOMContentLoaded', function() {
                document.head.appendChild(style);
            });
        }
        
        console.log('ArticleCraft AI: Force spinner CSS injected');
    }
    
    function registerDebugFunctions() {
        // Register debug functions on window object
        window.articlecraftDebug = {
            testSpinner: function() {
                console.log('🧪 Testing spinner - FORCE method...');
                const button = $('#articlecraft-ai-generate-from-url');
                
                if (button.length === 0) {
                    console.error('❌ Button not found!');
                    return;
                }
                
                console.log('✅ Button found, applying spinner...');
                button.addClass('articlecraft-spinner-active');
                
                // Remove after 5 seconds
                setTimeout(function() {
                    button.removeClass('articlecraft-spinner-active');
                    console.log('✅ Spinner removed');
                }, 5000);
            },
            
            testProgress: function() {
                console.log('🧪 Testing progress animation...');
                showProgress('Testing progress animation...', true);
                setTimeout(function() {
                    hideProgress();
                }, 10000);
            },
            
            testTitle: function(title = 'TEST TITLE ' + new Date().getTime()) {
                console.log('🧪 Testing title update with:', title);
                forceUpdateTitle(title);
            },
            
            simulateGeneration: function() {
                console.log('🧪 Simulating full generation process...');
                const mockData = {
                    title: 'Mock Generated Title ' + new Date().getTime(),
                    content: '<h2>Mock Generated Content</h2><p>This is a <strong>test paragraph</strong> with some content.</p><p>Another paragraph to test.</p>',
                    meta_title: 'Mock Meta Title',
                    meta_description: 'This is a mock meta description for testing purposes.'
                };
                
                handleAIResponse(mockData);
            }
        };
        
        console.log('ArticleCraft AI: Debug functions registered');
    }
    
    function initializePlugin() {
        // More aggressive Gutenberg detection
        detectGutenberg();
        
        // Setup event handlers
        setupEventHandlers();
        
        // Keep checking for Gutenberg every 1 second for first 30 seconds
        const gutenbergChecker = setInterval(function() {
            detectGutenberg();
            if (gutenbergAttempts > 30) {
                clearInterval(gutenbergChecker);
                console.log('ArticleCraft AI: Stopped Gutenberg detection after 30 attempts');
            }
        }, 1000);
    }
    
    function detectGutenberg() {
        gutenbergAttempts++;
        
        // Multiple detection methods
        const hasWP = typeof wp !== 'undefined';
        const hasData = hasWP && wp.data;
        const hasBlocks = hasWP && wp.blocks;
        const hasBlockEditor = hasData && wp.data.select && wp.data.select('core/block-editor');
        const hasEditor = hasData && wp.data.select && wp.data.select('core/editor');
        
        if (hasWP && hasData && hasBlocks && hasBlockEditor && hasEditor) {
            if (!isGutenbergReady) {
                isGutenbergReady = true;
                console.log('✅ ArticleCraft AI: Gutenberg READY! Attempt:', gutenbergAttempts);
            }
        } else {
            if (gutenbergAttempts <= 10 || gutenbergAttempts % 5 === 0) {
                console.log(`⏳ Waiting for Gutenberg... Attempt ${gutenbergAttempts}`);
            }
        }
    }
    
    function setupEventHandlers() {
        // Generate from URL
        $(document).off('click', '#articlecraft-ai-generate-from-url').on('click', '#articlecraft-ai-generate-from-url', function(e) {
            e.preventDefault();
            console.log('ArticleCraft AI: Generate clicked');
            
            const url = $('#articlecraft-ai-url').val().trim();
            if (!url || !isValidUrl(url)) {
                showNotice('Please enter a valid URL', 'error');
                return;
            }
            
            // Get structure mode
            const structureMode = $('input[name="articlecraft_structure_mode"]:checked').val() || 'maintain';
            
            startGeneration('generate', url, structureMode);
        });
        
        // Rewrite content
        $(document).off('click', '#articlecraft-ai-rewrite-content').on('click', '#articlecraft-ai-rewrite-content', function(e) {
            e.preventDefault();
            console.log('ArticleCraft AI: Rewrite clicked');
            
            const content = getEditorContent();
            if (!content || content.trim() === '') {
                showNotice('Please add some content to rewrite.', 'error');
                return;
            }
            
            // Get structure mode
            const structureMode = $('input[name="articlecraft_rewrite_structure_mode"]:checked').val() || 'maintain';
            
            startGeneration('rewrite', content, structureMode);
        });
        
        // Apply to SEO plugin button
        $(document).off('click', '#articlecraft-apply-seo').on('click', '#articlecraft-apply-seo', function(e) {
            e.preventDefault();
            applySEOMeta();
        });
    }
    
    function startGeneration(type, data, structureMode) {
        const isGenerate = type === 'generate';
        const button = isGenerate ? $('#articlecraft-ai-generate-from-url') : $('#articlecraft-ai-rewrite-content');
        const action = isGenerate ? 'articlecraft_ai_generate_from_url' : 'articlecraft_ai_rewrite_content';
        const requestData = isGenerate ? { url: data } : { content: data };
        
        // Add structure mode to request
        requestData.structure_mode = structureMode;
        
        console.log('ArticleCraft AI: Starting', type, 'with data length:', data.length, 'structure mode:', structureMode);
        
        // Show spinner and progress
        showButtonSpinner(button);
        showProgress('Processing with AI...', true);
        
        $.ajax({
            url: articlecraftAI.ajax_url,
            type: 'POST',
            data: {
                action: action,
                ...requestData,
                nonce: articlecraftAI.nonce
            },
            timeout: 180000,
            success: function(response) {
                console.log('ArticleCraft AI: Response received:', response);
                
                hideButtonSpinner(button);
                hideProgress();
                
                if (response.success && response.data) {
                    handleAIResponse(response.data);
                    if (isGenerate) $('#articlecraft-ai-url').val('');
                    showNotice(`Content ${type}d successfully! 🎉`, 'success');
                } else {
                    showNotice(response.data?.message || 'An error occurred. Please try again.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('ArticleCraft AI: AJAX error:', status, error);
                
                hideButtonSpinner(button);
                hideProgress();
                
                const errorMsg = status === 'timeout' ? 
                    'Request timed out. Please try again.' : 
                    'Network error occurred. Please try again.';
                showNotice(errorMsg, 'error');
            }
        });
    }
    
    function showButtonSpinner(button) {
        console.log('🔄 Showing spinner for button:', button.attr('id'));
        button.addClass('articlecraft-spinner-active').prop('disabled', true);
    }
    
    function hideButtonSpinner(button) {
        console.log('⏹️ Hiding spinner for button:', button.attr('id'));
        button.removeClass('articlecraft-spinner-active').prop('disabled', false);
    }
    
    function handleAIResponse(data) {
        console.log('📝 Processing AI response...');
        console.log('- Title:', data.title);
        console.log('- Content length:', data.content ? data.content.length : 0);
        console.log('- Meta title:', data.meta_title);
        console.log('- Meta description:', data.meta_description);
        
        // Update EDITOR title (post title) - ONLY in editor, NOT in content
        if (data.title) {
            console.log('📝 Updating editor title:', data.title);
            forceUpdateTitle(data.title);
            
            // Try again after delays for reliability
            setTimeout(() => forceUpdateTitle(data.title), 1000);
        }
        
        // Update CONTENT (article body) - WITHOUT title duplication
        if (data.content) {
            console.log('📝 Updating editor content (length:', data.content.length, ')');
            // Clean content to ensure no title duplication
            const cleanContent = cleanContentFromTitle(data.content, data.title);
            
            setTimeout(() => forceUpdateContent(cleanContent), 500);
            setTimeout(() => forceUpdateContent(cleanContent), 2000);
        } else {
            console.error('❌ No content received from AI!');
        }
        
        // Update META fields (for display only - will be applied to SEO plugin)
        if (data.meta_title) {
            console.log('📊 Updating meta title display:', data.meta_title);
            $('#articlecraft-ai-meta-title').val(data.meta_title).trigger('input');
        }
        if (data.meta_description) {
            console.log('📊 Updating meta description display:', data.meta_description);
            $('#articlecraft-ai-meta-description').val(data.meta_description).trigger('input');
        }
        
        // Show SEO apply button if both meta fields are filled
        if (data.meta_title && data.meta_description) {
            showSEOApplyButton();
        }
        
        // Scroll to editor after a delay
        setTimeout(() => scrollToEditor(), 1000);
    }
    
    function cleanContentFromTitle(content, title) {
        if (!title || !content) return content;
        
        // Remove title if it appears at the beginning of content
        const titlePattern = new RegExp(`^\\s*<h[1-6][^>]*>\\s*${escapeRegExp(title)}\\s*</h[1-6]>\\s*`, 'i');
        let cleanedContent = content.replace(titlePattern, '');
        
        // Also check for plain title at the beginning
        const plainTitlePattern = new RegExp(`^\\s*${escapeRegExp(title)}\\s*`, 'i');
        cleanedContent = cleanedContent.replace(plainTitlePattern, '');
        
        return cleanedContent.trim();
    }
    
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function showSEOApplyButton() {
        // Check if button already exists
        if ($('#articlecraft-apply-seo').length > 0) {
            return;
        }
        
        // Add apply to SEO button
        const seoButton = `
            <div class="articlecraft-ai-seo-apply" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <button type="button" 
                        id="articlecraft-apply-seo" 
                        class="button button-primary"
                        style="width: 100%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                    <span class="dashicons dashicons-admin-settings"></span>
                    Apply to SEO Plugin
                </button>
                <p class="description" style="margin-top: 8px; text-align: center; font-size: 12px; color: #666;">
                    Automatically apply meta title and description to your SEO plugin
                </p>
            </div>
        `;
        
        $('.articlecraft-ai-section').last().append(seoButton);
    }
    
    function applySEOMeta() {
        const metaTitle = $('#articlecraft-ai-meta-title').val().trim();
        const metaDescription = $('#articlecraft-ai-meta-description').val().trim();
        const postId = $('#post_ID').val() || $('input[name="post_ID"]').val();
        
        if (!metaTitle || !metaDescription) {
            showNotice('Meta title and description are required.', 'error');
            return;
        }
        
        if (!postId) {
            showNotice('Please save the post first before applying SEO meta.', 'error');
            return;
        }
        
        const button = $('#articlecraft-apply-seo');
        showButtonSpinner(button);
        
        $.ajax({
            url: articlecraftAI.ajax_url,
            type: 'POST',
            data: {
                action: 'articlecraft_ai_apply_seo_meta',
                post_id: postId,
                meta_title: metaTitle,
                meta_description: metaDescription,
                nonce: articlecraftAI.nonce
            },
            success: function(response) {
                hideButtonSpinner(button);
                
                if (response.success) {
                    showNotice(`SEO meta applied to ${response.data.plugin} successfully! ✅`, 'success');
                    button.text('✅ Applied to ' + response.data.plugin).prop('disabled', true);
                } else {
                    showNotice(response.data?.message || 'Failed to apply SEO meta.', 'error');
                }
            },
            error: function() {
                hideButtonSpinner(button);
                showNotice('Network error occurred while applying SEO meta.', 'error');
            }
        });
    }
    
    function forceUpdateTitle(title) {
        console.log('📝 FORCE updating title to:', title);
        
        // Method 1: Gutenberg via wp.data
        if (isGutenbergReady) {
            console.log('🎯 Trying Gutenberg title update...');
            
            try {
                if (wp.data.dispatch('core/editor')) {
                    wp.data.dispatch('core/editor').editPost({ title: title });
                    console.log('✅ Title updated via core/editor');
                }
            } catch (error) {
                console.log('❌ Gutenberg title update error:', error);
            }
        }
        
        // Method 2: DOM manipulation for classic editor and Gutenberg fallback
        console.log('🎯 Trying DOM title update...');
        
        const titleSelectors = [
            '#title',                                           // Classic editor
            '.editor-post-title__input',                        // Gutenberg
            '.editor-post-title textarea',                      // Gutenberg textarea
            '.wp-block-post-title',                            // Block
            '[aria-label="Add title"]',                        // Accessibility
            '.edit-post-visual-editor__post-title-wrapper textarea',
            '.edit-post-visual-editor__post-title-wrapper input',
            '.block-editor-post-title__input'                 // New Gutenberg
        ];
        
        let updated = false;
        titleSelectors.forEach(selector => {
            const elements = $(selector);
            if (elements.length > 0) {
                console.log(`📝 Updating title via ${selector} (${elements.length} elements)`);
                
                elements.each(function() {
                    const $el = $(this);
                    const element = this;
                    
                    // Clear and set value
                    if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                        $el.val('').val(title);
                    } else {
                        $el.empty().text(title);
                    }
                    
                    // Trigger events
                    $el.trigger('focus').trigger('input').trigger('change').trigger('keyup').trigger('blur');
                    
                    // Force native events
                    if (element.dispatchEvent) {
                        element.dispatchEvent(new Event('input', { bubbles: true }));
                        element.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    updated = true;
                });
            }
        });
        
        if (updated) {
            console.log('✅ Title updated via DOM manipulation');
        } else {
            console.log('❌ No title elements found for update');
        }
    }
    
    function forceUpdateContent(content) {
        console.log('📝 FORCE updating content (length:', content.length, ')');
        
        // Priority 1: TinyMCE (Classic Editor) - Most reliable for HTML content
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            console.log('🎯 Using TinyMCE for content update...');
            try {
                tinyMCE.activeEditor.setContent(content);
                tinyMCE.activeEditor.save();
                console.log('✅ Content updated via TinyMCE');
                return;
            } catch (error) {
                console.log('❌ TinyMCE update failed:', error);
            }
        }
        
        // Priority 2: Textarea fallback
        const textarea = $('#content');
        if (textarea.length) {
            console.log('🎯 Using textarea for content update...');
            try {
                textarea.val(content).trigger('change');
                console.log('✅ Content updated via textarea');
                return;
            } catch (error) {
                console.log('❌ Textarea update failed:', error);
            }
        }
        
        console.log('❌ All content update methods failed!');
    }
    
    function getEditorContent() {
        console.log('📖 Getting editor content...');
        
        // Try classic editor first for consistency
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            const content = tinyMCE.activeEditor.getContent();
            console.log('✅ Content from TinyMCE (length:', content.length, ')');
            return content;
        }
        
        // Textarea fallback
        const textarea = $('#content');
        if (textarea.length) {
            const content = textarea.val();
            console.log('✅ Content from textarea (length:', content.length, ')');
            return content;
        }
        
        console.log('❌ No content found');
        return '';
    }
    
    function scrollToEditor() {
        console.log('📜 Scrolling to editor...');
        try {
            const editorTargets = [
                '#post-body-content',
                '.edit-post-layout__content',
                '.editor-styles-wrapper',
                '#content_ifr',
                '#content'
            ];
            
            let scrollTarget = null;
            for (const selector of editorTargets) {
                const element = $(selector).first();
                if (element.length && element.is(':visible')) {
                    scrollTarget = element;
                    break;
                }
            }
            
            if (scrollTarget) {
                $('html, body').animate({
                    scrollTop: scrollTarget.offset().top - 100
                }, 1000);
                console.log('✅ Scrolled to editor');
            } else {
                console.log('⚠️ No visible editor target found for scrolling');
            }
        } catch (error) {
            console.log('❌ Scroll to editor failed:', error);
        }
    }
    
    function showProgress(text, forceShow = false) {
        const progressDiv = $('#articlecraft-ai-progress');
        if (progressDiv.length === 0) {
            console.error('❌ Progress div not found!');
            return;
        }
        
        progressDiv.find('.progress-text').text(text);
        progressDiv.show().css('display', 'block');
        
        startProgressAnimation();
        console.log('✅ Progress shown:', text);
    }
    
    function hideProgress() {
        $('#articlecraft-ai-progress').fadeOut(300);
        stopProgressAnimation();
        console.log('✅ Progress hidden');
    }
    
    function startProgressAnimation() {
        stopProgressAnimation();
        
        currentStep = 0;
        $('.progress-messages .message').removeClass('active');
        
        showProgressStep(1);
        
        progressInterval = setInterval(() => {
            currentStep = currentStep >= 3 ? 1 : currentStep + 1;
            showProgressStep(currentStep);
        }, 2500);
    }
    
    function showProgressStep(step) {
        $('.progress-messages .message').removeClass('active');
        $(`.progress-messages .message[data-step="${step}"]`).addClass('active');
    }
    
    function stopProgressAnimation() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        $('.progress-messages .message').removeClass('active');
    }
    
    function showNotice(message, type) {
        $('.articlecraft-ai-notice').remove();
        
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const icon = type === 'success' ? '✅' : '❌';
        
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible articlecraft-ai-notice">
                <p><strong>${icon} ArticleCraft AI:</strong> ${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss</span>
                </button>
            </div>
        `);
        
        const target = $('.wrap h1, .wp-header-end, #wpbody-content').first();
        if (target.length) {
            target.after(notice);
        } else {
            $('.articlecraft-ai-meta-box').prepend(notice);
        }
        
        setTimeout(() => notice.fadeOut(() => notice.remove()), 8000);
        notice.on('click', '.notice-dismiss', () => notice.fadeOut(() => notice.remove()));
    }
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch {
            return false;
        }
    }
    
})(jQuery);