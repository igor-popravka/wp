(function ($) {
    function getChartOptions(options) {
        return {
            credits: {
                enabled: false
            },
            chart: {
                backgroundColor: options.backgroundColor,
                width: 579
            },
            title: {
                text: ""
            },
            tooltip: {
                valuePrefix: "$"
            },
            xAxis: {
                categories: options.categories
            },
            yAxis: {
                title: {
                    text: ""
                },
                labels: {
                    format: "${value}"
                },
                gridLineColor: options.gridLineColor
            },
            legend: {
                enabled: true
            },
            series: options.series
        };
    }

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
                var el = $('<div>').css({display: "none"}).appendTo(context),
                    chart_options = getChartOptions(opt);


                //chart_options.series = context.data('chart_series');

                var chart = Highcharts.chart(el[0], chart_options);

                /*chart.xAxis[0].setCategories(series.categories);
                chart.series[0].setData(series.total_amount_data);
                chart.series[1].setData(series.gain_amount_data);
                chart.series[2].setData(series.fee_amount_data);*/

                el.dialog({
                    title: "Calculation result into graph",
                    width: chart_options.chart.width + 50
                });
            //}
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
            $.post(opt.adminUrl, $.extend({
                action: 'wdip-calculate-growth-data',
                accountId: opt.accountId,
                serviceClient: opt.serviceClient
            }, context.data('post_data')), function (result) {
                if (result.success) {
                    $(".total-amount", context).text(result.data.total_amount);
                    $(".gain-amount", context).text(result.data.gain_amount);
                    $(".fee-amount", context).text(result.data.fee_amount);

                    opt.categories = result.data.categories;
                    opt.series = result.data.series;
                    opt.backgroundColor = result.data.backgroundColor;
                    opt.gridLineColor = result.data.gridLineColor;
                } else {
                    $(".wdip-field", context).each(function () {
                        $(this).text('$0.00');
                    });
                }
            });
            return false;
        });
    }
})(jQuery);