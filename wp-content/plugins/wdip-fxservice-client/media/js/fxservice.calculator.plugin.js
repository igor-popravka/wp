(function ($) {
    $.fn.FXServiceCalculator = function (options) {
        var plugin = this;
        plugin.prop('options', options);
        plugin.prop('params', {});
        plugin.prop('methods', {
            init: function () {
                var select = $('select[name="interestRate"]', plugin),
                    submit = $('input[type="submit"]', plugin);

                $(plugin.prop('options').feeList).each(function (i, val) {
                    select.append("<option value=\"" + parseFloat(val / 100).toFixed(2) + "\">" + val + "%</option>\n")
                });

                select.on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                $('input[name="startDate"]', plugin).datepicker({
                    dateFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true
                }).on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                $('input[name="investAmount"]', plugin).on('change', function () {
                    plugin.prop('methods').saveParam($(this).attr('name'), $(this).val());
                }).on('focus', function () {
                    $(this).removeClass('valid-error');
                });

                $('form', plugin).submit(function (e) {
                    e.preventDefault();

                    if (plugin.prop('methods').validate()) {
                        submit.val('Please wait...').attr('disabled', true);

                        $.post(
                            plugin.prop('options').adminUrl,
                            $.extend({
                                action: 'wdip-calculate-growth-data',
                                accountId: plugin.prop('options').accountId,
                                serviceClient: plugin.prop('options').serviceClient
                            }, plugin.prop('params')),
                            function (result) {
                                var data = result.success ? result.data : {};
                                plugin.prop('methods').update(data);
                                submit.val('Calculate').attr('disabled', null);
                            }
                        );
                    }

                    return false;
                });
            },
            saveParam: function (name, value) {
                plugin.prop('params')[name] = value;
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
                    $(".response-panel .role-text", plugin).each(function () {
                        $(this).text('0.00').removeClass('down-amount').removeClass('up-amount');
                    });
                }
            },
            showChats: function () {
                var chart = $('.chart-panel', plugin);
                Highcharts.chart(chart[0], plugin.prop('options').chartOptions);
            },
            validate: function () {
                var valid = true;
                $("form input, form select", plugin).each(function () {
                    if (!$(this).val().length || $(this).val() == 0) {
                        valid = false;
                        $(this).addClass('valid-error');
                    }
                });
                return valid;
            }
        });

        plugin.prop('methods').init();
    }
})(jQuery);