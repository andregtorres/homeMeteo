<style><?php include 'style.css'; ?></style>

<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
  <title>HomeMeteo</title>
  <link rel="icon" type="image/png" href="favicon.png">
</head>

<body>
  <h1> Home Meteo </h1>
  <?php
  $servername = "";
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

	#stats
	#CREATE TABLE homeMeteoStats( day DATE, id TINYINT UNSIGNED,
	#        t_avg DOUBLE, t_std DOUBLE, t_median DOUBLE, t_min DOUBLE, t_max DOUBLE, t_q25 DOUBLE, t_q75 DOUBLE
	#        h_avg DOUBLE, h_std DOUBLE, h_median DOUBLE, h_min DOUBLE, h_max DOUBLE, h_q25 DOUBLE, h_q75 DOUBLE);
	$sql2 = "SELECT day, t_avg, t_std, t_median, t_min, t_max, t_q25, t_q75, h_avg, h_std, h_median, h_min, h_max, h_q25, h_q75  FROM homeMeteoStats WHERE id = 0  AND day >= ( CURDATE() - INTERVAL 1 YEAR) ORDER BY day";
  $result2 = $conn->query($sql2);
	if ($result2->num_rows > 0) {
    // output data of each row
    while($row = $result2->fetch_assoc()) {
			$day_array[] = $row["day"];
			$t_avg_array[] = $row["t_avg"];
			$t_std_array[] = $row["t_std"];
			$t_median_array[] = $row["t_median"];
			$t_min_array[] = $row["t_min"];
			$t_max_array[] = $row["t_max"];
			$t_q25_array[] = $row["t_q25"];
			$t_q75_array[] = $row["t_q75"];
			$h_avg_array[] = $row["h_avg"];
			$h_std_array[] = $row["h_std"];
			$h_median_array[] = $row["h_median"];
			$h_min_array[] = $row["h_min"];
			$h_max_array[] = $row["h_max"];
			$h_q25_array[] = $row["h_q25"];
			$h_q75_array[] = $row["h_q75"];

    }
    //convert the PHP array into JSON format, so it works with javascript
		$json_day = json_encode($day_array);
		$json_t_avg = json_encode($t_avg_array);
		$json_t_std = json_encode($t_std_array);
		$json_t_median = json_encode($t_median_array);
		$json_t_min = json_encode($t_min_array);
		$json_t_max = json_encode($t_max_array);
		$json_t_q25 = json_encode($t_q25_array);
		$json_t_q75 = json_encode($t_q75_array);
		$json_h_avg = json_encode($h_avg_array);
		$json_h_std = json_encode($h_std_array);
		$json_h_median = json_encode($h_median_array);
		$json_h_min = json_encode($h_min_array);
		$json_h_max = json_encode($h_max_array);
		$json_h_q25 = json_encode($h_q25_array);
		$json_h_q75 = json_encode($h_q75_array);

  } else {
    echo "0 results";
  }

  $conn->close();
  ?>
  <!-- BODY HERE -->
  <table>
    <thead>
        <tr>
            <th colspan="2">Last update: <?php echo end($time_array_1);?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Temperature:</td>
            <td><?php echo end($temp_array_1)/100;?> &#176;C</td>
        </tr>
        <tr>
            <td>Rel. humidity:</td>
            <td><?php echo end($humi_array_1)/100;?> %</td>
        </tr>
    </tbody>
