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