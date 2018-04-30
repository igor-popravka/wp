(function ($) {
    $.fn.FXServiceCalculator = function (options) {
        var plugin = this;

        plugin.prop('options', options);
        plugin.prop('params', {});
        plugin.prop('methods', {
            init: function () {
                var options = plugin.prop('options'),
                    fee = $('select[name="performanceFee"]', plugin),
                    start = $('input[name="startDate"]', plugin),
                    invest = $('input[name="investAmount"]', plugin),
                    submit = $('input[type="submit"]', plugin),
                    __chart_panel = $('#fxs-calc-charts-' + options.uid);

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

                __chart_panel.tabs();

                $('a[href="#total-chart-' + options.uid + '"]', __chart_panel).on('click', function () {
                    Highcharts.chart($($(this).attr('href'))[0], options.totalChartOptions);
                });

                $('a[href="#gl-chart-' + options.uid + '"]', __chart_panel).on('click', function () {
                    Highcharts.chart($($(this).attr('href'))[0], options.glChartOptions);
                });

                $('a[href="#fee-chart-' + options.uid + '"]', __chart_panel).on('click', function () {
                    Highcharts.chart($($(this).attr('href'))[0], options.feeChartOptions);
                });
            },
            saveParam: function (name, value) {
                plugin.prop('params')[name] = value;
            },
            calculate: function (callback) {
                var options = plugin.prop('options');

                plugin.prop('methods').switchCalcContent('spinner');

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

                        if ($.isFunction(callback)) {
                            callback();
                        }
                    }
                );
            },
            update: function (data) {
                var hasDataTotalChart = !$.isEmptyObject(data) && !$.isEmptyObject(data.totalChartOptions) && data.totalChartOptions.series["0"].data.length > 0,
                    hasDataGLChartOptions = !$.isEmptyObject(data) && !$.isEmptyObject(data.glChartOptions) && data.glChartOptions.series["0"].data.length > 0 ,
                    hasDataFeeChartOptions = !$.isEmptyObject(data) && !$.isEmptyObject(data.feeChartOptions) && data.feeChartOptions.series["0"].data.length > 0;

                if (hasDataTotalChart || hasDataGLChartOptions || hasDataFeeChartOptions) {
                    plugin.prop('methods').switchCalcContent('charts');

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

                    plugin.prop('options').totalChartOptions = data.totalChartOptions;
                    plugin.prop('options').glChartOptions = data.glChartOptions;
                    plugin.prop('options').feeChartOptions = data.feeChartOptions;

                    $('a[href^="#total-chart-"]', plugin).click();
                } else {
                    plugin.prop('methods').switchCalcContent();
                    plugin.prop('options').totalChartOptions.series = [];
                    plugin.prop('options').glChartOptions.series = [];
                    plugin.prop('options').feeChartOptions.series = [];

                    $('input[name="investAmount"]', plugin).val(null).removeClass('valid-error');
                    $('input[name="startDate"]', plugin).val(null).removeClass('valid-error');
                    $('select[name="performanceFee"]', plugin).val(null).removeClass('valid-error');

                    $('a[href^="#total-chart-"]', plugin).click();

                    $(".response-panel .role-text", plugin).each(function () {
                        $(this).text('0.00').removeClass('down-amount').removeClass('up-amount');
                    });
                }
            },
            switchCalcContent: function (select) {
                var __opt = plugin.prop('options'),
                    __default_text = $('#fxs-calc-default-text-' + __opt.uid, plugin),
                    __spinner = $('#fxs-calc-spinner-' + __opt.uid, plugin),
                    __charts = $('#fxs-calc-charts-' + __opt.uid, plugin);

                switch (select) {
                    case 'spinner':
                        __default_text.attr('hidden', true);
                        __spinner.attr('hidden', null);
                        __charts.attr('hidden', true);
                        break;
                    case 'charts':
                        __default_text.attr('hidden', true);
                        __spinner.attr('hidden', true);
                        __charts.attr('hidden', null);
                        break;
                    case 'default-text':
                    default:
                        __default_text.attr('hidden', null);
                        __spinner.attr('hidden', true);
                        __charts.attr('hidden', true);

                }
            },
            validate: function (display) {
                display = $.type(display) === "boolean" ? display : true;
                var __valid = true,
                    __opt = plugin.prop('options'),
                    __invest_amount = $('#fxs-calc-invest-amount-' + __opt.uid),
                    __start_date = $('#fxs-calc-start-date-' + __opt.uid),
                    __performance_fee = $('#fxs-calc-performance-fee-' + __opt.uid);

                if (!__invest_amount.val()) {
                    __valid = false;
                    if (display) {
                        __invest_amount.addClass('valid-error');
                    }
                }

                if (!__start_date.val()) {
                    __valid = false;
                    if (display) {
                        __start_date.addClass('valid-error');
                    }
                }

                if (!__performance_fee.val()) {
                    __valid = false;
                    if (display) {
                        __performance_fee.addClass('valid-error');
                    }
                }
                return __valid;
            }
        });

        plugin.prop('methods').init();
    }
})(jQuery);