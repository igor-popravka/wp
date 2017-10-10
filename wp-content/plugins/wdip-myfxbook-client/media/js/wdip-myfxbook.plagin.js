(function ($) {
    var plugin = {
        init: function (options) {
            var chart_options = plugin.getChartOptions(options),
                context = $(this),
                min = max = null,
                cart_data = chart_options.series[0].data,
                chart = Highcharts.chart(context.attr('id'), chart_options);

            $(cart_data).each(function (i, row) {
                min = min || row[0];
                max = max || row[0];

                min = Math.min(min, row[0]);
                max = Math.max(max, row[0]);
            });

            plugin.renderControlLabel(min, max, context, chart);

            $(".slider-control", context).slider({
                range: true,
                min: min,
                max: max,
                values: [min, max],
                slide: function (event, ui) {
                    plugin.renderControlLabel(ui.values[0], ui.values[1], context);

                    var dataRange = [];
                    $(cart_data).each(function (i, row) {
                        if (row[0] >= ui.values[0] && row[0] <= ui.values[1]) {
                            dataRange.push(row);
                        }
                    });

                    chart.series[0].setData(dataRange);
                }
            });

            $(".button-months", context).each(function () {
                $(this).click(function () {
                    $(".button-months", context).each(function () {
                        $(this).css("background-color", "rgba(68, 149, 204, 0.85)");
                    });

                    var role = $(this).attr('role'),
                        from = false,
                        date = new Date(max),
                        dataRange = [];

                    switch (role) {
                        case 'last-6-months':
                            date.setMonth(date.getMonth() - 5);
                            from = Date.UTC(date.getFullYear(), date.getMonth(), date.getDay());
                            break;
                        case 'last-12-months':
                            date.setMonth(date.getMonth() - 11);
                            from = Date.UTC(date.getFullYear(), date.getMonth(), date.getDay());
                    }

                    $(cart_data).each(function (i, row) {
                        if (from !== false) {
                            if (row[0] > from) {
                                dataRange.push(row);
                            }
                        } else {
                            dataRange.push(row);
                        }
                    });

                    chart.series[0].setData(dataRange);

                    $(this).css("background-color", "rgba(68, 149, 204, 1)");
                });
            })
        },

        renderControlLabel: function (min, max, context, chart) {
            if ($('.label-control', context).length) {
                var minDate = new Date(min),
                    maxDate = new Date(max),
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
                    <button class="button-months" role="last-6-months" >Last 6 months</button>\
                    <button class="button-months" role="last-12-months" >Last 12 months</button>\
                    <button class="button-months" role="all-months">All months</button>\
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
                    type: 'datetime',
                    tickInterval: options.monthtickinterval,
                    crosshair: true
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