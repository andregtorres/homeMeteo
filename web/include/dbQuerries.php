<?php
  include("histogramFunctions.php");

  function getDaysById($conn, $id, $days) {
    $days--;

    $time_array = Array();
    $temp_array = Array();
    $humi_array = Array();

    if (!$conn){
       echo 'Connection attempt failed.<br>';
    }
    $stmt= $conn->prepare("SELECT timestamp, temp, humi FROM homeMeteoLogs WHERE host = ? AND timestamp >= CURRENT_DATE() - INTERVAL ? DAY  ORDER BY 'timestamp'");
    $stmt -> bind_param("ss", $id, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
     // output data of each row
     while($row = $result->fetch_assoc()) {
       $time_array[] = $row["timestamp"];
       $temp_array[] = $row["temp"]/100;
       $humi_array[] = $row["humi"]/100;
     }
    }else{
      echo "No results<br>";
    }
    //convert the PHP array into JSON format, so it works with javascript
    $json_time = json_encode($time_array);
    $json_temp = json_encode($temp_array);
    $json_humi = json_encode($humi_array);
    return [$json_time, $json_temp, $json_humi];
  }

  function getStatsById($conn, $id, $years) {
    #CREATE TABLE homeMeteoStats( day DATE, id TINYINT UNSIGNED,
    #        t_avg DOUBLE, t_std DOUBLE, t_median DOUBLE, t_min DOUBLE, t_max DOUBLE, t_q25 DOUBLE, t_q75 DOUBLE
    #        h_avg DOUBLE, h_std DOUBLE, h_median DOUBLE, h_min DOUBLE, h_max DOUBLE, h_q25 DOUBLE, h_q75 DOUBLE);
    $sql = "SELECT day, t_avg, t_q25, t_q75,
            h_avg, h_q25, h_q75
            FROM homeMeteoStats WHERE id = ?  AND
            day >= ( CURDATE() - INTERVAL ? YEAR) ORDER BY day";
    $stmt= $conn->prepare($sql);
    $stmt -> bind_param("ss", $id, $years);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt -> close();
    if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
        $day_array[] = $row["day"];
        $t_avg_array[] = $row["t_avg"];
        $t_q25_array[] = $row["t_q25"];
        $t_q75_array[] = $row["t_q75"];
        $h_avg_array[] = $row["h_avg"];
        $h_q25_array[] = $row["h_q25"];
        $h_q75_array[] = $row["h_q75"];
      }
    } else {
      echo "No statistics for id ". $id . "<br>";
    }
    //convert the PHP array into JSON format, so it works with javascript
    //$json_day = json_encode($day_array);
    //$json_t_avg = json_encode($t_avg_array);
    //$json_t_q25 = json_encode($t_q25_array);
    //$json_t_q75 = json_encode($t_q75_array);
    //$json_h_avg = json_encode($h_avg_array);
    //$json_h_q25 = json_encode($h_q25_array);
    //$json_h_q75 = json_encode($h_q75_array);
    //return [$json_day, $json_t_avg, $json_t_q25, $json_t_q75, $json_h_avg, $json_h_q25, $json_h_q75];
    $json_stats = json_encode(array("day"=>$day_array,"t_avg"=>$t_avg_array,"t_q25"=>$t_q25_array,"t_q75"=>$t_q75_array,"h_avg"=>$h_avg_array,"h_q25"=>$h_q25_array,"h_q75"=>$h_q75_array));
    return [$json_stats];
  }
  function getStatsById_full($conn, $id, $years) {
    $day_array[] 	 		= Array();
    $t_avg_array[] 		= Array();
    $t_std_array[] 		= Array();
    $t_median_array[] = Array();
    $t_min_array[] 		= Array();
    $t_max_array[] 		= Array();
    $t_q25_array[] 		= Array();
    $t_q75_array[] 		= Array();
    $h_avg_array[] 		= Array();
    $h_std_array[] 		= Array();
    $h_median_array[] = Array();
    $h_min_array[] 		= Array();
    $h_max_array[] 		= Array();
    $h_q25_array[] 		= Array();
    $h_q75_array[] 		= Array();
    #CREATE TABLE homeMeteoStats( day DATE, id TINYINT UNSIGNED,
    #        t_avg DOUBLE, t_std DOUBLE, t_median DOUBLE, t_min DOUBLE, t_max DOUBLE, t_q25 DOUBLE, t_q75 DOUBLE
    #        h_avg DOUBLE, h_std DOUBLE, h_median DOUBLE, h_min DOUBLE, h_max DOUBLE, h_q25 DOUBLE, h_q75 DOUBLE);
    $sql = "SELECT day, t_avg, t_std, t_median, t_min, t_max, t_q25, t_q75,
            h_avg, h_std, h_median, h_min, h_max, h_q25, h_q75
            FROM homeMeteoStats WHERE id = ?  AND
            day >= ( CURDATE() - INTERVAL ? YEAR) ORDER BY day";
    $stmt= $conn->prepare($sql);
    $stmt -> bind_param("ss", $id, $years);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt -> close();
    if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
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
    } else {
      echo "No statistics for id ". $id . "<br>";
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
    return [$json_day, $json_t_avg, $json_t_std, $json_t_median, $json_t_min, $json_t_max, $json_t_q25, $json_t_q75, $json_h_avg, $json_h_std, $json_h_median, $json_h_min, $json_h_max, $json_h_q25, $json_h_q75];
  }

  function getLastStatRow($conn, $id){
    $stmt = $conn->prepare("SELECT * FROM homeMeteoStats WHERE id =? ORDER BY day DESC LIMIT 1");
    $stmt -> bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    //$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $row = $result->fetch_assoc();
    $stmt -> close();
    return($row["day"]);
  }

  function checkDay($conn, $id, $day_input){
    $nextDay=clone $day_input;
    $nextDay->modify('+1 day');
    $stmt = $conn->prepare("SELECT * FROM homeMeteoLogs WHERE (host = ? AND timestamp >= ?  AND timestamp < ? )");
    $stmt -> bind_param("sss", $id, $day_input->format("Y-m-d H:i:s"), $nextDay->format("Y-m-d H:i:s"));
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt -> close();
    echo "DB OK got ".$result->num_rows." rows.\n";
    return($result->num_rows>0);
  }

  function getHistogram($conn, $id, $day){
    $sql1 = "SELECT
							B.temp, B.humi, B.measurements,
							H.acq_time,
							H.x_min, H.x_max, H.dx, H.Nx,
							H.y_min, H.y_max, H.dy, H.Ny
							FROM homeMeteoBins as B
							INNER JOIN homeMeteoHistograms as H
							ON B.id_hist = H.id_hist
							WHERE H.id_host = ".$id."
							AND B.date='".$day->format('Y-m-d')."'
							ORDER BY B.id_bins DESC LIMIT 1";
	  $result1 = $conn->query($sql1);
	  if ($result1->num_rows > 0) {
	    $row = $result1->fetch_assoc();
			$x=array_map('intval', explode(" ", $row["temp"]));
			$y=array_map('intval', explode(" ", $row["humi"]));
			$z=array_map('intval', explode(" ", $row["measurements"]));
			$bins_2d=decompress2d($x,$y, $z,$row["Nx"],$row["Ny"]);
			$json_bins_2d = json_encode($bins_2d);
			$json_histParams = json_encode(array("x_min"=>$row["x_min"],"x_max"=>$row["x_max"],"dx"=>$row["dx"],
																						"y_min"=>$row["y_min"],"y_max"=>$row["y_max"],"dy"=>$row["dy"]));
	  } else {
	    return NULL;
	  }
    return [$json_bins_2d,$json_histParams];
  }

  function getHistograms($conn, $id, $months){
    $bins  = array();
    $params = array();
    $sql1 = "SELECT
							B.temp, B.humi, B.measurements, B.date,
							H.acq_time,
							H.x_min, H.x_max, H.dx, H.Nx,
							H.y_min, H.y_max, H.dy, H.Ny
							FROM homeMeteoBins as B
							INNER JOIN homeMeteoHistograms as H
							ON B.id_hist = H.id_hist
							WHERE H.id_host = ".$id."
							AND B.date > DATE(NOW()) - INTERVAL ".$months." MONTH
              ORDER BY B.id_bins ASC";

	  $result1 = $conn->query($sql1);
	  while($row = $result1->fetch_array(MYSQLI_ASSOC)) {
  		$x=array_map('intval', explode(" ", $row["temp"]));
			$y=array_map('intval', explode(" ", $row["humi"]));
			$z=array_map('intval', explode(" ", $row["measurements"]));
			$bins_2d=decompress2d($x,$y, $z,$row["Nx"],$row["Ny"]);
			$histParams = array("x_min"=>$row["x_min"],"x_max"=>$row["x_max"],"dx"=>$row["dx"],
																						"y_min"=>$row["y_min"],"y_max"=>$row["y_max"],"dy"=>$row["dy"]);

      $bins[$row["date"]]=$bins_2d;
      $params[$row["date"]]=$histParams;
    }
	  return [json_encode($bins),json_encode($params)];
  }

?>
