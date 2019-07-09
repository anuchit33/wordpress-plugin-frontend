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

}

new WordPressPluginFrontend();
