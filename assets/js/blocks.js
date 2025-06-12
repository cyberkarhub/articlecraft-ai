// ArticleCraft AI Gutenberg Blocks
(function() {
    'use strict';
    
    // Ensure WordPress blocks are available
    if (typeof wp === 'undefined' || !wp.blocks || !wp.element || !wp.components) {
        console.log('ArticleCraft AI: WordPress blocks not available');
        return;
    }
    
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { Button, TextControl } = wp.components;
    
    // Register ArticleCraft AI block
    registerBlockType('articlecraft-ai/generator', {
        title: 'ArticleCraft AI Generator',
        icon: 'admin-customizer',
        category: 'widgets',
        attributes: {
            url: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            
            return createElement(
                'div',
                { className: 'articlecraft-ai-block' },
                [
                    createElement('h3', { key: 'title' }, '🚀 ArticleCraft AI Generator'),
                    createElement('p', { 
                        key: 'description',
                        className: 'description' 
                    }, 'Generate professional articles from any URL using AI.'),
                    createElement(TextControl, {
                        key: 'url-input',
                        label: 'Article URL',
                        value: attributes.url,
                        placeholder: 'https://example.com/article',
                        onChange: function(value) {
                            setAttributes({ url: value });
                        }
                    }),
                    createElement(Button, {
                        key: 'generate-button',
                        className: 'articlecraft-ai-generate-button',
                        isPrimary: true,
                        onClick: function() {
                            if (attributes.url) {
                                console.log('ArticleCraft AI: Generating from URL:', attributes.url);
                                // Trigger the same functionality as the meta box
                                if (window.articlecraftAI && window.articlecraftAI.generateFromUrl) {
                                    window.articlecraftAI.generateFromUrl(attributes.url);
                                }
                            } else {
                                alert('Please enter a URL first.');
                            }
                        }
                    }, 'Generate Article')
                ]
            );
        },
        
        save: function() {
            // This block doesn't save content, it's a generator tool
            return null;
        }
    });
    
    console.log('ArticleCraft AI: Gutenberg block registered');
})();