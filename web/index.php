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
	<p><a href="full.php">Full data</a> | <a href="prague.php">Prague</a> | <a href="last.php">Last measurement</a> | <a href="https://github.com/andregtorres/homeMeteo">GitHub</a></p>
	<?php

		//Connect to database
		include("include/dbConn.php");
		include("include/dbQuerries.php");

	  if (!$conn){
	    echo 'Connection attempt failed.<br>';
	  }

		$sql = "SELECT host_id, location, mins FROM homeMeteoDevices WHERE live IS TRUE ORDER BY host_id";
		$result = $conn->query($sql);
		if ($result) {
			$devices = $result->fetch_all(MYSQLI_ASSOC);
		} else {
			echo "Database error! <br>";
		}
		$result -> free_result();
		$times=array();
		$temp=array();
		$humi=array();
		$online=array();
		$plot=array();
		$N_devices=count($devices);

		//online Status
		$now = new DateTime('now');
		for ($i=0; $i < $N_devices; $i++) {
			[$times_, $temp_, $humi_] = getDaysById($conn, $devices[$i]["host_id"], 2);
			$times[]= $times_;
			$temp[]= $temp_;
			$humi[]= $humi_;
			$online[]=false;
			if (count(json_decode($times_))>0){
				$plot[]=true;
				$then = new DateTime(end(json_decode($times_)));
				$interval =  $now->getTimestamp() - $then->getTimestamp();
				if ($interval <= $devices[$i]["mins"]*60){
					$online[$i]=true;
				}
			}else{
				$plot[]=false;
			}
		}

		//STATS
		$stats=array();
		for ($i=0; $i < $N_devices; $i++) {
			$stats[]= getStatsById($conn, $devices[$i]["host_id"], 1);
		}
		//DENSITY
		//$hists=array();
		//for ($i=0; $i < $N_devices; $i++) {
			//[$bins,$params]==getHistograms($conn, $i, 24);
			//$hist_dates=array_keys(json_decode($params,true));
			//$hists[]=[$hist_dates,$bins,$params];
		//}
		$conn->close();
  ?>
	<table border="1">
	  <tr>
			<th>Location</th>
	    <th>Device</th>
			<th>Status</th>
			<th>Last measurement</th>
			<th>Temp. [&#176;C]</th>
			<th>RH [%]</th>
			<th>Plot</th>
	  </tr>
		<?php
			for ($i=0; $i < $N_devices; $i++) {
				$color= $online[$i] ? "#228b22" : "#ff0000";
				$onOff= $online[$i] ? "Online" : "Offline";
				$checked= $plot[$i] ? "checked" : "";

				echo "<tr>";
				echo "<td>" . $devices[$i]["location"] . "</td>";
				echo "<td>" . $devices[$i]["host_id"] . "</td>";
				echo '<td> <span class="dot" style="background-color:' . $color . ';"></span>  '. $onOff .'</td>';
				echo "<td>" . end(json_decode($times[$i])) . "</td>";
				echo "<td>" . end(json_decode($temp[$i])) . "</td>";
				echo "<td>" . end(json_decode($humi[$i])) . "</td>";
				echo '<td><input type="checkbox" onclick="onChangeDevices()" name="plotDevice"'.$checked.'></td>';
				echo "</tr>";
			}
		?>
	</table>
	<br>
	<div id='plotlyDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
	<h2>Statistics:</h2>
	<div id='plotlyStatsDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
	<script>

		function onChangeDevices(){
			var checkedBoxes = document.querySelectorAll('input[name=plotDevice]');
			for (var i = 0; i < checkedBoxes.length; i++) {
				plots[i]=checkedBoxes[i].checked;
			}
			doPlot("plotlyDiv", times, temp, labels, plots);
			doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);
		}

		//PLOTS
		var N_devices= <?php echo $N_devices; ?>;
		var times=[];
		var temp=[];
		var humi=[];
		var labels=[];
		var plots=[];
		//stats
		var days=[];
		var t_avg=[];
		var t_q25=[];
		var t_q75=[];
		var h_avg=[];
		var h_q25=[];
		var h_q75=[];
		<?php
			for ($i=0; $i < $N_devices; $i++) {
				echo "times.push(".$times[$i].");\n";
				echo "temp.push(".$temp[$i].");\n";
				echo "humi.push(".$humi[$i].");\n";
				echo "plots.push(".$plot[$i].");\n";
				echo "labels.push('".$devices[$i]["location"]."');";
			}
		?>
		doPlot("plotlyDiv", times, temp, labels, plots);
		var stats= <?php echo json_encode($stats) ?>;
		doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);

	</script>

</body>
