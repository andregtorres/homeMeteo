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
			$N_meteo=0;

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

			//METEO
			$url='https://api.open-meteo.com/v1/forecast?latitude=50.0614&longitude=19.9366&hourly=temperature_2m,relative_humidity_2m&past_days=1&forecast_days=1&timezone=Europe%2FBerlin';
			$jsonResponse = file_get_contents($url);
			if ($jsonResponse) {
				$meteo=json_decode($jsonResponse)->hourly;
				$lastIdx=0;
				$now2=strtotime("now");
				for ($i = 0; $i < count($meteo->time) ; $i++) {
					$meteo->time[$i]=str_replace("T", " ", $meteo->time[$i]);
					$timePoint = strtotime($meteo->time[$i]);
					if ($timePoint<=$now2){
						$lastIdx=$i;
					}
				}
				$times[]=json_encode(array_slice($meteo->time,0,$lastIdx+1));
				$temp[]=json_encode(array_slice($meteo->temperature_2m,0,$lastIdx+1));
				$humi[]=json_encode(array_slice($meteo->relative_humidity_2m,0,$lastIdx+1));
				$online[]=true;
				$plot[]=true;
				$devices[]= array("host_id"=>0, "location"=>"Outside", "mins"=>0);
				$N_meteo=1;

			}

			//STATS
			$stats=array();
			for ($i=0; $i < $N_devices; $i++) {
				$stats[]= getStatsById($conn, $devices[$i]["host_id"], 1);
			}
			//DENSITY
			$hists=array();
			for ($i=0; $i < $N_devices; $i++) {
				[$bins,$params]=getHistograms($conn, $devices[$i]["host_id"], 24);
				$hist_dates=array_keys(json_decode($params,true));
				$hists[]=[$hist_dates,$bins,$params];
			}
			$conn->close();
	  ?>
		<table class="normal">
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
				$N=$N_devices + $N_meteo;
				for ($i=0; $i < $N; $i++) {
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
			doPlot("plotlyDiv", Nitems, times, temp, labels, plots);
			doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);
			plotDensities("densityPlot",hoverBins, hoverParams, hoverLabels, hoverPlots);
		}

		//PLOTS
		var N_devices= <?php echo $N_devices; ?>;
		var Nitems= <?php echo $N; ?>;
		var times=[];
		var temp=[];
		var humi=[];
		var labels=[];
		var plots=[];
		<?php
			for ($i=0; $i < $N; $i++) {
				echo "times.push(".$times[$i].");\n";
				echo "temp.push(".$temp[$i].");\n";
				echo "humi.push(".$humi[$i].");\n";
				//echo "plots.push(".$plot[$i].");\n";
				if ($plot[$i]) {
					echo "plots.push(1);\n";
				}else{
					echo "plots.push(0);\n";
				}
				echo "labels.push('".$devices[$i]["location"]."');";
			}
		?>
		doPlot("plotlyDiv", Nitems, times, temp, labels, plots);
		//stats
		var stats= <?php echo json_encode($stats) ?>;
		doPlotStats("plotlyStatsDiv", N_devices, stats, labels, plots);
		//DENSITY PLOTS
		//https://plotly.com/javascript/hover-events/
	  var myPlot = document.getElementById('plotlyStatsDiv'),
	    hoverInfo = document.getElementById('hoverinfo');
		var hists = <?php echo json_encode($hists); ?>;

		var hoverIds=[];
		var hoverBins=[];
		var hoverParams=[];
		var hoverLabels=[];
		var hoverPlots=[];
	  myPlot.on('plotly_hover', function(data){
			hoverInfo.innerHTML = '';
	    var infotext = data.points.map(function(d){
				date = data.points[0].x;
				hoverBins=[];
				hoverParams=[];
				hoverLabels=[];
				hoverPlots=[];
				for (let i = 0; i < N_devices; i++) {
					if (hists[i][0].includes(date)){
						hoverBins.push(JSON.parse(hists[i][1])[date]);
						hoverParams.push(JSON.parse(hists[i][2])[date]);
						hoverLabels.push(labels[i]);
						hoverPlots.push(plots[i]);
					}
				}
				if (hoverBins.length > 0) {
					return('<div style="height: 800px; max-width:800;" id="densityPlot">');
				} else {
					return('<img src="plots/notFound.jpg">');
				}
			});
	    hoverInfo.innerHTML = infotext.join('<br/>');
			if (hoverBins.length > 0) {
				plotDensities("densityPlot",hoverBins, hoverParams, hoverLabels, hoverPlots);
			}
	  })
	  .on('plotly_unhover', function(data){
	    //hoverInfo.innerHTML = '';
	  });
		myPlot.on('plotly_click', function(data){
			hoverInfo.innerHTML = '';
	    var infotext = data.points.map(function(d){
				date = data.points[0].x;
				hoverBins=[];
				hoverParams=[];
				hoverLabels=[];
				hoverPlots=[];
				for (let i = 0; i < N_devices; i++) {
					if (hists[i][0].includes(date)){
						hoverBins.push(JSON.parse(hists[i][1])[date]);
						hoverParams.push(JSON.parse(hists[i][2])[date]);
						hoverLabels.push(labels[i]);
						hoverPlots.push(plots[i]);
					}
				}
				if (hoverBins.length > 0) {
					return('<div style="height: 800px; max-width:800;" id="densityPlot">');
				} else {
					return('<img src="plots/notFound.jpg">');
				}
			});
	    hoverInfo.innerHTML = infotext.join('<br/>');
			if (hoverBins.length > 0) {
				plotDensities("densityPlot",hoverBins, hoverParams, hoverLabels, hoverPlots);
			}
		});
	</script>

</body>
