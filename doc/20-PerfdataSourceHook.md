# Writing a PerfdataSourceHook

In order to write a custom backend for the Icinga Web Module for Performance Data Graphs you need to implement
PerfdataSource hook provided by the module.

## Input parameters for fetching data

The hook requires the following methods:

`public function getName(): string;`

The `getName()` must return a descriptive name for the backend. This is used in the configuration page for example.

`public function fetchData(string $hostName, string $serviceName, string $checkCommand, string $duration, array $metrics): array;`

The `fetchData()` must return the data that is rendered into charts.

Parameters:

* `$hostName` is the host name for the performance data query
* `$serviceName` is the service name for the performance data query
* `$checkCommand` is the checkcommand name for the performance data query
* `$duration` for which to fetch the data for in PHP's [DateInterval](https://www.php.net/manual/en/class.dateinterval.php) format (e.g. PT12H, P1D, P1Y)
   * This can be used to calculate the time range that the user requested. The current timestamp as a starting point is implicit
* `$metrics` a list of metrics (preformance data labels) that are requested, if not set all available metrics should be returned

## Return value for fetching data

The section describes the return value of the `fetchData()` method.

The returned PHP array contains objects that describe which describe the chart.

Each chart object has the following attributes:

* `title` is the title that will be displayed for this chart
* `timestamps` is a list of UNIX epoch timestamps in seconds, used for the x-axis
* `series` is an array of dataset objects that are used for the y-axes

Each series object has the following attributes:

* `name` is the name that will be displayed for this series
* `data` is a list of data points for this series

Example chart and series object:

```json
  {
    "title": "available_upgrades",
    "timestamps": [
      1737623700,
      1737623800
    ],
    "series": [
      {
        "name": "value",
        "data": [
          20,
          18
        ]
      }
    ]
  }
```

Full example:

```json
[
  {
    "title": "available_upgrades",
    "timestamps": [
      1737623700,
      1737623800
    ],
    "series": [
      {
        "name": "value",
        "data": [
          20,
          18
        ]
      }
    ]
  },
  {
    "title": "critical_updates",
    "timestamps": [
      1737623700,
      1737623800
    ],
    "series": [
      {
        "name": "value",
        "data": [
          10,
          12
        ]
      }
    ]
  }
]
```
