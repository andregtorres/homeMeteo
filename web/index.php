<style><?php include 'style.css'; ?></style>
<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
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
		const colors = [
			'#5988ff',
			'#ff8559',
			'#59ff8b',
			'#bd59ff',
			'#17becf'
		];
		const colorsTransp = [
			'rgba(89, 136, 255,0.2)',
			'rgba(255, 133, 89,0.2)',
			'rgba(89, 255, 139,0.2)',
			'rgba(189, 89, 255,0.2)',
			'rgba(23, 190, 207,0.2)'
		];
		function onChangeDevices(){
			var checkedBoxes = document.querySelectorAll('input[name=plotDevice]');
			for (var i = 0; i < checkedBoxes.length; i++) {
				plots[i]=checkedBoxes[i].checked;
			}
			doPlot();
			doPlotStats();
		}
		function doPlot(){
			var data=[];
			for (var i = 0; i < N_devices ; i++) {
				var trace1 ={
						x:times[i],
						y:temp[i],
						mode:'lines+markers',
						name:labels[i],
						marker: {
							color:colors[i],
						},
				};
				var trace2 ={
						x:times[i],
						y:humi[i],
						mode:'lines+markers',
						name: labels[i],
						yaxis: 'y2',
						marker: {
							color:colors[i],
						},
						showlegend: false,
				};
				if (plots[i]){
					data.push(trace1,trace2);
				}

			}
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
			Plotly.newPlot('plotlyDiv', data, layout);
		}

		function doPlotStats(){
		  var d3colors = Plotly.d3.scale.category10();
			var dataStats=[];
			for (var i = 0; i < N_devices ; i++) {
				var devStats=JSON.parse(stats[i.toString()])
				var trace1 ={
			    x:devStats["day"],
			    y:devStats["t_q25"],
			    line: {color: "transparent"},
			    fillcolor: colorsTransp[i],
			    name: "q25",
			    showlegend: false,
			    type: "scatter",
			    mode: 'lines',
					hoverinfo:"x+y"
			  };
			  var trace2 ={
			    x:devStats["day"],
			    y:devStats["t_avg"],
			    mode:'lines+markers',
			    name:labels[i],
			    line: {color:colors[i]},
			    marker: {color:colors[i]},
			    fillcolor: colorsTransp[i],
			    type: "scatter",
			    fill: "tonexty",
					hoverinfo:"x+y"
			  };
			  var trace3 ={
			    x:devStats["day"],
			    y:devStats["t_q75"],
			    fill: "tonexty",
			    fillcolor: colorsTransp[i],
			    line: {color: "transparent"},
			    name: "q75",
			    showlegend: false,
			    type: "scatter",
			    mode: 'lines',
					hoverinfo:"x+y"
			  };

			  var trace4 ={
					x:devStats["day"],
			    y:devStats["h_q25"],
			    line: {color: "transparent"},
			    fillcolor: colorsTransp[i],
			    name: "q25",
			    showlegend: false,
			    type: "scatter",
			    mode: 'lines',
			    yaxis: 'y2',
					hoverinfo:"x+y"
			  };
			  var trace5 ={
					x:devStats["day"],
					y:devStats["h_avg"],
			    mode:'lines+markers',
			    yaxis: 'y2',
			    line: {color:colors[i]},
			    marker: {color:colors[i]},
			    fillcolor: colorsTransp[i],
					showlegend: false,
			    type: "scatter",
			    fill: "tonexty",
					hoverinfo:"x+y"
			  };
			  var trace6 ={
					x:devStats["day"],
			    y:devStats["h_q75"],
			    fill: "tonexty",
			    fillcolor: colorsTransp[i],
			    line: {color: "transparent"},
			    name: "q75",
			    showlegend: false,
			    type: "scatter",
			    mode: 'lines',
			    yaxis: 'y2',
					hoverinfo:"x+y"
			  };
				if (plots[i]){
					dataStats.push(trace1,trace2,trace3,trace4,trace5,trace6);
				}
			}
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
		  Plotly.newPlot('plotlyStatsDiv', dataStats, layout);
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
		doPlot()
		var stats= <?php echo json_encode($stats) ?>;
		doPlotStats()

	</script>

</body>
