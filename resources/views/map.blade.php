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

        <div class="content">
            <div class="slide_container">
                <input type="range" min="1880" max="2020" value="1950" step="0.01" class="slider" id="myRange">
            </div>
            <p class="selectedYear">1950</p>
        </div>

        <div id="map_container"></div>

        <h2 class="is-center">Infection numbers on <span id="diseaseName"></span></h2>
        <div class="pure-g" style="max-width: 850px; margin: 0 auto;">
            <div class="pure-u-1 pure-u-sm-1-2 l-box is-center">
                <div class="select">
                    <select name="disease" id="diseaseSelect">
                        <option selected disabled value="None">Choose a disease</option>
                        <option value="Diphtheria">Diphtheria</option>
                        <option value="Influenza">Influenza</option>
                        <option value="Measles">Measles</option>
                        <option value="Mumps">Mumps</option>
                        <option value="Pneumonia">Pneumonia</option>
                        <option value="Scarlet fever">Scarlet fever</option>
                        <option value="Smallpox">Smallpox</option>
                        <option value="Pertussis">Pertussis</option>
                        <option value="Tuberculosis">Tuberculosis</option>
                        <option value="Typhoid fever">Typhoid fever</option>
                    </select>
                </div>
            </div>

            <div class="pure-u-1 pure-u-sm-1-2 l-box is-center">
                <div class="select">
                    <select name="db" id="dbSelect">
                        <option selected disabled value="None">Choose ADB</option>
                        <option value="SQL">SQL</option>
                        <option value="MongoDB">MongoDB</option>
                        <option value="Neo4J">Neo4J</option>
                    </select>
                </div>
            </div>
        </div>

        <script src="https://code.highcharts.com/maps/highmaps.js"></script>
        <script src="https://code.highcharts.com/mapdata/countries/us/us-all.js"></script>
        <script>
            let map = Highcharts.mapChart('map_container', {
                chart: {
                    map: 'countries/us/us-all',
                    borderWidth: 0,
                    backgroundColor: '#7ac7f1',
                    maxWidth: 850,
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
                    min: 0,
                    type: 'linear',
                    stops: [
                        [0, '#f6f6f6'],
                        [0.33, '#f6e1b9'],
                        [0.67, '#ff8448'],
                        [1, '#ff2500']
                    ]
                },

                series: [{
                    animation: {
                        duration: 500
                    },
                    data: [],
                    joinBy: ['hc-key', 'code'],
                    name: 'Count',
                }],
            });
        </script>
        <script>
            const usStates = Highcharts.maps["countries/us/us-all"].features.map(f => {
                return {
                    code: f.properties['hc-key'],
                    value : 0
                }
            });

            const slider = document.getElementById("myRange");
            const diseaseSelect = document.getElementById("diseaseSelect")
            const dbSelect = document.getElementById("dbSelect")
            const yearText = document.getElementsByClassName("selectedYear");

            slider.addEventListener('input', (event) => {
                let oldVal = yearText[0].innerText;
                let newVal = Math.floor(event.target.value).toString();
                yearText[0].innerText = newVal;

                if (oldVal !== newVal)
                    fetchCases();
            });

            diseaseSelect.addEventListener('change', (event) => {
                const statesCopy = usStates.map(state => ({code: state.code, value: state.value}));
                map.series[0].setData(statesCopy, true, 200);
                setTimeout(() => fetchCases({ duration: 500 }), 200);

                document.getElementById("diseaseName").textContent = event.target.value;
            });

            dbSelect.addEventListener('change', (event) => {
                const statesCopy = usStates.map(state => ({code: state.code, value: state.value}));
                map.series[0].setData(statesCopy, true, 200);
                setTimeout(() => fetchCases({ duration: 500 }), 200);
            });

            function fetchCases(animation)
            {
                const hm_animation = animation ?? { duration: 50}

                const year = Math.floor(slider.value);
                const disease = diseaseSelect.value;
                const adb = dbSelect.value;

                if (disease === 'None')
                    return;

                fetch(`/api/cases/${year}/${disease}?adb=${adb}`)
                    .then(response => response.json())
                    .then(data => {
                        newData = usStates.map((state) => {
                            newValue = data?.find(d => d.stateIso === state.code)?.caseCount ?? 0;

                            return {
                                code: state.code,
                                value: newValue,
                            }
                        });

                        map.series[0].setData(newData, true, hm_animation);
                    });
            }
        </script>
    </body>
</html>
