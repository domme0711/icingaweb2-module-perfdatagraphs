# Development

## uPlot Update

```
# Update the JS file
curl -sSL https://raw.githubusercontent.com/leeoniya/uPlot/refs/heads/master/dist/uPlot.iife.min.js -o public/js/vendor/uPlot.iife.min.js

# (optional) Update the CSS file
# Note: We customized the vendor/uPlot.css - removed lines are commented out in the uPlot.css
# curl -sSL https://raw.githubusercontent.com/leeoniya/uPlot/refs/heads/master/dist/uPlot.css -o public/css/vendor/uPlot.css

# Update the dependency
vi package.json
```