</table>


	<p><a href="full.php">Full data</a> | <a href="last.php">Last measurement</a> | <a href="https://github.com/andregtorres/homeMeteo">GitHub</a></p>
  <div id='plotlyDiv1'><!-- Plotly chart will be drawn inside this DIV --></div>
	<div id='plotlyDiv2'><!-- Plotly chart will be drawn inside this DIV --></div>
	<!-- as seen in https://itnext.io/how-to-stop-content-jumping-when-images-load-7c915e47f576 -->
	 <div class="wrapper">
		 <div id="hoverinfo" class="wrapper__img" style="margin-left:200px;"></div><!-- Histogram -->
	</div>
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
    hovermode:'x unified',
  };
  Plotly.newPlot('plotlyDiv1', data, layout);
	</script>
	<script>
  var days = <?php echo $json_day; ?>;
  var t_avg = <?php echo $json_t_avg; ?>;
	var t_median = <?php echo $json_t_median; ?>;
	var t_min = <?php echo $json_t_min; ?>;
	var t_max = <?php echo $json_t_max; ?>;
	var t_q25 = <?php echo $json_t_q25; ?>;
	var t_q75 = <?php echo $json_t_q75; ?>;
  var h_avg = <?php echo $json_h_avg; ?>;
	var h_median = <?php echo $json_h_median; ?>;
	var h_min = <?php echo $json_h_min; ?>;
	var h_max = <?php echo $json_h_max; ?>;
	var h_q25 = <?php echo $json_h_q25; ?>;
	var h_q75 = <?php echo $json_h_q75; ?>;
	var d3colors = Plotly.d3.scale.category10();

	var trace1 ={
		//x:days2.concat(days,days.reverse()),
		x:days,
		y:t_q25,
		//fill: "toself",
		line: {color: "transparent"},
		fillcolor: "rgba(31, 119, 180,0.2)",
		name: "q25",
		showlegend: false,
		type: "scatter",
		mode: 'lines',
	};
  var trace2 ={
      x:days,
      y:t_avg,
      mode:'lines+markers',
      name:'Temperature',
      line: {color:d3colors(0)},
			marker: {color:d3colors(0)},
			fillcolor: "rgba(31, 119, 180,0.2)",
			type: "scatter",
			fill: "tonexty",
  };
	var trace3 ={
		//x:days2.concat(days,days.reverse()),
		x:days,
		y:t_q75,
		fill: "tonexty",
		fillcolor: "rgba(31, 119, 180,0.2)",
		line: {color: "transparent"},
		name: "q75",
		showlegend: false,
		type: "scatter",
		mode: 'lines',
	};

	var trace4 ={
		//x:days2.concat(days,days.reverse()),
		x:days,
		y:h_q25,
		//fill: "toself",
		line: {color: "transparent"},
		fillcolor: "rgba(255, 127, 14,0.2)",
		name: "q25",
		showlegend: false,
		type: "scatter",
		mode: 'lines',
		yaxis: 'y2',
	};
	var trace5 ={
      x:days,
      y:h_avg,
      mode:'lines+markers',
      name:'Relative humidity',
      marker_color:humi,
      yaxis: 'y2',
			line: {color:d3colors(1)},
			marker: {color:d3colors(1)},
			fillcolor: "rgba(255, 127, 14,0.2)",
			type: "scatter",
			fill: "tonexty",
  };
	var trace6 ={
		x:days,
		y:h_q75,
		fill: "tonexty",
		fillcolor: "rgba(255, 127, 14,0.2)",
		line: {color: "transparent"},
		name: "q75",
		showlegend: false,
		type: "scatter",
		mode: 'lines',
		yaxis: 'y2',
	};
	var trace7 ={
      x:days,
      y:h_median,
      mode:'lines+markers',
      name:'Median',
      yaxis: 'y2',
			line: {color:d3colors(2)},
			marker: {color:d3colors(2)},
			type: "scatter",
  };
  var data = [trace1, trace2, trace3, trace4, trace5, trace6];

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
    hovermode:'closest',
  };
  //https://plotly.com/javascript/hover-events/
  var myPlot = document.getElementById('plotlyDiv2'),
    hoverInfo = document.getElementById('hoverinfo');

  Plotly.newPlot('plotlyDiv2', data, layout);
  myPlot.on('plotly_hover', function(data){
    hoverInfo.innerHTML = '';
    var infotext = data.points.map(function(d){
      return('<img src="plots/homeMeteo_full_'+data.points[0].x+'.png" alt="'+data.points[0].x+'" onerror="this.onerror=null; this.src=\'plots/notFound.jpg\'">');
      });

    hoverInfo.innerHTML = infotext.join('<br/>');
  })
  .on('plotly_unhover', function(data){
    //hoverInfo.innerHTML = '';
  });


	</script>
</body>
