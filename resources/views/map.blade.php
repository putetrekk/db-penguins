<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A layout example that shows off a responsive product landing page.">
        <title>Interactive Tycho Disease Dataset on US States</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css">
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css">
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
        <link rel="stylesheet" href="styles.css">

        <!-- <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/base-min.css"> -->
    </head>
    <body class="map-background">
        <div class="header">
            <div class="home-menu pure-menu pure-menu-horizontal">
                <a class="pure-menu-heading" href="">Interactive Tycho Disease Dataset on US States</a>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="content">
                <div class="slide_container">
                    <input type="range" min="1880" max="2020" value="1950" step="0.01" class="slider" id="myRange">
                </div>
                <p class="selectedYear">1950</p>
            </div>
        </div>

        <div class="content-wrapper">
            <div id="map_container"></div>


            <h2 class="is-center">Infection numbers on Typhoid fever (1900-1975)</h2>
        </div>

        <script src="https://code.highcharts.com/maps/highmaps.js"></script>
        <script src="https://code.highcharts.com/mapdata/countries/us/us-all.js"></script>
        <script>
            let map = Highcharts.mapChart('map_container', {
                chart: {
                    map: 'countries/us/us-all',
                    borderWidth: 0,
                    backgroundColor: '#7ac7f1',
                    maxWidth: 1050,
                    height: '100%',
                },
                credits: {
                    enabled: false
                },

                title: undefined,

                legend: {
                    layout: 'horizontal',
                    borderWidth: 0,
                    backgroundColor: 'transparent',
                    floating: true,
                    verticalAlign: 'bottom',
                    y: 20
                },

                colorAxis: {
                    min: 1,
                    type: 'logarithmic',
                    stops: [
                        [0, '#f6f6f6'],
                        [0.33, '#f6e1b9'],
                        [0.67, '#ff8448'],
                        [1, '#ff2500']
                    ]
                },

                series: [{
                    animation: {
                        duration: 1000
                    },
                    data: [],
                    joinBy: ['hc-key', 'code'],
                    name: 'Count',
                }],
            });
        </script>
        <script>
            const slider = document.getElementById("myRange");
            const yearText = document.getElementsByClassName("selectedYear");

            slider.addEventListener('input', (event) => {
                let oldVal = yearText[0].innerText
                let newVal = Math.floor(event.target.value).toString()

                if (oldVal !== newVal)
                    fetchCases(yearText[0].innerText, '11')

                yearText[0].innerText = newVal
            });

            function fetchCases(year, diseaseId)
            {
                fetch('/api/cases/'+year+'/'+diseaseId)
                    .then(response => response.json())
                    .then(data => {
                        data = data.map((c) => {
                            if (c.caseCount <= 0)
                                return

                            return {
                                code: c.stateIso,
                                value: c.caseCount,
                            }
                        });
                        map.series[0].setData(data);
                    });
            }
        </script>
    </body>
</html>
