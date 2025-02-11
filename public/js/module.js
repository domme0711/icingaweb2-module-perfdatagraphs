;(function(Icinga, $) {

    'use strict';

    // The element in which we will add the charts
    const CHARTCLASS = '.line-chart';
    // The element in which we will show errors
    const CHARTERRORCLASS = 'p.line-chart-error';
    // Enpoint to fetch the data from
    const FETCH_ENDPOINT = '/perfdatagraphs/fetch';

    class Perfdatagraphs extends Icinga.EventListener {
        // data contains the fetched chart data with the element ID where it is rendered as key.
        // Where we store data inbetween the autorefresh.
        data = new Map();
        // plots contains the chart objects with the element ID where it is rendered as key.
        // Used for resizing the charts.
        plots = new Map();
        // Where we store data inbetween autorefresh
        currentSelect = null;
        currentCursor = null;
        currentSeriesShow = {1: true};
        // Where we store the variable selected and the constant timerange
        duration = '';
        defaultDuration = '';

        constructor(icinga)
        {
            super(icinga);

            // We register a ResizeObserver so that we can resize the charts
            // when their respective .container changes size.
            this.resizeObserver = new ResizeObserver(entries => {
                for (let elem of entries) {
                    const _plots = this.plots.get(elem.target) ?? [];
                    const s = this.getChartSize(elem.contentRect.width);
                    for (let u of _plots) {
                        u.setSize(s);
                    }
                }
            });

            this.on('rendered', '#main > .icinga-module', this.rendered, this);
            this.on('click', '.perfdata-charts a.action-link[data-duration]', this.onTimeClick, this);
        }

        /**
         * rendered makes sure the data is available and then renderes the charts
         */
        rendered(event, isAutorefresh)
        {
            let _this = event.data.self;

            // This elements contains the configured default timerange from the
            // module's configuration.
            const elem = document.getElementById('perfdatagraphs-default-timerange');
            const defaultduration = elem.getAttribute('data-duration');
            this.duration = defaultduration;
            this.defaultDuration = defaultduration;

            if (!isAutorefresh) {
                // Reset the selection and set the duration when it's
                // an autorefresh and new data is being loaded.
                // _this.currentSelect = {min: 0, max: 0};
                _this.currentSelect = null;
                _this.currentSeriesShow = {1: true};
                _this.currentCursor = null;
                _this.duration = this.defaultDuration;
            }

            // Now we fetch and render
            _this.fetchData();
            _this.renderCharts();
        }

        /**
         * onTimeClick retrieves the requested duration and calls the rendered.
         */
        onTimeClick(event)
        {
            let _this = event.data.self;
            let target = event.currentTarget;

            const duration = target.getAttribute('data-duration');

            // Reset the selection and set the duration.
            // These need to be stored inbetween the autorefresh
            _this.currentSelect = {min: 0, max: 0};
            _this.duration = duration;

            // Now we fetch and render
            _this.fetchData();
            _this.renderCharts();
        }


        /**
         * formatNumber transforms numbers into metric (SI) prefix notation. Used in the y axis.
         */
        formatNumber(n)
        {
            const unitList = ['y', 'z', 'a', 'f', 'p', 'n', 'μ', 'm', '', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
            const zeroIndex = 8;
            const nn = n.toExponential(2).split(/e/);
            let u = Math.floor(+nn[1] / 3) + zeroIndex;
            if (u > unitList.length - 1) {
                u = unitList.length - 1;
            } else
                if (u < 0) {
                    u = 0;
                }
            return nn[0] * Math.pow(10, +nn[1] - (u - zeroIndex) * 3) + unitList[u];
        }

        /**
         * ensureArray ensures the given object is an Array.
         * It will transform Objects if possible.
         * A dirty PHP 8.0 hack since I sometimes used SplFixedArray.
         * Can be removed once PHP 8.0 is ancient history.
         */
        ensureArray(obj) {
            if (typeof obj === 'object' && !Array.isArray(obj)) {
                return Object.values(obj);
            }

            return obj;
        }

        /**
         * Translate a given rgb() string into rgba().
         * Used for the fill of the chart.
         */
        ensureRgba(color, alpha=1) {
            // If already in rgba just return.
            if (color.startsWith('rgba')) {
                return color;
            }

            // Try to match the rgb format and return with alpha.
            const rgbMatch = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            if (!rgbMatch) {
                // If we match nothing return what was given just to be safe.
                return color;
            }

            // Add the given alpha.
            const [_, r, g, b] = rgbMatch;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        /**
         * getChartSize is used to recalculate the canvas size based on the
         * object-detail colum width.
         */
        getChartSize(width)
        {
            return {
                // Subtract some pixels to avoid flickering scollbar in Chrome
                // Maybe there's a better way?
                width: width - 10,
                height: 200,
            }
        }

        isValidData(data)
        {
            if (data === undefined || data.length === 0) {
                const errorMsg = $(CHARTERRORCLASS).attr('data-message-nodata');
                $(CHARTERRORCLASS).text(errorMsg).show();
                this.icinga.logger.warn('perfdatagraphs: no data received');
                return false;
            }

            if (data.errors !== undefined && data.errors.length > 0) {
                const errorMsg = $(CHARTERRORCLASS).attr('data-message-error');
                $(CHARTERRORCLASS).text(errorMsg +': '+ data.errors.join('; ')).show();
                this.icinga.logger.error('perfdatagraphs', data.errors.join('; '));
                return false;
            }

            if (data.data === undefined || data.data.length === 0) {
                const errorMsg = $(CHARTERRORCLASS).attr('data-message-nodata');
                $(CHARTERRORCLASS).text(errorMsg).show();
                this.icinga.logger.warn('perfdatagraphs: no data received');
                return false;
            }

            return true;
        }

        /**
         * fetchData tries to get the data for the given object from the Controller.
         */
        fetchData()
        {
            var _this = this;

            // Get the elements we going to render the charts in
            const lineCharts = document.querySelectorAll(CHARTCLASS);
            // Check if the elements exist, just to be safe
            if (lineCharts.length < 1) {
                return;
            }

            _this.icinga.logger.debug('perfdatagraphs', 'start fetchData', lineCharts);

            for (let elem of lineCharts) {
                // Get the parameters the Hooks added to the element
                const parameters = {
                    host: elem.getAttribute('data-host'),
                    service: elem.getAttribute('data-service'),
                    checkcommand: elem.getAttribute('data-checkcommand'),
                    duration: _this.duration,
                }

                // Make a request to the internal controller to get the data for the charts
                let req = $.ajax({
                    type: 'GET',
                    cache: true,
                    // I tried to have this async but that did cause some flickering when the data isn't loaded yet.
                    async: false,
                    url: this.icinga.config.baseUrl + FETCH_ENDPOINT,
                    data: parameters,
                    dataType: 'json',
                    error: function (request, status, error) {
                        // Just in case the fetch controller explodes on us.
                        // There might be a better way.
                        $('i.spinner').hide();
                        const el = $(request.responseText);
                        const errorMsg = $('p.error-message', el).text();
                        _this.icinga.logger.error('perfdatagraphs:', errorMsg);
                        $(CHARTERRORCLASS).text($(CHARTERRORCLASS).attr('data-message-error') + ': ' + errorMsg).show();
                    },
                    beforeSend: function() {
                        // We show the spinner when we fetch data
                        $('i.spinner').show();
                    },
                    success: function(data) {
                        // On success try rendering the chart
                        $('i.spinner').hide();
                        _this.icinga.logger.debug('perfdatagraphs', 'finish fetchData', data);

                        if (! _this.isValidData(data)) {
                            return;
                        }

                        $(CHARTERRORCLASS).hide()
                        _this.data.set(elem.getAttribute('id'), data.data);
                    }
                });
            }
        }

        /**
         * renderCharts creates the canvas objects given the provided datasets.
         * TODO: Maybe refactor and split up this method a bit.
         */
        renderCharts()
        {
            // Properties for the x and y axes
            const axesColor = $('div.axes-color').css('background-color');
            const warningColor = $('div.warning-color').css('background-color');
            const criticalColor = $('div.critical-color').css('background-color');
            const valueColor = $('div.value-color').css('background-color');

            // TODO: Dry refactor this at some point
            const xProperty = {
                stroke: axesColor,
                grid: {
                    stroke: axesColor,
                    width: 0.5,
                },
                ticks: {
                    stroke: axesColor,
                    width: 0.5,
                }
            }

            const yProperty = {
                stroke: axesColor,
                grid: {
                    stroke: axesColor,
                    width: 0.5,
                },
                ticks: {
                    stroke: axesColor,
                    width: 0.5,
                },
                values: (u, vals, space) => vals.map(v => this.formatNumber(v)),
            }

            // Reset the existing plots map for the new rendering
            this.plots = new Map();

            this.icinga.logger.debug('perfdatagraphs', 'start renderCharts', this.data);

            this.data.forEach((data, elemID, map) => {
                // Get the element in which we render the chart
                const elem = document.getElementById(elemID);

                if (elem === null) {
                    return;
                }

                // The options for each chart
                const opts = {
                    ...this.getChartSize(elem.offsetWidth),
                    cursor: {
                        sync: {
                            key: 0,
                            setSeries: true,
                        },
                    },
                    scales: {
                        x: {
                            time: true,
                        },
                    },
                    axes: [
                        xProperty,
                        yProperty,
                    ],
                    // series holds the config of each dataset, such as visibility, styling,
                    // labels & value display in the legend
                    series: [
                        {},
                    ],
                    hooks: {
                        init: [
                            u => {
                                u.over.ondblclick = e => {
                                    // We need to reset the currentSelect to the min/max
                                    // when we zoom out again.
                                    this.currentSelect = {min: 0, max: 0};
                                }
                            }
                        ],
                        setCursor: [
                            (u) => {
                                // We need to store the current cursor
                                // to refresh it when the autorefresh hits.
                                this.currentCursor = u.cursor;
                            }
                        ],
                        setSeries: [
                            (u, sidx) => {
                                // When series are toggled, we store the current option
                                // so that it can be restored when the Icinga Web autorefresh hits.
                                if (u.series[sidx] !== undefined) {
                                    this.currentSeriesShow[sidx] = u.series[sidx].show;
                                }
                            }
                        ],
                        setSelect: [
                            u => {
                                // When a select is performed, we store the current selection
                                // so that it can be restored when the Icinga Web autorefresh hits.
                                let _min = u.posToVal(u.select.left, 'x');
                                let _max = u.posToVal(u.select.left + u.select.width, 'x');
                                this.currentSelect = {min: _min, max: _max};
                            }
                        ]
                    }
                };

                // Add each element to the resize observer so that we can
                // resize the chart when its container changes
                this.resizeObserver.observe(elem);

                // Reset the existing canvas elements for the new rendering
                elem.replaceChildren();

                // Create a new uplot chart for each performance dataset
                data.forEach((dataset) => {
                    dataset.timestamps = this.ensureArray(dataset.timestamps);

                    // Add a new empty plot with a title for the dataset
                    opts.title = dataset.title;
                    opts.title += dataset.unit ? ' | ' + dataset.unit : '';
                    let u = new uPlot(opts, [], elem);
                    // Where we store the finished data for the chart
                    let d = [dataset.timestamps];

                    // Create the data for the plot and add the series
                    // Using a 'classic' for loop since we need the index
                    for (let idx = 0; idx < dataset.series.length; idx++) {
                        // // The series we are going to add (e.g. values, warn, crit, etc.)
                        let set = dataset.series[idx].values;
                        set = this.ensureArray(set);

                        // See if there are series options from the last autorefresh
                        // if so we use them, otherwise the default.
                        let show = this.currentSeriesShow[idx+1];
                        // Get the style either from the dataset or from CSS
                        let stroke = dataset.stroke ?? valueColor;
                        let fill = dataset.fill ?? this.ensureRgba(valueColor, 0.3);

                        // Add a new series to the plot. Need adjust the index, since 0 is the timestamps
                        if (dataset.series[idx].name === 'warning') {
                            stroke = warningColor;
                            fill = false;
                        }
                        if (dataset.series[idx].name === 'critical') {
                            stroke = criticalColor;
                            fill = false;
                        }

                        u.addSeries({
                            label: dataset.series[idx].name,
                            stroke: stroke,
                            fill: fill,
                            show: show,
                            // Override the default uplot callback so that smaller values are
                            // shown in the hover.
                            value: (self, rawValue) => rawValue,
                        }, idx+1);
                        // Add this to the final data for the chart
                        d.push(set);
                    }
                    // Add the data to the chart
                    u.setData(d);

                    // If a selection is stored we restore it.
                    if (this.currentSelect !== null) {
                        u.setScale('x', this.currentSelect);
                    }
                    // If a cursor is stored we restore it.
                    if (this.currentCursor !== null) {
                        u.setCursor(this.currentCursor);
                    }

                    // Add the chart to the map which we use for the resize observer
                    const _plots = this.plots.get(elem) || [];
                    _plots.push(u)
                    this.plots.set(elem, _plots);
                });
            });
            this.icinga.logger.debug('perfdatagraphs', 'finish renderCharts', this.plots);
        }
    }

    Icinga.Behaviors.Perfdatagraphs = Perfdatagraphs;

}(Icinga, jQuery));
