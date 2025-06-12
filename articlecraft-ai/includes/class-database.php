<?php
/**
 * Database operations for ArticleCraft AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArticleCraftAI_Database {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'articlecraft_ai_logs';
        
        // Create tables on activation
        add_action('init', array($this, 'maybe_create_tables'));
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NULL,
            action_type varchar(50) NOT NULL,
            source_url text NULL,
            input_content longtext NULL,
            generated_content longtext NULL,
            meta_title varchar(255) NULL,
            meta_description text NULL,
            tokens_used int(11) NULL,
            processing_time float NULL,
            status varchar(20) DEFAULT 'completed',
            error_message text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY action_type (action_type),
            KEY created_at (created_at),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version
        add_option('articlecraft_ai_db_version', ARTICLECRAFT_AI_VERSION);
    }
    
    public function maybe_create_tables() {
        $installed_version = get_option('articlecraft_ai_db_version');
        
        if ($installed_version !== ARTICLECRAFT_AI_VERSION) {
            $this->create_tables();
        }
    }
    
    public function log_operation($data) {
        global $wpdb;
        
        $defaults = array(
            'post_id' => null,
            'action_type' => '',
            'source_url' => null,
            'input_content' => '',
            'generated_content' => '',
            'meta_title' => '',
            'meta_description' => '',
            'tokens_used' => 0,
            'processing_time' => 0,
            'status' => 'completed',
            'error_message' => null,
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array(
                '%d', // post_id
                '%s', // action_type
                '%s', // source_url
                '%s', // input_content
                '%s', // generated_content
                '%s', // meta_title
                '%s', // meta_description
                '%d', // tokens_used
                '%f', // processing_time
                '%s', // status
                '%s', // error_message
                '%s'  // created_at
            )
        );
        
        if ($result === false) {
            error_log('ArticleCraft AI: Failed to log operation - ' . $wpdb->last_error);
        }
        
        return $result;
    }
    
    public function get_logs($limit = 100, $offset = 0, $filters = array()) {
        global $wpdb;
        
        $where_clauses = array('1=1');
        $where_values = array();
        
        // Apply filters
        if (!empty($filters['post_id'])) {
            $where_clauses[] = 'post_id = %d';
            $where_values[] = intval($filters['post_id']);
        }
        
        if (!empty($filters['action_type'])) {
            $where_clauses[] = 'action_type = %s';
            $where_values[] = sanitize_text_field($filters['action_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = sanitize_text_field($filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $where_values[] = sanitize_text_field($filters['date_to']);
        }
        
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = sanitize_text_field($filters['status']);
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = intval($limit);
        $where_values[] = intval($offset);
        
        if (!empty($where_values)) {
            $prepared_sql = $wpdb->prepare($sql, $where_values);
        } else {
            $prepared_sql = $sql;
        }
        
        return $wpdb->get_results($prepared_sql);
    }
    
    public function get_log_count($filters = array()) {
        global $wpdb;
        
        $where_clauses = array('1=1');
        $where_values = array();
        
        // Apply same filters as get_logs
        if (!empty($filters['post_id'])) {
            $where_clauses[] = 'post_id = %d';
            $where_values[] = intval($filters['post_id']);
        }
        
        if (!empty($filters['action_type'])) {
            $where_clauses[] = 'action_type = %s';
            $where_values[] = sanitize_text_field($filters['action_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = sanitize_text_field($filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $where_values[] = sanitize_text_field($filters['date_to']);
        }
        
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = sanitize_text_field($filters['status']);
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";
        
        if (!empty($where_values)) {
            $prepared_sql = $wpdb->prepare($sql, $where_values);
        } else {
            $prepared_sql = $sql;
        }
        
        return $wpdb->get_var($prepared_sql);
    }
    
    public function delete_old_logs($days = 30) {
        global $wpdb;
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE created_at < %s",
                $date_threshold
            )
        );
        
        return $result;
    }
    
    public function get_statistics($days = 30) {
        global $wpdb;
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = array();
        
        // Total operations
        $stats['total_operations'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= %s",
                $date_threshold
            )
        );
        
        // Operations by type
        $stats['by_type'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT action_type, COUNT(*) as count FROM {$this->table_name} WHERE created_at >= %s GROUP BY action_type",
                $date_threshold
            ),
            ARRAY_A
        );
        
        // Total tokens used
        $stats['total_tokens'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(tokens_used) FROM {$this->table_name} WHERE created_at >= %s",
                $date_threshold
            )
        );
        
        // Average processing time
        $stats['avg_processing_time'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(processing_time) FROM {$this->table_name} WHERE created_at >= %s",
                $date_threshold
            )
        );
        
        // Success rate
        $stats['success_rate'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT (COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*)) FROM {$this->table_name} WHERE created_at >= %s",
                $date_threshold
            )
        );
        
        return $stats;
    }
    
    public function cleanup_database() {
        global $wpdb;
        
        // Delete logs older than 90 days
        $this->delete_old_logs(90);
        
        // Optimize table
        $wpdb->query("OPTIMIZE TABLE {$this->table_name}");
        
        return true;
    }
    
    public function get_table_name() {
        return $this->table_name;
    }
}