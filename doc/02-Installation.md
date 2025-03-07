# Installation

The installation includes this "frontend module", which is responsible for rendering the data
and a "backend module", which is responsible for fetching the data from a performance data backend (Graphite, OpenSearch, Elasticsearch, InfluxDB, etc.).

## From source

1. Clone the Icinga Web Performance Data Graphs repository into `/usr/share/icingaweb2/modules/perfdatagraphs`

2. Clone a Icinga Web Performance Data Graphs Backend repository into `/usr/share/icingaweb2/modules/`

Examples:

```
/usr/share/icingaweb2/modules/perfdatagraphsgraphite/
/usr/share/icingaweb2/modules/perfdatagraphsinfluxdbv1/
/usr/share/icingaweb2/modules/perfdatagraphsinfluxdbv2/
/usr/share/icingaweb2/modules/perfdatagraphsinfluxdbv3/
/usr/share/icingaweb2/modules/perfdatagraphselasticsearch/
/usr/share/icingaweb2/modules/perfdatagraphsopensearch/
```

3. Enable both modules using the `Configuration → Modules` menu

5. Configure the "backend" module (e.g. URL and authentication for the performance database)

5. (optionally) Grant permissions for the "frontend" and "backend" module for users

6. (optionally) Configure specific graphs via Icinga2 Custom Variables
