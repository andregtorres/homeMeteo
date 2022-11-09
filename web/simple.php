<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>

</head>

<body>
  <h1> Home Meteo </h1>
  <?php
  $servername = "db.tecnico.ulisboa.pt";
  $username = "";
  $password = "";
  $dbname = "";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql1 = "SELECT timestamp, temp, humi FROM homeMeteoLogs WHERE host = 0 AND timestamp >= ( CURDATE() - INTERVAL 1 DAY)";
  $result1 = $conn->query($sql1);


  $time_array_1 = Array();
  $temp_array_1 = Array();
  $humi_array_1 = Array();

  if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) {
      //echo "timestamp: " . $row[$timestamp] . "temp: " . $row["temp"]. " - humi: " . $row["humi"]. "<br>";
      $time_array_1[] = $row["timestamp"];
      $temp_array_1[] = $row["temp"];
      $humi_array_1[] = $row["humi"];
    }
    //convert the PHP array into JSON format, so it works with javascript
    $json_time_1 = json_encode($time_array_1);
    $json_temp_1 = json_encode($temp_array_1);
    $json_humi_1 = json_encode($humi_array_1);
  } else {
    echo "0 results";
  }

  $conn->close();
  ?>
  <div id='plotlyDiv1'><!-- Plotly chart will be drawn inside this DIV --></div>

  <script>
  var times = <?php echo $json_time_1; ?>;
  var temp = <?php echo $json_temp_1; ?>.map(x=>+x/100);
  var humi = <?php echo $json_humi_1; ?>.map(x=>+x/100);

  var trace1 ={
      x:times,
      y:temp,
      mode:'lines+markers',
      name:'Temperature',
      marker_color:temp,
  };
  var trace2 ={
      x:times,
      y:humi,
      mode:'lines+markers',
      name:'Relative humidity',
      marker_color:humi,
      yaxis: 'y2',
  };

  var data = [ trace1, trace2];

  var layout = {
    grid: {rows: 2, columns: 1},
    shared_xaxes: true,
    yaxis: {
      title:{text: "Temperature [ºC]"},
      row:1,
      col:1,
    },
    yaxis2: {
      title:{text: "Relative humidity [%]"},
      row:2,
      col:1,
    },
  };
  Plotly.newPlot('plotlyDiv1', data, layout);
	</script>
</body>
