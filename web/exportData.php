<?php

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

    function processDay($conn, $id, $day_input){
      $nextDay=clone $day_input;
      $nextDay->modify('+1 day');
      $stmt = $conn->prepare("SELECT * FROM homeMeteoLogs WHERE (host = ? AND timestamp >= ?  AND timestamp < ? )");
      $stmt -> bind_param("sss", $id, $day_input->format("Y-m-d H:i:s"), $nextDay->format("Y-m-d H:i:s"));
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt -> close();
      //echo "DB OK got ".$result->num_rows." rows.\n";
      while($row = $result->fetch_assoc()) {
        $time_array[] = $row["timestamp"];
        $temp_array[] = $row["temp"];
        $humi_array[] = $row["humi"];
      }
      $out_array = array("host"=>$id, "day"=>$day_input->format("Y-m-d"),"time"=>$time_array,"temp"=>$temp_array,"humi"=>$humi_array);
      return json_encode($out_array);
    }


    //Connect to database
    include("include/dbConn.php");

    //Get current date
    //$today = new DateTime('now');
    $today_s = date("Y-m-d");
    $today = new DateTime($today_s);
    if(isset($_POST['id'])){
        $id = test_input($_POST['id']);
        if(!isset($_POST['startDate'])){
          $lastStatDay=getLastStatRow($conn, $id);
          $day = new DateTime($lastStatDay);
          $day->modify('+1 day');
        }else{
          $startDay = test_input($_POST['startDate']);
          $day = new DateTime($startDay);
        }
        $i=0;
        while($day <= $today){
          $outJson=processDay($conn, $id, $day);
          $arr[] = ["seq"=>$i, "payload"=>$outJson];
          $day->modify('+1 day');
          $i++;
        }
        $outJson = json_encode($arr);
        echo $outJson;
        echo "\n";
    } else {
        echo "Error - invalid host id\n";
      }
    $conn->close();
?>
