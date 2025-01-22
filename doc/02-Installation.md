# Installation

The installation includes this module, which is responsible for rendering the data
and a "backend module", which is responsible for fetching the data from a performance data backend (Graphite, OpenSearch, Elasticsearch, InfluxDB, etc.)

## From source

1. Clone the Icinga Web Performance Data Graphs repository into `/usr/share/icingaweb2/modules/perfdatagraphs`

2. Clone a Icinga Web Performance Data Graphs Backend repository into `/usr/share/icingaweb2/modules/perfdatagraphsgraphite`

3. Enable both modules using the `Configuration → Modules` menu

4. (optionally) Configure specific graphs via Icinga2 Custom Variables
