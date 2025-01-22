

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

### Warning and Critical Series

If available the warning and critical series are disabled by default.
Rational behind this was, that often times these values are many times higher than
the perfdata values. This would cause the actual values to be almost invisible.

### Custom Variables

In order to ease integration with Icinga Directory, in which Icinga2 dictionary data types are currently
no the easiest to work with, we decided to use "flat" data types where possible (e.g. `perfdatagraphs_config_disable`).

However, for the `perfdatagraphs_metrics` variable a dictionary is the natural fit and "flat" data types
would have increased the complexity of the code base.

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
