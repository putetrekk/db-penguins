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

    <style lang="css">
        .button-link {
            background: #2c3e50;
            color: #fff;
            width: 250px;
            font-size: 115%;
        }

        .button-link:hover {
            background: #515f76;
        }
    </style>
    <!-- <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/base-min.css"> -->
</head>
<body class="map-background">
    <div class="header">
        <div class="home-menu pure-menu pure-menu-horizontal">
            <span class="pure-menu-heading">INTERACTIVE WEB INTERFACE FOR TYCHO DATASET</span>
        </div>
    </div>

    <div class="is-center" style="margin-top: 25px; margin-bottom: 40px;">
        <h2 style="margin: 30px auto">Choose your method to interact with the dataset:</h2>
        <div>
            <a class="pure-button button-link" href="/interactive-map">Map view</a>
        </div>
        <br/>
        <div>
            <a class="pure-button button-link" href="/graph-history">Graph view</a>
        </div>
    </div>
</body>
</html>
