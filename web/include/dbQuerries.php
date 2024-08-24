<?php
  include("histogramFunctions.php");

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
