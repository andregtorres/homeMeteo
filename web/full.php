<style><?php include 'style.css'; ?></style>
<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
	<script src='include/jsPlots.js'></script>
	<title>HomeMeteo</title>
  <link rel="icon" type="image/png" href="favicon.png">
</head>

<body>
	<div class="content">
	  <h1> Home Meteo </h1>
		<p><a href="index.php">Back</a> | <a href="prague.php">Prague</a> | <a href="last.php">Last measurement</a> | <a href="https://github.com/andregtorres/homeMeteo">GitHub</a></p>
		<?php

			//Connect to database
			include("include/dbConn.php");
			include("include/dbQuerries.php");

		  if (!$conn){
		    echo 'Connection attempt failed.<br>';
		  }

			#$sql = "SELECT host_id, location, mins FROM homeMeteoDevices WHERE live IS TRUE ORDER BY host_id";
			$sql = "SELECT host_id, location, mins, live FROM homeMeteoDevices ORDER BY host_id";
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
			$live=array();
			$plot=array();
			$N_devices=count($devices);

			//online Status
			$now = new DateTime('now');
			for ($i=0; $i < $N_devices; $i++) {
				[$times_, $temp_, $humi_] = getDaysById($conn, $devices[$i]["host_id"], 365);
				$times[]= $times_;
				$temp[]= $temp_;
				$humi[]= $humi_;
				$online[]=false;
				$plot[]=false;
				$live[]=$devices[$i]["live"];
				if ($devices[$i]["live"] && count(json_decode($times_))>0){
					$plot[$i]=true;
					$then = new DateTime(end(json_decode($times_)));
					$interval =  $now->getTimestamp() - $then->getTimestamp();
					if ($interval <= $devices[$i]["mins"]*60){
						$online[$i]=true;
					}
				}
			}
			//STATS
			$stats=array();
			for ($i=0; $i < $N_devices; $i++) {
				$stats[]= getStatsById($conn, $devices[$i]["host_id"], 10);
			}
			$conn->close();
	  ?>
		<table class="normal">
		  <tr>
				<th>Location</th>
		    <th>Device</th>
				<th>Live</th>
				<th>Status</th>
				<th>Last measurement</th>
				<th>Plot</th>
		  </tr>
			<?php
				for ($i=0; $i < $N_devices; $i++) {
					$color= $online[$i] ? "#228b22" : "#ff0000";
					$color2= $live[$i] ? "#228b22" : "#ff0000";
					$onOff= $online[$i] ? "Online" : "Offline";
					$checked= $plot[$i] ? "checked" : "";

					echo "<tr>";
					echo "<td>" . $devices[$i]["location"] . "</td>";
					echo "<td>" . $devices[$i]["host_id"] . "</td>";
					echo '<td> <span class="dot" style="background-color:' . $color2 . ';"></span>  '.'</td>';
					echo '<td> <span class="dot" style="background-color:' . $color . ';"></span>  '. $onOff .'</td>';
					echo "<td>" . end(json_decode($times[$i])) . "</td>";
					echo '<td><input type="checkbox" onclick="onChangeDevices()" name="plotDevice"'.$checked.'></td>';
					echo "</tr>";
				}
			?>
		</table>
		<br>
		<div class="plot" id='plotlyDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
		<h2>Statistics:</h2>
		<div class="plot" id='plotlyStatsDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
		<div class="wrapper">
			<div id="hoverinfo" class="wrapper__img" style="margin-left:200px;"></div><!-- Histogram -->
	 	</div>
	 </div>
	<script>

		function onChangeDevices(){
			var checkedBoxes = document.querySelectorAll('input[name=plotDevice]');
			for (var i = 0; i < checkedBoxes.length; i++) {
				plots[i]=checkedBoxes[i].checked;
			}
			doPlot("plotlyDiv", N_devices, times, temp, labels, plots);
			doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);
		}

		//PLOTS
		var N_devices= <?php echo $N_devices; ?>;
		var times=[];
		var temp=[];
		var humi=[];
		var labels=[];
		var plots=[];
		<?php
			for ($i=0; $i < $N_devices; $i++) {
				echo "times.push(".$times[$i].");\n";
				echo "temp.push(".$temp[$i].");\n";
				echo "humi.push(".$humi[$i].");\n";
				echo "plots.push(".var_export($plot[$i], true).");\n";
				echo "labels.push('".$devices[$i]["location"]."');";
			}
		?>
		doPlot("plotlyDiv", N_devices, times, temp, labels, plots);
		//stats
		var stats= <?php echo json_encode($stats) ?>;
		doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);

	</script>

</body>
