# Icinga Web Performance Data Graphs

Icinga Web Module for Performance Data Graphs. This module enables graphs on the Host and Service Detail View for
the respective performance data.

The data is fetched by a "backend module", these backend modules also need to be enabled.

![Graphs Light](doc/_images/screenshot_light.png)
![Graphs Dark](doc/_images/screenshot_dark.png)

This module aims to be a "batteries included" and opinionated solution.
Configuration options are limited by design.

## Features

* Interactive graphs for Host and Service performance data
  * Mouse click and select a region to zoom in
  * Click on a time range or double click to zoom out
* Interchangeable performance data backends
  * Fetched data is cached to improve speed and reduce load on the backend
* Graphs are adjustable via Icinga 2 custom variables

## Design Decisions

Here are some of our design decisions in order to understand why the module works the way it does.
This should also be used as a reference for future development.

### A chart for each metric

There is a seperate chart for each metric because the magnitude of performance data for a single check plugin
can vary widely. Meaning one metric could be double digits and another only fractions.
Custom configuration for when metrics should be combined in a single chart would highly
increase the complexity of this module.

### Fixed time range selection

We decided to have a fixed set of time range to choose from.
Having user input for the time ranges would increase the complexity of this module.

### Warning and critical series

If available the warning and critical series are disabled by default.
Rationale behind this was, that often times these values are many times higher than
the perfdata values. This would cause the actual values to be almost invisible.

### Custom variables

In order to ease integration with Icinga Directory, in which Icinga2 dictionary data types are currently
no the easiest to work with, we decided to use "flat" data types where possible (e.g. `perfdatagraphs_config_disable`).

However, for the `perfdatagraphs_metrics` variable a dictionary is the natural fit and "flat" data types
would have increased the complexity of the code base.

### Missing data

Missing data is not shown in the charts, this might cause gaps in the rendering.
We will not take any steps to hide these or provide a default in case of missing data.
Rationale behind this was, to transparently show the incomplete data and avoid
wrong interpretation when data is set to a default value.

## Units

Values for the y-axis are automatically transformed into the following metric (SI) prefixes:

| Prefix  | Symbol |
|---------|--------|
| Yotta   | Y      |
| Zetta   | Z      |
| Exa     | E      |
| Peta    | P      |
| Tera    | T      |
| Giga    | G      |
| Mega    | M      |
| Kilo    | k      |
| (Base)  |        |
| Milli   | m      |
| Micro   | µ      |
| Nano    | n      |
| Pico    | p      |
| Femto   | f      |
| Atto    | a      |
| Zepto   | z      |
| Yocto   | y      |
