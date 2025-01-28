# Writing a PerfdataSourceHook

In order to write a custom backend for the Icinga Web Module for Performance Data Graphs you need to implement
PerfdataSource hook provided by the module.

⚠️ The return value for the Hook is still being worked on and is likely to change.

## Input parameters for fetching data

The hook requires the following methods:

`public function getName(): string;`

The `getName()` must return a descriptive name for the backend. This is used in the configuration page for example.

`public function fetchData(string $hostName, string $serviceName, string $checkCommand, string $duration, array $metrics): PerfdataResponse;`

The `fetchData()` must return the data that is rendered into charts.

Parameters:

* `$hostName` is the host name for the performance data query
* `$serviceName` is the service name for the performance data query
* `$checkCommand` is the checkcommand name for the performance data query
* `$duration` for which to fetch the data for in PHP's [DateInterval](https://www.php.net/manual/en/class.dateinterval.php) format (e.g. PT12H, P1D, P1Y)
   * This can be used to calculate the time range that the user requested. The current timestamp as a starting point is implicit
* `$metrics` a list of metrics (performance data labels) that are requested, if not set all available metrics should be returned

## Return value for fetching data

The section describes the return value of the `fetchData()` method.

The method must return a `Icinga\Module\Perfdatagraphs\Model\PerfdataResponse`.

This object a list of `Icinga\Module\Perfdatagraphs\Model\PerfdataSet` and a list
of errors, which are used to communicate issues with the frontend.

Each `PerfdataSet` must contain the timestamps for the x-axis.
It also must contain a `Icinga\Module\Perfdatagraphs\Model\PerfdataSeries` for
the y-axis. It may contain additional `PerfdataSeries` for the y-axis (e.g. warning, critical).

Full example:

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
