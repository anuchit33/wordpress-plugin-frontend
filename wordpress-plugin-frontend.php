<?php
/*
Plugin Name: WordPress Frontend
Plugin URI: https://github.com/anuchit33/wordpress-plugin-frontend
Description: wordpress-plugin-frontend
Author: Anuchit Yai-in
Version: 0.0.1
Author URI: https://github.com/anuchit33/wordpress-plugin-frontend
*/

class WordPressPluginFrontend {

    function __construct() {

        # Activation / Deactivation Hooks
        register_activation_hook(__FILE__, array($this, 'wp_activation'));
        register_deactivation_hook(__FILE__, array($this, 'wp_deactivation'));

        # Shortcode
        add_shortcode('shortcode-wp-frontend', array($this, 'wp_shortcode_display'));
        add_shortcode('shortcode-wp-gold-price', array($this, 'wp_shortcode_display_gold_price'));

        # add action get
        add_action('wp_ajax_get_gold_price', array($this, 'wp_api_get_gold_price'));
        add_action('wp_ajax_nopriv_get_gold_price', array($this, 'wp_api_get_gold_price'));

    }

    function wp_activation(){
        /**1. create page**/
        # create page WP Frontend
        $page_id = wp_insert_post(array(
            'post_title' => 'WP Frontend',
            'post_type' => 'page',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_content' => "[shortcode-wp-frontend]"
        ));

        # save page_id
        update_option( 'wp-frontend', $page_id );

        /**2. create table**/
        global $wpdb;
        $table_name = $wpdb->prefix.'contact_message';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE `$table_name` (
            `id` int(11) UNSIGNED NOT NULL,
            `email` varchar(55) NOT NULL,
            `message` text NOT NULL,
            `created_datetime` datetime NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
          ALTER TABLE `wp_contact_message` ADD PRIMARY KEY (`id`);";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function wp_deactivation(){

        # 1.delete page
        $page_id = get_option('wp-frontend');
        wp_delete_post($page_id);

        # 1.delete table
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_message';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);

    }

    // function wp_shortcode_display($atts){
    //     return 'Hello WordPress Plugin!';
    // }

    function wp_shortcode_display($atts) {

        # handle action POST
        $this->save_post();

        ob_start();
        require_once( dirname(__FILE__) . '/templates/frontend/page-contact.php');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    function wp_shortcode_display_gold_price($atts) {

        ob_start();
        require_once( dirname(__FILE__) . '/templates/frontend/table-gold-price.php');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    function save_post(){
        if (isset($_POST['email'])){
            if (! isset( $_POST['csrf'] ) || ! wp_verify_nonce( $_POST['csrf'], 'post-contact' )){
                echo 'Sorry, your nonce did not verify.';
                exit;
            }

            global $wpdb;
            $post = array(
                'email'    => $_POST['email'],
                'message'  => $_POST['message'],
                'created_datetime' => date('Y-m-s H:i:s'),
            );
            $tablename = $wpdb->prefix . 'contact_message';
            $wpdb->insert($tablename, $post);
            echo 'Saved your post successfully! :)';
        }
    }

    function wp_api_get_gold_price(){

        # check ajax_security
        check_ajax_referer('ajax_security', 'security');

        # filter date
        $date = isset($_GET['date'])?$_GET['date']:date('Y-m-d');

        # fetch gold price
        $args = array();
        $url = 'https://www.aagold-th.com/price/daily/?date='.$date;
        $response = wp_remote_get( $url );
        $body = wp_remote_retrieve_body( $response );
        wp_send_json(json_decode($body,true)[0],200);
        die();
    }
}

new WordPressPluginFrontend();
