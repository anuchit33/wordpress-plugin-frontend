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
                    console.log('response', response)

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