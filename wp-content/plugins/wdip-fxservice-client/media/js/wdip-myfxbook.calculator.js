(function ($) {
    $.fn.FXCalculator = function (options) {
        var context = this,
            opt = $.extend({
                fee: ['0.00'],
                accountId: null,
                saveData: function () {
                    var post_data = context.data('post_data'),
                        data = {};
                    if (!post_data) {
                        post_data = {};
                    }
                    data[$(this).attr('name')] = $(this).val();
                    context.data('post_data', $.extend(post_data, data));
                }
            }, options);

        $('.show-graph', context).button().on('click', function () {
            //if (context.data('chart_series')) {
            var el = $('<div>').css({display: "none"}).appendTo(context);
            
            Highcharts.chart(el[0], opt.chartOptions);
            
            el.dialog({
                title: "Calculation result into graph",
                width: parseInt(opt.chartOptions.chart.width, 10) + 50
            });
        });

        $('.wdip-menu', context).height(
            $('.show-graph', context).height()
        );

        $('.wdip-data-amount input', context).on('change', opt.saveData);
        $('.wdip-data-date input', context).datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true
        }).on('change', opt.saveData);
        $('.wdip-data-fee select', context).append((function (opt) {
            var selOpt = "";
            $(opt.fee).each(function (n, item) {
                selOpt += "<option value=\"" + parseFloat(item / 100).toFixed(2) + "\">" + item + "%</option>\n";
            });
            return selOpt;
        })(opt)).on('change', opt.saveData);

        context.data('post_data', {
            fee: $('.wdip-data-fee select', context).find('option:selected').attr('value')
        });

        $('form', context).submit(function (e) {
            e.preventDefault();
            $('input[type="submit"]', context).val('Please wait...').attr('disabled', true);

            $.post(opt.adminUrl, $.extend({
                action: 'wdip-calculate-growth-data',
                accountId: opt.accountId,
                serviceClient: opt.serviceClient
            }, context.data('post_data')), function (result) {
                if (result.success) {
                    var t_amount = result.data.totalAmount,
                        t_amount_sign = t_amount >= 0 ? '+' : '',
                        gl_amount = result.data.gainLosAmount,
                        gl_amount_sign = gl_amount >= 0 ? '+' : '';

                    $(".total-amount", context).text(t_amount_sign + t_amount);
                    $(".gain-loss-amount", context).text(gl_amount_sign + gl_amount);
                    $(".fee-amount", context).text(result.data.feeAmount);

                    if (t_amount >= 0) {
                        $(".total-amount", context).removeClass('down-amount').addClass('up-amount');
                    } else {
                        $(".total-amount", context).removeClass('up-amount').addClass('down-amount');
                    }

                    if (gl_amount >= 0) {
                        $(".gain-loss-amount", context).removeClass('down-amount').addClass('up-amount');
                    } else {
                        $(".gain-loss-amount", context).removeClass('up-amount').addClass('down-amount');
                    }

                    opt.chartOptions = result.data.chartOptions;

                } else {
                    $(".wdip-field", context).each(function () {
                        $(this).text('0.00');
                    });
                }
                $('input[type="submit"]', context).val('Calculate').attr('disabled', null);
            });
            return false;
        });
    }
})(jQuery);