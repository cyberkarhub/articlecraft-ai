<?php
/**
 * URL Scraper for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_URLScraper {
    
    public function scrape_url($url) {
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception(__('Invalid URL provided.', 'articlecraft-ai'));
            }
            
            // Fetch the URL content
            $response = wp_remote_get($url, array(
                'timeout' => 30,
                'user-agent' => 'ArticleCraft AI Bot/1.0',
                'headers' => array(
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                )
            ));
            
            if (is_wp_error($response)) {
                throw new Exception(__('Failed to fetch URL: ', 'articlecraft-ai') . $response->get_error_message());
            }
            
            $html = wp_remote_retrieve_body($response);
            
            if (empty($html)) {
                throw new Exception(__('No content found at the provided URL.', 'articlecraft-ai'));
            }
            
            // Parse and extract content
            return $this->extract_content($html, $url);
            
        } catch (Exception $e) {
            error_log('ArticleCraft AI URL Scraper Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function extract_content($html, $url) {
        // Create DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        $content = array(
            'title' => $this->extract_title($xpath),
            'description' => $this->extract_description($xpath),
            'content' => $this->extract_main_content($xpath),
            'url' => $url
        );
        
        // Combine all extracted content
        $combined_content = "Title: " . $content['title'] . "\n\n";
        
        if (!empty($content['description'])) {
            $combined_content .= "Description: " . $content['description'] . "\n\n";
        }
        
        $combined_content .= "Content:\n" . $content['content'];
        
        return $combined_content;
    }
    
    private function extract_title($xpath) {
        // Try multiple selectors for title
        $selectors = array(
            '//title',
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//h1[1]'
        );
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $title = trim($nodes->item(0)->nodeValue);
                if (!empty($title)) {
                    return $title;
                }
            }
        }
        
        return '';
    }
    
    private function extract_description($xpath) {
        // Try multiple selectors for description
        $selectors = array(
            '//meta[@name="description"]/@content',
            '//meta[@property="og:description"]/@content',
            '//meta[@name="twitter:description"]/@content'
        );
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $description = trim($nodes->item(0)->nodeValue);
                if (!empty($description)) {
                    return $description;
                }
            }
        }
        
        return '';
    }
    
    private function extract_main_content($xpath) {
        // Try multiple selectors for main content
        $selectors = array(
            '//article',
            '//*[@class="content"]',
            '//*[@class="post-content"]',
            '//*[@class="entry-content"]',
            '//*[@id="content"]',
            '//*[@class="main-content"]',
            '//main',
            '//div[contains(@class, "content")]'
        );
        
        $content = '';
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $content = $this->extract_text_from_node($nodes->item(0));
                if (strlen($content) > 200) { // Only use if substantial content
                    break;
                }
            }
        }
        
        // If no substantial content found, try body
        if (strlen($content) < 200) {
            $bodyNodes = $xpath->query('//body');
            if ($bodyNodes->length > 0) {
                $content = $this->extract_text_from_node($bodyNodes->item(0));
            }
        }
        
        // Clean up the content
        return $this->clean_content($content);
    }
    
    private function extract_text_from_node($node) {
        // Remove script and style elements
        $xpath = new DOMXPath($node->ownerDocument);
        $scripts = $xpath->query('.//script | .//style | .//nav | .//header | .//footer | .//aside', $node);
        
        foreach ($scripts as $script) {
            $script->parentNode->removeChild($script);
        }
        
        // Get text content
        $text = $node->textContent;
        
        // Clean whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    private function clean_content($content) {
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove common unwanted phrases
        $unwanted_phrases = array(
            'Cookie Policy',
            'Privacy Policy',
            'Terms of Service',
            'Subscribe to Newsletter',
            'Share this article',
            'Follow us on',
            'Advertisement'
        );
        
        foreach ($unwanted_phrases as $phrase) {
            $content = str_ireplace($phrase, '', $content);
        }
        
        // Limit content length (AI APIs have token limits)
        if (strlen($content) > 8000) {
            $content = substr($content, 0, 8000) . '...';
        }
        
        return trim($content);
    }
}