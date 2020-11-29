<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Database management project in IKT446 ICT Seminar 4 Database Management at University of Agder (UiA), Grimstad 2020.">
        <link rel="icon" type="image/png" href="/favicon.png">
        <title>Interactive Tycho Disease Dataset on US States</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css">
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css">
        <script src="https://kit.fontawesome.com/a19accb0ec.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="styles.css">

        <!-- <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/base-min.css"> -->
    </head>
    <body class="map-background">
        <div class="header">
            <div class="home-menu pure-menu pure-menu-horizontal">
                <a class="pure-menu-heading" href="">GRAPH OF DISEASE CASES IN US STATES</a>
            </div>
        </div>

        <div class="is-center" style="margin-top: 25px; margin-bottom: 40px;">
            <div class="select">
                <select name="db" id="dbSelect">
                    <option selected disabled value="None">Choose ADB</option>
                    <option value="SQL">SQL</option>
                    <option value="MongoDB">MongoDB</option>
                    <option value="Neo4J">Neo4J</option>
                </select>
            </div>

            <br />
            <br />

            <label class="switch">
                <input type="checkbox" id="deathSwitch">
                <span class="toggle round"></span>
            </label>
        </div>

        <div id="graph_container"></div>

        <div class="pure-g" style="max-width: 850px; margin: 0 auto;">
            <div class="pure-u-1 pure-u-sm-1-2 l-box is-center">
                <div class="select">
                    <select name="disease" id="diseaseSelect">
                        <option selected disabled value="None">Choose a disease</option>
                        @foreach($diseases as $disease)
                            <option value="{{$disease}}">{{$disease}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pure-u-1 pure-u-sm-1-2 l-box is-center">
                <div class="select">
                    <select name="state" id="stateSelect">
                        <option selected disabled value="None">Choose a state</option>
                    </select>
                </div>
            </div>
        </div>

        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script>
            const chart = Highcharts.chart('graph_container', {
                chart: {
                    type: 'spline',
                    borderWidth: 0,
                    backgroundColor: 'transparent',
                    maxWidth: 850,
                    height: 400,
                },
                title: undefined,
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        year: '%Y'
                    },
                    title: {
                        text: undefined
                    }
                },
                yAxis: {
                    title: {
                        text: 'Number of cases'
                    },
                    min: 0
                },
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%Y}: {point.y:.0f}'
                },

                plotOptions: {
                    series: {
                        marker: {
                            enabled: true
                        }
                    }
                },

                series: [{
                    name: "Cases",
                    data: [],
                    color: '#f00'
                }],
            });
        </script>
        <script>
            const deathSwitch = document.getElementById("deathSwitch")
            const diseaseSelect = document.getElementById("diseaseSelect")
            const stateSelect = document.getElementById("stateSelect");
            const dbSelect = document.getElementById("dbSelect")

            const usStates = [
                ["USA", "All states"],
                ["US-AL", "Alabama"],
                ["US-AK", "Alaska"],
                ["US-AZ", "Arizona"],
                ["US-AR", "Arkansas"],
                ["US-CA", "California"],
                ["US-CO", "Colorado"],
                ["US-CT", "Connecticut"],
                ["US-DE", "Delaware"],
                ["US-DC", "District Of Columbia"],
                ["US-FL", "Florida"],
                ["US-GA", "Georgia"],
                ["US-HI", "Hawaii"],
                ["US-ID", "Idaho"],
                ["US-IL", "Illinois"],
                ["US-IN", "Indiana"],
                ["US-IA", "Iowa"],
                ["US-KS", "Kansas"],
                ["US-KY", "Kentucky"],
                ["US-LA", "Louisiana"],
                ["US-ME", "Maine"],
                ["US-MD", "Maryland"],
                ["US-MA", "Massachusetts"],
                ["US-MI", "Michigan"],
                ["US-MN", "Minnesota"],
                ["US-MS", "Mississippi"],
                ["US-MO", "Missouri"],
                ["US-MT", "Montana"],
                ["US-NE", "Nebraska"],
                ["US-NV", "Nevada"],
                ["US-NH", "New Hampshire"],
                ["US-NJ", "New Jersey"],
                ["US-NM", "New Mexico"],
                ["US-NY", "New York"],
                ["US-NC", "North Carolina"],
                ["US-ND", "North Dakota"],
                ["US-OH", "Ohio"],
                ["US-OK", "Oklahoma"],
                ["US-OR", "Oregon"],
                ["US-PA", "Pennsylvania"],
                ["US-RI", "Rhode Island"],
                ["US-SC", "South Carolina"],
                ["US-SD", "South Dakota"],
                ["US-TN", "Tennessee"],
                ["US-TX", "Texas"],
                ["US-UT", "Utah"],
                ["US-VT", "Vermont"],
                ["US-VA", "Virginia"],
                ["US-WA", "Washington"],
                ["US-WV", "West Virginia"],
                ["US-WI", "Wisconsin"],
                ["US-WY", "Wyoming"],
            ]

            usStates.forEach(s => {
                const option = document.createElement("option");
                option.setAttribute('value', s[0]);
                option.innerText = s[1];
                stateSelect.append(option)
            });

            stateSelect.addEventListener('change', (event) => {
                fetchCases()
            });

            diseaseSelect.addEventListener('change', (event) => {
                fetchCases()
            });

            dbSelect.addEventListener('change', (event) => {
                chart.series[0].setData([]);
                fetchCases();
            });

            deathSwitch.addEventListener('change', (event) => {
                document.body.classList.toggle('death');
                fetchCases();
            })

            function fetchCases(animation)
            {
                animation ??= { duration: 50 }

                const disease = diseaseSelect.value;
                const state = stateSelect.value;
                const adb = dbSelect.value;
                const fatalities = deathSwitch.checked ? 1 : 0;

                if (disease === 'None' || state === 'None' || adb === 'None')
                    return;

                return fetch(`/api/history/${disease}/${state}?adb=${adb}&fatalities=${fatalities}`)
                    .then(response => response.json())
                    .then(data => {
                        data = data.sort((a, b) => { return a.year - b.year });

                        const newData = data.map((obj) => [Date.UTC(obj.year, 0), obj.count]);
                        const stateName = usStates.find(s => s[0] === state)[1];

                        chart.series[0].setData(newData, true);
                        chart.series[0].setName(`Number of ${disease} ${fatalities ? 'deaths' : 'cases'} in ${stateName}`)
                        chart.yAxis[0].axisTitle.attr({
                            text:  `Number of ${fatalities ? 'deaths' : 'cases'}`
                        });
                    });
            }
        </script>
    </body>
</html>
