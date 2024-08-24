<style><?php include 'style.css'; ?></style>

<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
	<script src='include/jsPlots.js'></script>
  <title>HomeMeteo</title>
  <link rel="icon" type="image/png" href="favicon.png">
</head>

<body>
  <h1> Home Meteo </h1>
  <?php
	//Connect to database
	include("include/dbConn.php");
	include("include/dbQuerries.php");

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
	//density
	[$bins,$params]=getHistograms($conn, 0, 12);
	$dates=array_keys(json_decode($params,true));
  $conn->close();
  ?>
  <!-- BODY HERE -->
	<?php
	$now = new DateTime('now');
	$then = new DateTime(end($time_array_1));
	$interval =  $now->getTimestamp() - $then->getTimestamp();
	$status="Ofline";
	$color="#ff0000"; //red
	if ($interval < 120){
		$status="Online";
		$color="#228b22";
		//echo $interval;
	}
	//echo $status;
	?>
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
	<span class="dot" style="background-color:<?php echo $color;?>;"></span>
	<?php echo $status; ?>


	<p><a href="full.php">Full data</a> | <a href="last.php">Last measurement</a> | <a href="https://github.com/andregtorres/homeMeteo">GitHub</a></p>
  <div style="height: 600px; max-width:1000;" id='measurementsPlot'><!-- Plotly chart will be drawn inside this DIV --></div>
	<div style="height: 600px; max-width:1000;" id='statsPlot'><!-- Plotly chart will be drawn inside this DIV --></div>
	<!-- as seen in https://itnext.io/how-to-stop-content-jumping-when-images-load-7c915e47f576 -->
	 <div class="wrapper">
		 <div id="hoverinfo" class="wrapper__img" style="margin-left:200px;"></div><!-- Histogram -->
	</div>
	<script>
  var times = <?php echo $json_time_1; ?>;
  var temp = <?php echo $json_temp_1; ?>.map(x=>+x/100);
  var humi = <?php echo $json_humi_1; ?>.map(x=>+x/100);
	plotMeasurements('measurementsPlot', times, temp, humi)

  var days = <?php echo $json_day; ?>;
  var t_avg = <?php echo $json_t_avg; ?>;
	var t_q25 = <?php echo $json_t_q25; ?>;
	var t_q75 = <?php echo $json_t_q75; ?>;
  var h_avg = <?php echo $json_h_avg; ?>;
	var h_q25 = <?php echo $json_h_q25; ?>;
	var h_q75 = <?php echo $json_h_q75; ?>;

	plotStats('statsPlot', days,t_avg,t_q25,t_q75,h_avg,h_q25,h_q75);
  //https://plotly.com/javascript/hover-events/
  var myPlot = document.getElementById('statsPlot'),
    hoverInfo = document.getElementById('hoverinfo');

	var bins = <?php echo $bins; ?>;
	var histParams = <?php echo $params; ?>;
	var dates = <?php echo json_encode($dates); ?>;

  myPlot.on('plotly_hover', function(data){
		hoverInfo.innerHTML = '';
    var infotext = data.points.map(function(d){
			date = data.points[0].x;
			if (dates.includes(date)) {
				return('<div style="height: 800px; max-width:800;" id="densityPlot">');
			} else {
				return('<img src="plots/homeMeteo_full_'+data.points[0].x+'.png" alt="'+data.points[0].x+'" onerror="this.onerror=null; this.src=\'plots/notFound.jpg\'">');
			}
		});
    hoverInfo.innerHTML = infotext.join('<br/>');
		if (dates.includes(date)) {
			plotDensity("densityPlot",date, bins, histParams);
		}
  })
  .on('plotly_unhover', function(data){
    //hoverInfo.innerHTML = '';
  });

	</script>
</body>
