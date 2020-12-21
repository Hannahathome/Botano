<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Connection to OOCSI -->
    <title>OOCSI data streams</title>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="oocsi-web.js"></script>

    <!--  Css -->
    <!-- THIS WAS TAKEN FROM MY OWN WEBSITE, feel free to add your own -->
    <!-- <link rel="stylesheet" href="/assets/css/default.css"> -->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Css specific to this page -->
    <style>
    * {
        box-sizing: border-box;
    }

    .row {
        display: flex;
        padding-left: 40px;
    }

    .back {
        display: flex;
        padding-left: 50px;

    }

    .intro {
        display: flex;
        padding-left: 50px;
        width: 50%;
    }

    .column {
        float: left;
        padding: 10px;
        height: 300px;
    }

    .left,
    .middle {
        width: 35%;
    }

    .right {
        width: 20%;
        padding-right: 60px;
    }

    a {
        font-size: 18px;
    }

    h3 {
        font-size: 20px;
    }

    p {
        font-size: 18px;
        margin: 0;
        display: inline
    }
    </style>
</head>

<body>
    <br>
    <div class="back">
        <a class="backbutton" href="Insert link of Index page here"> Back to the overview </a>
    </div>

    <div class='intro'>
        <h2> Recipe Recommender </h2>
    </div>
    <div class='intro'>

        <p>On this page, you can test our recommender system. On the left, you can select produce that you have or is
            ready to be harvested
            in your garden. In the middle column, a list of recipes will appear based on the Gorré family's preferences.
            On the rightmost
            side of the page, you can choose different filters to see the overall popular recipes or see the 'family
            favourites', as determined by <a target="_blank" href="https://solar.jorritvanderheide.com/recuisine">
                Recuisine </a>. </p>
        <br><br>

    </div>

    <div class="row">
        <div class="column left">
            <h3> Which ingredients are ready?</h3>
            <br>
            <p>
                Choose from: <br>
                <i>basil, beetroot, bell pepper,<br>
                    carrot, celery, coriander,<br>
                    eggplant, fresh ginger, garlic,<br>
                    mint, onion, paprika,<br>
                    parsley, red pepper, spinach,<br>
                    spring onion, tomato, zucchini </i>
            </p><br><br>

            <input type="text" id="ing" value="onion"><br><br>

            <p>Click the "Send" button to get the possible recipes you can cook</p><br><br>

            <!-- <button onclick="myFunction()">Send</button> -->
            <a class="button" href="#" onclick="myFunction()"> Send</a>

        </div>
        <div class="column middle">
            <h3>Your chosen ingredients: </h3>
            <br><br>

            <p id="demo"></p><br>

            <h3 id="filter"></h3><br>

            <p id="r0"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r1"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r2"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r3"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r4"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r5"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r6"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r7"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r8"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>

            <p id="r9"></p>
            <button onclick="sendWatt()"> Cook! </button>
            <br><br>
        </div>

        <!-- Filter buttons -->
        <div class="column right">
            <h3>Select filter</h3> <br>
            <a class="button" href="#" onclick="pop()">Popular recipes (default)</a>
            <br><br><br>

            <a class="button" href="#" onclick="pers()">Recommend for you</a>
            <br><br><br>

            <a class="button" href="#" onclick="fam()">Family favourites</a>

        </div>
        <br><br>
    </div>
    <br><br>

    <script type="text/javascript">
    // connect to the OOCSI server
    OOCSI.connect("wss://" + "oocsi.id.tue.nl" + "/ws");

    function sendWatt() {
        var watt = {
            'energy': Math.floor(Math.random() * 3000) + 2500,
            'time': Math.floor(Math.random() * 90) + 15
        };
        OOCSI.send("recipeEnergy", watt);
    }

    // getting recipes from the database using the filters
    function myFunction() {
        var x = document.getElementById("ing").value;
        document.getElementById("demo").innerHTML = x;
        var d = {
            "ingredients": document.getElementById("ing").value
        };
        var name = "The most popular recipes:";
        document.getElementById("filter").innerHTML = name;
        OOCSI.send("RecipeRecommender", d);
        var filter = {
            "filter": "popular"
        }
        OOCSI.send("RecipeRecommender", filter);
    }

    // different funtions for filtering
    function pop() {
        var filter = {
            "filter": "popular"
        }
        OOCSI.send("RecipeRecommender", filter);
        var name = "Most popular:";
        document.getElementById("filter").innerHTML = name;
    }

    function pers() {
        var filter = {
            "filter": "personal"
        }
        OOCSI.send("RecipeRecommender", filter);
        var name = "Recommend for you:";
        document.getElementById("filter").innerHTML = name;
    }

    function fam() {
        var filter = {
            "filter": "family"
        }
        OOCSI.send("RecipeRecommender", filter);
        var name = "Family favourites:";
        document.getElementById("filter").innerHTML = name;
    }

    // subscribe to a channel and add data to HTML
    OOCSI.subscribe("RecipeRecommender", function(e) {
        document.getElementById("r0").innerHTML = (typeof e.data.Recipe0 != 'undefined') ? e.data.Recipe0 : "";
        document.getElementById("r1").innerHTML = (typeof e.data.Recipe1 != 'undefined') ? e.data.Recipe1 : "";
        document.getElementById("r2").innerHTML = (typeof e.data.Recipe2 != 'undefined') ? e.data.Recipe2 : "";
        document.getElementById("r3").innerHTML = (typeof e.data.Recipe3 != 'undefined') ? e.data.Recipe3 : "";
        document.getElementById("r4").innerHTML = (typeof e.data.Recipe4 != 'undefined') ? e.data.Recipe4 : "";
        document.getElementById("r5").innerHTML = (typeof e.data.Recipe5 != 'undefined') ? e.data.Recipe5 : "";
        document.getElementById("r6").innerHTML = (typeof e.data.Recipe6 != 'undefined') ? e.data.Recipe6 : "";
        document.getElementById("r7").innerHTML = (typeof e.data.Recipe7 != 'undefined') ? e.data.Recipe7 : "";
        document.getElementById("r8").innerHTML = (typeof e.data.Recipe8 != 'undefined') ? e.data.Recipe8 : "";
        document.getElementById("r9").innerHTML = (typeof e.data.Recipe9 != 'undefined') ? e.data.Recipe9 : "";
    });
    </script>
</body>

</html>