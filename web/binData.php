<?php
	/*
	Bins the data to save space on archiving
	Andre Torres
	22/08/24

	usage:
	curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=' -d 'date='  https://homemeteo.atorres.eu/binData.php
	*/
	//Connect to database
	include("include/dbConn.php");
	//include("include/histogramFunctions.php"); dbQuerries already includes
	include("include/dbQuerries.php");

	$today_s = date("Y-m-d");
	$today = new DateTime($today_s);
	$endDate= $today;
	$id=0;
	if(isset($_POST['id'])){
		$id = test_input($_POST['id']);
	}
	if(!isset($_POST['date'])){
		$lastStatDay=getLastStatRow($conn, $id);
		echo "last row in stat ".$lastStatDay."\n";
		$day = new DateTime($lastStatDay);
		$day->modify('+1 day');
	}else{
		$date = test_input($_POST['date']);
		$day = new DateTime($date);
		$endDate=clone($day);
		$endDate->modify('+1 day');
	}

	echo "First day to processs ".$day->format("Y-m-d")."\n";
	echo "Until (not included) ".$endDate->format("Y-m-d")."\n";
	while($day < $endDate){
		echo "   ".$day->format("Y-m-d")."\n";
		$nextDay= clone($day);
		$nextDay->modify('+1 day');
		$sql1 = "SELECT timestamp, temp, humi
							FROM homeMeteoLogs
							WHERE host = 0
							AND timestamp >= '". $day->format('Y-m-d H:i:s') .
							"' AND timestamp < '". $nextDay->format('Y-m-d H:i:s') . "'";
		$sql2 = "SELECT *
							FROM homeMeteoHistograms
							WHERE id_host = 0
							AND from_date <= (CURDATE()- INTERVAL 4 DAY)
							AND (to_date >= CURDATE()- INTERVAL 3 DAY
							OR to_date IS NULL)";
		$sql3 = "INSERT INTO homeMeteoBins (id_hist,date,measurements,temp,humi)
							VALUES (?,?,?,?,?)";
		$result1 = $conn->query($sql1);
		$result2 = $conn->query($sql2);
		$histParams = $result2->fetch_assoc();

		$time_array = Array();
		$temp_array = Array();
		$humi_array = Array();

		if ($result1->num_rows > 0) {
			// output data of each row
			while($row = $result1->fetch_assoc()) {
		  	//echo "timestamp: " . $row[$timestamp] . "temp: " . $row["temp"]. " - humi: " . $row["humi"]. "<br>";
		  	$time_array[] = $row["timestamp"];
		  	$temp_array[] = $row["temp"];
		  	$humi_array[] = $row["humi"];
		  }
			$n_meas=count($time_array);
			//$temp_bins=binify($temp_array,$histParams["x_min"],$histParams["x_min"],$histParams["dx"]);
			$bins_2d=binify2d($temp_array,$humi_array,
												$histParams["x_min"],$histParams["x_max"],$histParams["dx"],
												$histParams["y_min"],$histParams["y_max"],$histParams["dy"]);
			[$x,$y, $z]= compress2d($bins_2d);
			$stmt = $conn->prepare($sql3);
			$stmt -> bind_param("sssss", $histParams["id_hist"], $day->format('Y-m-d'), implode(" ",$z), implode(" ",$x), implode(" ",$y));
			$stmt->execute();
			$stmt -> close();
			echo "DB OK - INSERTED DAY\n";
		}else {
			echo "No resuts, skipping";
		}
		$day->modify('+1 day');
	}
	$conn->close();
?>
