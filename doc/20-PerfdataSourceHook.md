# Writing a Performance Data Backend

In order to write a custom backend for the Icinga Web Module for Performance Data Graphs you need to implement
the PerfdataSource hook provided by this module.

⚠️ The Hook is still being worked on and is likely to change.

## Data model

The hook relies on the following data model:

* PerfdataRequest, contains everything a backend needs to fetch data (e.g. host, service, checkcommand)
* PerfdataResponse, contains the data returned from a backend which is then rendered into charts
  * PerfdataSeries, represents a single series (y-axis) on the chart
  * PerfdataSet, represents a single chart in the frontend

## PerfdataSourceHook

The hook requires the following methods:

`public function getName(): string;`

The `getName()` method returns a descriptive name for the backend. This is used in the configuration page for example.

`public function fetchData(PerfdataRequest $req): PerfdataResponse;`

The `fetchData()` method returns the data that is rendered into charts.

### PerfdataRequest

The `PerfdataRequest` contains the following data:

* `string $hostName` host name for the performance data query
* `string $serviceName` service name for the performance data query
* `string $checkCommand` checkcommand name for the performance data query
* `string $duration` duration for which to fetch the data for in PHP's [DateInterval](https://www.php.net/manual/en/class.dateinterval.php) format (e.g. PT12H, P1D, P1Y)
* `bool $isHostCheck` is this a Host or Service Check that is requested. Backends queries might differ for these.
* `array $includeMetrics` a list of metrics that are requested, if not set all available metrics should be returned
* `array $excludeMetrics` a list of metrics should be excluded from the results, if not set no metrics should be excluded

ISO8601 durations are used because:

1. it provides a simple and parsable format to send via URL parameters
2. PHP offers a native function to parse the format
3. each backend has different requirements for the time range format, ISO8601 durations provide common ground.

This can be used to calculate the time range that the user requested. The current timestamp as a starting point is implicit.

### PerfdataResponse

The `PerfdataResponse` is a `JsonSerializable` that we use to render the charts.

This object a list of `PerfdataSet` and a list
of errors, which are used to communicate issues to the frontend.

Each `PerfdataSet` must contain the timestamps for the x-axis.
It also must contain at least `PerfdataSeries` with values for the y-axis.
It may contain additional `PerfdataSeries` for the y-axis (e.g. `warning`, `critical`).

Example:

```json
{ "data": [
  {
    "title": "available_upgrades",
    "timestamps": [
      1737623700,
      1737623800
    ],
    "series": [
      {
        "name": "value",
        "values": [
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
        "values": [
          10,
          12
        ]
      }
    ]
  }
]}
```

Example error:

```json
{
  "data": {},
  "error": {"message": "Something went wrong"}
}
```
