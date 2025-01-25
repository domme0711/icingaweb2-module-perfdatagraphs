# Troubleshooting

To investigate the data returned by the backend, you can view the view the Hook's response by
opening your Browser's development console and examine the request made by this module:

```
http://icingaweb/perfdatagraphs/fetch?host=localhost&service=hostalive&checkcommand=hostalive&duration=PT12H
```

To investigate the data processing and chart rendering in JavaScript
open your Browser's development console and set the Icinga Web Logger to `debug` level, to see the JavaScript logs:

```
icinga.logger.setLevel("debug")
```
