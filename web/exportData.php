<?php

    function getLastStatRow($conn, $id){
      $sql = "SELECT * FROM homeMeteoStats WHERE id = ".$id." ORDER BY day DESC LIMIT 1";
      $result = $conn->query($sql);
      //echo " DB OK got ".$result->num_rows." row.\n";
      $row = $result->fetch_assoc();
      //echo "last row in stat " .$row["day"]. "\n";
      return($row["day"]);
    }

    function processDay($conn, $id, $day_input){
      $nextDay=clone $day_input;
      $nextDay->modify('+1 day');
      $sql = "SELECT * FROM homeMeteoLogs WHERE (host = ".$id." AND timestamp >='".$day_input->format("Y-m-d H:i:s")."' AND timestamp <'".$nextDay->format("Y-m-d H:i:s")."')";
      $result = $conn->query($sql);
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
    $servername = "";
    $username = "";
    $password = "";
    $dbname = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }

    //Get current date
    //$today = new DateTime('now');
    $today_s = date("Y-m-d");
    $today = new DateTime($today_s);
    //echo "today: ".$today_s."\n";
    if(isset($_POST['id'])){
        $id = $_POST['id'];
        //echo "id: ".$id."\n";
        if(!isset($_POST['startDate'])){
          $lastStatDay=getLastStatRow($conn, $id);
          //echo "last row in stat ".$lastStatDay."\n";
          $day = new DateTime($lastStatDay);
          $day->modify('+1 day');
        }else{
          $startDay=$_POST['startDate'];
          $day = new DateTime($startDay);
        }
        //echo "first day to processs ".$day->format("Y-m-d")."\n";
        $i=0;
        while($day < $today){
          //echo "   ".$day->format("Y-m-d")."\n";
          $outJson=processDay($conn, $id, $day);
          $arr[] = ["seq"=>$i, "payload"=>$outJson];

          $day->modify('+1 day');
          $i++;
        }
        $outJson = json_encode($arr);
        echo $outJson;

    } else {
        echo "Error - invalid host id\n";
      }

    $conn->close();
?>
