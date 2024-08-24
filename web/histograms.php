<head>
	<!-- Load plotly.js into the DOM -->
	<script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
	<script src='include/jsPlots.js'></script>
  <title>HomeMeteo</title>
  <link rel="icon" type="image/png" href="favicon.png">
</head>

<body>
  <h1> Test histograms with Plotly </h1>
  <?php

		//Connect to database
		include("include/dbConn.php");
		include("include/dbQuerries.php");

		$today_s = date("Y-m-d");
		$day = new DateTime($today_s);
		$id=0;
		if(isset($_GET['id'])){
			$id = test_input($_GET['id']);
		}
		if(isset($_GET['date'])){
			$date = test_input($_GET['date']);
			$day = new DateTime($date);
		}

	 	[$bins,$params]=getHistograms($conn, $id, 1);
		$dates=array_keys(json_decode($params,true));
	  $conn->close();
  ?>
  <p><a href="index.php">Back</a> | <a href="https://github.com/andregtorres/homeMeteo">GitHub</a></p>

	<select id="dateSelect" onchange="onChangeDate()">
		<?php
			foreach ($dates as $date) {
				echo "<option value=".$date.">".$date."</option>";
			}
		 ?>
	</select>

  <div style="height: 800px; max-width:800;" id='densityPlot'> <!-- Plotly chart will be drawn inside this DIV --> </div>

  <script>

		var bins = <?php echo $bins; ?>;
		var histParams = <?php echo $params; ?>;
		function onChangeDate(){
			date = document.getElementById("dateSelect").value;
			console.log(date);
			plotDensity("densityPlot",date, bins, histParams);
		}

		onChangeDate();
	</script>
</body>
