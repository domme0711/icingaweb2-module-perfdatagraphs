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

## Documentation

Documentation for this module is available at [doc](doc/).
