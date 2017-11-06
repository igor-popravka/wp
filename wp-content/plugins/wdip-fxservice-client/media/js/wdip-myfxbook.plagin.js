(function ($) {
    var plugin = {
        init: function (options) {
            switch (options.charttype) {
                case 'month-growth':
                case 'monthly-gain-loss':
                    $.proxy(plugin.initColumnsChart, this, options)();
                    break;
                case 'total-growth':
                    $.proxy(plugin.initLineChart, this, options)();
            }
        },

        initColumnsChart: function (options) {
            var chartOptions = plugin.getMonthGrowthOptions(options),
                chart = Highcharts.chart($(this).attr('id'), chartOptions);

            $(this).prop('seriesData', options.series[0].data);
            $(this).prop('xAxisCategories', options.categories);

            $(".button-months.time-tick-6", $(this)).click($.proxy(plugin.applyButtonFilter, $(this), chart, 6));
            $(".button-months.time-tick-12", $(this)).click($.proxy(plugin.applyButtonFilter, $(this), chart, 12));
            $(".button-months.time-tick-all", $(this)).click($.proxy(plugin.applyButtonFilter, $(this), chart, 'all'));
        },

        initLineChart: function (options) {
            var chartOptions = plugin.getTotalGrowthOptions(options),
                chart = Highcharts.chart($(this).attr('id'), chartOptions);

            $(this).prop('seriesData', options.series[0].data);

            $.proxy(plugin.renderControlLabel, $(this), chart)();
            $(".slider-control", $(this)).slider($.proxy(plugin.getSliderFilterOptions, $(this), chart)());
        },

        applyButtonFilter: function (chart, interval) {
            $(".button-months", this).each(function () {
                $(this).css("background-color", "rgba(68, 149, 204, 0.85)");
            });

            var seriesData = $(this).prop('seriesData'),
                xAxisCategories = $(this).prop('xAxisCategories'),
                rangeData = [],
                rangeCategories = [],
                count = 1;

            if (interval !== 'all') {
                do {
                    rangeData.unshift(seriesData[seriesData.length - count]);
                    rangeCategories.unshift(xAxisCategories[xAxisCategories.length - count]);
                    count++;
                } while (count <= interval);
            } else {
                rangeData = seriesData;
                rangeCategories = xAxisCategories;
            }

            chart.series[0].setData(rangeData);
            chart.xAxis[0].setCategories(rangeCategories);

            $(".button-months.time-tick-" + interval, this).css("background-color", "rgba(68, 149, 204, 1)");
        },

        getSliderFilterOptions: function (chart) {
            var context = $(this),
                seriesData = context.prop('seriesData'),
                minTimeTick = seriesData[0][0],
                maxTimeTick = seriesData[seriesData.length - 1][0];

            return {
                range: true,
                min: minTimeTick,
                max: maxTimeTick,
                values: [minTimeTick, maxTimeTick],
                slide: function (event, ui) {
                    $.proxy(plugin.renderControlLabel, context, chart, ui.values[0], ui.values[1])();

                    var rangeData = [];
                    $(seriesData).each(function (i, row) {
                        if (row[0] >= ui.values[0] && row[0] <= ui.values[1]) {
                            rangeData.push(row);
                        }
                    });

                    chart.series[0].setData(rangeData);
                }
            }
        },

        renderControlLabel: function (chart, minTimeTick, maxTimeTick) {
            var context = $(this),
                seriesData = context.prop('seriesData'),
                minTimeTick = minTimeTick || seriesData[0][0],
                maxTimeTick = maxTimeTick || seriesData[seriesData.length - 1][0];

            if ($('.label-control', context).length) {
                var minDate = new Date(minTimeTick),
                    maxDate = new Date(maxTimeTick),
                    locale = "en-us",
                    minMonth = minDate.toLocaleDateString(locale, {month: 'short', year: '2-digit', day: 'numeric'}),
                    maxMonth = maxDate.toLocaleDateString(locale, {month: 'short', year: '2-digit', day: 'numeric'});

                $('.label-control.left', context).text(minMonth);
                $('.label-control.right', context).text(maxMonth);

                if (typeof chart != 'undefined') {
                    chart.redraw();
                }
            }
        },

        buttonHTMLOwner: function () {
            return '<div class="chart-button-control">\
                    <button class="button-months time-tick-6">Last 6 months</button>\
                    <button class="button-months time-tick-12">Last 12 months</button>\
                    <button class="button-months time-tick-all">All months</button>\
                </div>';
        },

        sliderHTMLOwner: function () {
            return '<div class="chart-range-control">\
                    <div class="label-control left"></div>\
                    <div class="slider-control"></div>\
                    <div class="label-control right"></div>\
                </div>';
        },

        getChartOptions: function (options) {
            switch (options.charttype) {
                case 'month-growth':
                case 'monthly-gain-loss':
                    return plugin.getMonthGrowthOptions(options);
                case 'total-growth':
                    return plugin.getTotalGrowthOptions(options);
            }
            return {}
        },

        getMonthGrowthOptions: function (options) {
            return {
                credits: {
                    enabled: false
                },
                chart: {
                    backgroundColor: options.backgroundcolor || null,
                    type: 'column',
                    zoomType: 'x',
                    height: options.chartheight || null,
                    width: options.chartwidth || null,
                    spacingBottom: 25
                },
                title: {
                    text: options.title || ''
                },
                subtitle: {
                    text: plugin.buttonHTMLOwner(),
                    useHTML: true,
                    align: "right"
                },
                xAxis: {
                    tickmarkPlacement: 'on',
                    gridLineWidth: 1,
                    gridLineColor: options.gridlinecolor || '#7A7F87',
                    gridLineDashStyle: 'dot',
                    categories: options.categories
                },
                yAxis: {
                    gridLineColor: options.gridlinecolor || '#7A7F87',
                    title: {text: ''},
                    labels: {
                        formatter: function () {
                            return this.value + '%';
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        shadow: true,
                        borderRadius: 3,
                        borderWidth: 1
                    }
                },
                tooltip: {
                    valueSuffix: '%'
                },
                series: options.series
            };
        },

        getTotalGrowthOptions: function (options) {
            return {
                lang: {
                    rangeSelectorZoom: ''
                },
                credits: {
                    enabled: false
                },
                chart: {
                    backgroundColor: options.backgroundcolor || null,
                    type: 'areaspline',
                    zoomType: 'x',
                    height: options.chartheight || null,
                    width: options.chartwidth || null,
                    spacingBottom: 25
                },
                title: {
                    text: options.title || ''
                },
                subtitle: {
                    text: plugin.sliderHTMLOwner(),
                    useHTML: true,
                    align: "center"
                },
                tooltip: {
                    valueSuffix: '%'
                },
                xAxis: {
                    tickmarkPlacement: 'on',
                    gridLineWidth: 1,
                    gridLineColor: options.gridlinecolor || '#7A7F87',
                    gridLineDashStyle: 'dot',
                    type: 'datetime',
                    tickInterval: options.monthtickinterval,
                    labels: {
                        formatter: function () {
                            var dt = new Date(this.value),
                                local = 'en-US';
                            return dt.toLocaleDateString(local, {month: 'short', year: '2-digit', day: 'numeric'});
                        }
                    }
                },
                yAxis: {
                    gridLineColor: options.gridlinecolor || '#7A7F87',
                    title: {
                        text: ''

                    },
                    labels: {
                        formatter: function () {
                            return this.value + '%';
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillColor: {
                            linearGradient: [0, 0, 0, 240],
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                            ]
                        },
                        lineWidth: 2,
                        states: {
                            hover: {
                                lineWidth: 3
                            }
                        },
                        threshold: null
                    },
                    allowPointSelect: true

                },
                series: options.series
            };
        }
    };

    $.fn.myFxBook = function (method) {
        if (plugin[method]) {
            return plugin[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return plugin.init.apply(this, arguments);
        } else {
            $.error("Method with name " + method + " doesn't exist for jQuery.tagsMultiSelect");
        }
    };
})(jQuery);