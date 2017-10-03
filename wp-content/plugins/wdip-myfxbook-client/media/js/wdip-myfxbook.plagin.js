(function ($) {
    var plugin = {
        init: function (options) {
            Highcharts.chart($(this).attr('id'), plugin.getChartOptions(options));
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
                    text: '',
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
                    text: '',
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