<?php
/*
Plugin Name: WordPress Frontend
Plugin URI: https://github.com/anuchit33/wordpress-plugin-frontend
Description: wordpress-plugin-frontend
Author: Anuchit Yai-in
Version: 0.0.1
*/

class WordPressPluginFrontend {

    function __construct() {

        # Activation / Deactivation Hooks
        register_activation_hook(__FILE__, array($this, 'wp_activation'));
        register_deactivation_hook(__FILE__, array($this, 'wp_deactivation'));

        # Shortcode
        add_shortcode('shortcode-wp-frontend', array($this, 'wp_shortcode_display'));

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

        /**2. create table contact_message**/
        global $wpdb;
        $table_name = $wpdb->prefix.'contact_message';
        $charset_collate = $wpdb->get_charset_collate();
        $sql_contact_message = "CREATE TABLE `$table_name` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `subject` varchar(55) NOT NULL,
            `email` varchar(55) NOT NULL,
            `message` text NOT NULL,
            `created_datetime` datetime NOT NULL,
		    UNIQUE KEY id (id)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        
        /**3. create table contact_email**/
        global $wpdb;
        $table_name = $wpdb->prefix.'contact_email';
        $charset_collate = $wpdb->get_charset_collate();
        $sql_contact_email = "CREATE TABLE `$table_name` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(55) NOT NULL,
            `email` varchar(55) NOT NULL,
		    UNIQUE KEY id (id)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        # 
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_contact_message );
        dbDelta( $sql_contact_email );
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
        $this->action_post();

        ob_start();
        require_once( dirname(__FILE__) . '/templates/frontend/page-contact.php');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    function action_post(){
        if (isset($_POST['subject'])){
            if (! isset( $_POST['csrf'] ) || ! wp_verify_nonce( $_POST['csrf'], 'post-contact' )){
                echo 'Sorry, your nonce did not verify.';
                exit;
            }

            global $wpdb;
            $data = array(
                'subject'    => sanitize_text_field($_POST['subject']),
                'email'    => sanitize_text_field($_POST['email']),
                'message'  => sanitize_text_field($_POST['message']),
                'created_datetime' => date('Y-m-d H:i:s'),
            );
            $tablename = $wpdb->prefix . 'contact_message';
            $wpdb->insert($tablename, $data);
            echo 'Saved your post successfully! :)';


            // send email
            $tablename = $wpdb->prefix . 'contact_email';
            $results = $wpdb->get_results("SELECT * FROM " . $tablename . " ", OBJECT);
            $to = array();
            foreach ($results as $v) {
                $to[] = $v->email;
            }
            $subject = 'Contact Us';
            $message = "Subject: " . $data["subject"] . " \n Email: " . $data["email"] . " \n Message: \n " . $data["message"] . "";
            $headers = array('From: '.$data['email'].' <'.$data['email'].'>');

            wp_mail($to, $subject, $message, $headers);
        }
    }

}

new WordPressPluginFrontend();