# WordPress Plugin Frontend
การสร้าง WordPress Plugin สำหรับแสดงผลส่วน Frontend

### 1.Header Requirements
สร้างไฟล์ wp-content/plugins/wordpress-plugin-frontend/`wordpress-plugin-frontend.php`
```
<?php 
/*
Plugin Name: WordPress Frontend
Plugin URI: https://github.com/anuchit33/wordpress-plugin-frontend
Description: wordpress-plugin-frontend
Author: Anuchit Yai-in
Version: 0.0.1
*/
```

### 2.สร้าง Plugin Class(OOP)
```
class WordPressPluginFrontend {
    function __construct() {
    }
}
new WordPressPluginFrontend();
```

### 3.Activation / Deactivation Hooks
เพิ่มเข้าไปในส่วนของ __construct()
```
    function __construct() {
        # Activation / Deactivation Hooks
        register_activation_hook(__FILE__, array($this, 'wp_activation'));
        register_deactivation_hook(__FILE__, array($this, 'wp_deactivation'));
    }

    function wp_activation(){
    }

    function wp_deactivation(){
    }
```

### 4. Handle activation
1. สร้างเพจ Contact-US
2. สร้างตาราง contact_message
2. สร้างตาราง contact_email
```
    function wp_activation(){
        /**1. create page**/
        # create page Contact-US
        $page_id = wp_insert_post(array(
            'post_title' => 'Contact-US',
            'post_type' => 'page',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_content' => "[contact-us]"
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
```

### 5. สร้างเพจหลังจาก deactivation
```
    function wp_deactivation(){
        # delete page
        $page_id = get_option('wp-frontend');
        wp_delete_post($page_id);
    }
```

### 6. Add Shortcode และการ  Handle display
Shortcode : `[contact-us]`
```
    function __construct() {
        ...
        # Shortcode
        add_shortcode('contact-us', array($this, 'wp_shortcode_display'));
    }
    
    function wp_shortcode_display($atts){
        return 'Hello WordPress Plugin!';
    }
```
การใช้ template html
```
    function wp_shortcode_display($atts) {
        ob_start();
        require_once( dirname(__FILE__) . '/templates/frontend/page-contact.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
```

### 7. สร้าง template
สร้างไฟล์ `wordpress-plugin-frontend/themepates/frontend/page-contact.php`
```
<form method="POST" action="<?php the_permalink();?>">
  <?php wp_nonce_field( 'post-contact','csrf' ); ?>

  <div class="form-group">
    <label for="name">Subject</label>
    <input type="text" class="form-control" id="subject" name ="subject" aria-describedby="subjectHelp" placeholder="Enter subject">
  </div>
  <div class="form-group">
    <label for="email">Email address</label>
    <input type="email" class="form-control" id="email" name ="email" aria-describedby="emailHelp" placeholder="Enter email">
  </div>
  <br/>
  <div class="form-group">
    <label for="message">Message</label>
    <textarea class="form-control" id="message"  name="message" rows="3" placeholder="Message..."></textarea>
  </div>
  <button type="submit" class="btn btn-primary btn-submit-contact" >Submit</button>
</form>
```

### 8. action post
เพิ่มฟังก์ชั่น action_post()
- บันทึกข้อมูลผู้ติดต่อลงตาราง
- ส่งเมลแจ้ง
```
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
```
wp_shortcode_display เรียกฟังก์ชั่น save post
```
    function wp_shortcode_display($atts) {
        # handle action POST
        $this->save_post();
        ...    
    }
```
