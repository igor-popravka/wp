(function ($) {
    var plugin = {
        init: function (options) {
            plugin.normaliseSeriesData(options);
            Highcharts.chart(options.uid, plugin.getChartOptions(options));
        },
        normaliseSeriesData: function (options) {
            $(options.series).each(function (i, sr) {
                $(sr.data).each(function (j, dt) {
                    dt.x = Date.UTC(dt.Y, dt.M, dt.D);
                });
            });
        },
        getChartOptions: function (options) {
           return plugin.getDailyGainOptions(options)
        },
        getDailyGainOptions: function (options) {
            return {
                credits: {
                    enabled: false
                },
                chart: {
                    backgroundColor: options.bgcolor || null,
                    type: 'column',
                    zoomType: 'x',
                    height: options.height || null,
                    width: options.width || null,
                    spacingBottom: 25
                },
                title: {
                    text: options.title || ''
                },
                subtitle: {
                    text: ((typeof options.filter != 'undefined') && options.filter == 1) ? 'buttonHTMLOwner()' : '',
                    useHTML: true,
                    align: "right"
                },
                xAxis: {
                    tickmarkPlacement: 'on',
                    gridLineWidth: 1,
                    gridLineColor: options.gridcolor || '#7A7F87',
                    gridLineDashStyle: 'dot',
                    type: 'datetime',
                    tickInterval: options.month_tick_interval, // 1 months
                    crosshair: true
                },
                yAxis: {
                    gridLineColor: options.gridcolor || '#7A7F87',
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