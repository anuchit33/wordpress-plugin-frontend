# WordPress Plugin Frontend
การสร้าง WordPress Plugin สำหรับแสดงผลส่วน Frontend
- Form submit
- Ajax request and HTTP API

## Form Submit
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
Author URI: https://github.com/anuchit33/wordpress-plugin-frontend
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

### 4. สร้างเพจหลังจาก activation
1. สร้างเพจ WP Frontend
2. สร้างตาราง contact_message
```
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
Shortcode : `[wp_shortcode_display]`
```
    function __construct() {
        ...
        # Shortcode
        add_shortcode('shortcode-wp-frontend', array($this, 'wp_shortcode_display'));
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
<form method="POST">
  <?php wp_nonce_field( 'post-contact','csrf' ); ?>
  <div class="form-group">
    <label for="email">Email address</label>
    <input type="email" class="form-control" id="email" name ="email" aria-describedby="emailHelp" placeholder="Enter email">
  </div>
  <div class="form-group mt-3">
    <label for="message">Message</label>
    <textarea class="form-control" id="message"  name="message" rows="3" placeholder="Message..."></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### 8. Save Post
เพิ่มฟังก์ชั่น save_post()
```
    function save_post(){
        if (isset($_POST['email'])){
            if (! isset( $_POST['csrf'] ) || ! wp_verify_nonce( $_POST['csrf'], 'post-contact' )){
                echo 'Sorry, your nonce did not verify.';
                exit;
            }
            global $wpdb;
            $post = array(
                'email'    => sanitize_text_field($_POST['email']),
                'message'  => $_POST['message'],
                'created_datetime' => date('Y-m-s H:i:s'),
            );
            $tablename = $wpdb->prefix . 'contact_message';
            $wpdb->insert($tablename, $post);
            echo 'Saved your post successfully! :)';
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

## Ajax request and HTTP API
### 1. Add Shotcode สำหรับแสดงราคาทอง  
Shotcode: `[wp_shortcode_display_gold_price]`
---
    function __construct() {
        # Shortcode
        ...
        add_shortcode('shortcode-wp-gold-price', array($this, 'wp_shortcode_display_gold_price'));
    }
    
    function wp_shortcode_display_gold_price($atts) {
        ob_start();
        require_once( dirname(__FILE__) . '/templates/frontend/table-gold-price.php');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
---

สร้างไฟล์  `wordpress-plugin-frontend/themepates/frontend/table-gold-price.php`
```
<?php
# wp_enqueue_style
wp_enqueue_style('style-css', '/wp-content/plugins/wordpress-plugin-frontend/inc/css/style.css');
?>
<input type="date" value="<?=date('Y-m-d')?>" max="<?=date('Y-m-d')?>" name="date" id="inputDate" />
<br/>
<div class="row">
    <div class="col">
        <table class="table table-goldprice ">
            <tbody>
                <tr>
                    <td class="bg" colspan="2">ทองคำ 96.5% (บาทละ)</td>
                </tr>
                <tr>
                    <td class="text-center"><small>ราคารับซื้อ</small></td>
                    <td class="text-center"><small>ราคาขายออก</small></td>
                </tr>
                <tr>
                    <td class="text-center"><span id="bar965_sell_baht"></span></td>
                    <td class="text-center"><span id="bar965_buy_baht"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col">
            <table class="table table-goldprice ">
                <tbody>
                    <tr>
                        <td class="bg" colspan="2">ทองรูปพรรณ 96.5% (บาทละ)</td>
                    </tr>
                    <tr>
                        <td class="text-center"><small>ราคารับซื้อ</small></td>
                        <td class="text-center"><small>ราคาขายออก</small></td>
                    </tr>
                    <tr>
                        <td class="text-center"><span id="ornament965_sell_baht"></span></td>
                        <td class="text-center"><span id="ornament965_buy_baht"></span></td>
                    </tr>
                </tbody>
            </table>
    </div>
</div>

<script type="text/javascript">
    var $ajax_nonce = '<?=wp_create_nonce( "ajax_security" )?>';
    var $ajax_url = '<?=admin_url('admin-ajax.php')?>';

    jQuery(document).ready(function ($) {
        var getGoldPriceByDate = function (date = '') {
            var data = {
                action: 'get_gold_price',
                security: $ajax_nonce,
                date: date
            };
            $.ajax({
                type: 'get',
                url: $ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    $('#bar965_sell_baht').html(response.bar965_sell_baht)
                    $('#bar965_buy_baht').html(response.bar965_buy_baht)
                    $('#ornament965_sell_baht').html(response.ornament965_sell_baht)
                    $('#ornament965_buy_baht').html(response.ornament965_buy_baht)
                }
            });
        }

        // ready load
        getGoldPriceByDate($('#inputDate').val())

        // event
        $('#inputDate').change(function(){
            getGoldPriceByDate($('#inputDate').val())
        })

    });
</script>
```
### 2. Add Action
```
    function __construct() {
        ...
        # add action get
        add_action('wp_ajax_get_gold_price', array($this, 'wp_api_get_gold_price'));
        add_action('wp_ajax_nopriv_get_gold_price', array($this, 'wp_api_get_gold_price'));
    }
    
    function wp_api_get_gold_price(){
    }
```

### 2. function wp_qpi_get_gold_price
1. รอรับ ajax request
2. HTTP Api ดึงข้อมูลราคาทองจากเว็บ aagold-th
```
    function wp_api_get_gold_price(){

        # check ajax_security
        check_ajax_referer('ajax_security', 'security');

        # query string date
        $date = isset($_GET['date'])?$_GET['date']:date('Y-m-d');

        # fetch gold price
        $args = array();
        $url = 'https://www.aagold-th.com/price/daily/?date='.$date;
        $response = wp_remote_get( $url );
        $body = wp_remote_retrieve_body( $response );
        wp_send_json(json_decode($body,true)[0],200);
        die();
    }
```

# Setup Plugin
1. Run `cd wordpress-demo/web/docroot/wp-content/plugin/`
2. Run `git clone https://github.com/anuchit33/wordpress-plugin-frontend.git` 
