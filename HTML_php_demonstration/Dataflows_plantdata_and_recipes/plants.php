<html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>OOCSI data streams</title>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="oocsi-web.js"></script>

    <!-- Css -->
    <!-- THIS WAS TAKEN FROM MY OWN WEBSITE, feel free to add your own -->
    <!-- <link rel="stylesheet" href="/assets/css/default.css"> -->

    <!-- plotly scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.plot.ly/plotly-latest.min.js" charset="utf-8"></script>
    <script> src="/assets/js/demoday/plotly.min.js"</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Css specific to this page -->
    <style>
    * {
        box-sizing: border-box;
    }

    .intro {
        display: flex;
        padding-left: 50px;
        width: 50%;
    }

    .row {
        display: flex;
        padding-left: 40px;
    }

    .column {
        float: left;
        padding: 10px;
        height: 300px;
    }

    .left,
    .middle {
        width: 45%;
    }

    .right {
        width: 45%;
        padding-right: 80px;
    }

    .back {
        display: flex;
        /* float: left;  */
        padding-left: 50px;
    }

    p {
        margin: 0;
        display: inline
    }
    </style>
</head>

<body>
    <br>

    <body>
        <div class="back">
            <a class="backbutton" href="Insert link of Index page here"> Back to the overview </a>
        </div>

        <div class='intro'>
            <h2> Plant information </h2>
        </div>
        <div class='intro'>
            <p>On this page, you can see the data stream which comes from a Flora plant sensor.
                There is a slight delay between the printed data and the graphs.
            </p>
            <br><br>
        </div>

        <div class="row">
            <div class="column left">
                <h3> Sensor data </h3>
                <p>Light: </p>
                <p id="L"></p>
                <br>
                <p>Temperature: </p>
                <p id="T"> </p>
                <br>
                <p>Moisture: </p>
                <p id="M"></p>
                <br>
                <p>Conductivity: </p>
                <p id="C"></p>
                <br><br>

            </div>
        </div>

        <script type="text/javascript">
        OOCSI.connect("wss://" + "oocsi.id.tue.nl" + "/ws");

        // subscribe to a channel and add data to HTML
        var temperature = 0
        var moisture = 0
        var conductivity = 0
        var light = 0

        OOCSI.subscribe("Botano", function(e) {
            temperature = e.data.temperature;
            document.getElementById("T").innerHTML = temperature;
            moisture = e.data.soil_moisture_s001;
            document.getElementById("M").innerHTML = moisture;
            conductivity = e.data.plant_nutrient_s001;
            document.getElementById("C").innerHTML = conductivity;
            light = e.data.light_intensity;
            document.getElementById("L").innerHTML = light;
        });
        </script>

        <div>
            <div class="column left" id='chartlight'>
                <!-- chart goes here -->
            </div>
        </div>

        <script>
        var layoutlight = {
            title: {
                text: 'Light plot',
                font: {
                    size: 14
                },
            },
            yaxis: {
                range: [0, 1050]
            }
        };

        function getDatalight() {
            // return Math.random(); //handy for testing
            return light;
        }

        Plotly.plot('chartlight', [{
            y: [getDatalight()],
            type: 'line'

        }], layoutlight);

        var cnt = 0;
        setInterval(function() {
            Plotly.extendTraces('chartlight', {
                y: [
                    [getDatalight()]
                ]
            }, [0]);
            cnt++;

            if (cnt > 20) {
                Plotly.relayout('chartlight', {
                    xaxis: {
                        range: [cnt - 20, cnt]
                    }
                });
            }
        }, 20000);
        </script>

        <div>
            <div class="column right" id='charttemp'>
                <!-- chart goes here -->
            </div>
        </div>

        <script>
        var layouttemp = {
            title: {
                text: 'Temperature plot',
                font: {
                    size: 14
                },
            },
            yaxis: {
                range: [0, 30]
            },

        };

        function getDatatemp() {
            // return Math.random();
            return temperature;
        }

        Plotly.plot('charttemp', [{
            y: [getDatatemp()],
            type: 'line'

        }], layouttemp);

        var cnt = 0;
        setInterval(function() {
            Plotly.extendTraces('charttemp', {
                y: [
                    [getDatatemp()]
                ]
            }, [0]);
            cnt++;

            if (cnt > 20) {
                Plotly.relayout('charttemp', {
                    xaxis: {
                        range: [cnt - 20, cnt]
                    }
                });
            }
        }, 20000);
        </script>

        <div>
            <div class="column left" id='chartmoist'>
                <!-- chart goes here -->
            </div>
        </div>

        <script>
        var layoutmoist = {
            title: {
                text: 'Soil moisture plot',
                font: {
                    size: 14
                },
            },
            yaxis: {
                range: [0, 25]
            },

        };

        function getDatamoist() {
            // return Math.random();
            return moisture;
        }

        Plotly.plot('chartmoist', [{
            y: [getDatamoist()],
            type: 'line'

        }], layoutmoist);

        var cnt = 0;
        setInterval(function() {
            Plotly.extendTraces('chartmoist', {
                y: [
                    [getDatamoist()]
                ]
            }, [0]);
            cnt++;

            if (cnt > 20) {
                Plotly.relayout('chartmoist', {
                    xaxis: {
                        range: [cnt - 20, cnt]
                    }
                });
            }
        }, 20000);
        </script>

        <div>
            <div class="column right" id='chartconductivity'>
                <!-- chart goes here -->
            </div>
        </div>

        <script>
        var layoutcon = {
            title: {
                text: 'Soil conductivity plot',
                font: {
                    size: 14
                },
            },
            yaxis: {
                range: [0, 200]
            },

        };

        function getDataconductivity() {
            // return Math.random();
            return conductivity;
        }

        Plotly.plot('chartconductivity', [{
            y: [getDataconductivity()],
            type: 'line'
        }], layoutcon);

        var cnt = 0;
        setInterval(function() {
            Plotly.extendTraces('chartconductivity', {
                y: [
                    [getDataconductivity()]
                ]
            }, [0]);
            cnt++;

            if (cnt > 20) {
                Plotly.relayout('chartconductivity', {
                    xaxis: {
                        range: [cnt - 20, cnt]
                    }
                });
            }
        }, 20000);
        </script>
    </body>

</html>