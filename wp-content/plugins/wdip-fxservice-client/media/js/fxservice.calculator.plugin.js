(function ($) {
    $.fn.FXServiceCalculator = function (options) {
        var plugin = this;
        plugin.prop('options', options);
        plugin.prop('params', {});
        plugin.prop('methods', {
            init: function () {
                var fee = $('select[name="performanceFee"]', plugin),
                    start = $('input[name="startDate"]', plugin),
                    invest = $('input[name="investAmount"]', plugin),
                    submit = $('input[type="submit"]', plugin),
                    options = plugin.prop('options');

                $(options.feeList).each(function (i, val) {
                    fee.append("<option value=\"" + parseInt(val, 10) + "\">" + val + "%</option>\n")
                });

                fee.on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                if (typeof options.performanceFee != 'undefined') {
                    fee.val(options.performanceFee).change();
                }

                start.datepicker({
                    dateFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true
                }).on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                if (typeof options.startDate != 'undefined') {
                    start.val(options.startDate).change();
                }

                invest.on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                if (typeof options.investAmount != 'undefined') {
                    invest.val(options.investAmount).change();
                }

                $('input[name="reset"]', plugin).on('click', function () {
                    plugin.prop('methods').update({});
                });

                $('form', plugin).submit(function (e) {
                    e.preventDefault();

                    if (plugin.prop('methods').validate()) {
                        submit.val('Please wait...').attr('disabled', true);

                        plugin.prop('methods').calculate(function () {
                            submit.val('Calculate').attr('disabled', null);
                        });
                    }

                    return false;
                });

                if (plugin.prop('methods').validate(false)) {
                    plugin.prop('methods').calculate();  
                }
            },
            saveParam: function (name, value) {
                plugin.prop('params')[name] = value;
            },
            calculate: function (callback) {
                var options = plugin.prop('options');

                $.post(
                    options.adminUrl,
                    $.extend({
                        action: 'wdip-calculate-growth-data',
                        accountId: options.accountId,
                        serviceClient: options.serviceClient
                    }, plugin.prop('params')),
                    function (result) {
                        var data = result.success ? result.data : {};
                        plugin.prop('methods').update(data);
                        callback();
                    }
                );
            },
            update: function (data) {
                if (!$.isEmptyObject(data)) {
                    var t_amount = parseFloat(data.totalAmount).toFixed(2),
                        t_amount_sign = t_amount >= 0 ? '+' : '',
                        gl_amount = parseFloat(data.gainLosAmount).toFixed(2),
                        gl_amount_sign = gl_amount >= 0 ? '+' : '';

                    $(".total-amount", plugin).text(t_amount_sign + t_amount);
                    $(".gain-loss-amount", plugin).text(gl_amount_sign + gl_amount);
                    $(".total-fee-amount", plugin).text('-' + parseFloat(data.feeAmount).toFixed(2)).addClass('down-amount');

                    if (t_amount >= 0) {
                        $(".total-amount", plugin).removeClass('down-amount').addClass('up-amount');
                    } else {
                        $(".total-amount", plugin).removeClass('up-amount').addClass('down-amount');
                    }

                    if (gl_amount >= 0) {
                        $(".gain-loss-amount", plugin).removeClass('down-amount').addClass('up-amount');
                    } else {
                        $(".gain-loss-amount", plugin).removeClass('up-amount').addClass('down-amount');
                    }

                    plugin.prop('options').chartOptions = data.chartOptions;
                    plugin.prop('methods').showChats();
                } else {
                    var options = plugin.prop('options').chartOptions;
                    options.series = [];

                    $('input[name="investAmount"]', plugin).val(null);
                    $('input[name="startDate"]', plugin).val(null);
                    $('select[name="performanceFee"]', plugin).val(0);

                    plugin.prop('options').chartOptions = options;
                    plugin.prop('methods').showChats();

                    $('.chart-panel', plugin).text('Calculation Result Graph');

                    $(".response-panel .role-text", plugin).each(function () {
                        $(this).text('0.00').removeClass('down-amount').removeClass('up-amount');
                    });
                }
            },
            showChats: function () {
                var chart = $('.chart-panel', plugin);
                Highcharts.chart(chart[0], plugin.prop('options').chartOptions);
            },
            validate: function (display) {
                var valid = true;
                display = (typeof display != 'undefined') ? display : true;
                
                $("form input, form select", plugin).each(function () {
                    if (!$(this).val().length || $(this).val() == 0) {
                        valid = false;
                        if(display){
                            $(this).addClass('valid-error');  
                        }
                    }
                });
                return valid;
            }
        });

        plugin.prop('methods').init();
    }
})(jQuery);