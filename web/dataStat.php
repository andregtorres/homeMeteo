<?php

    //from https://blog.poettner.de/2011/06/09/simple-statistics-with-php/
    function Median($Array) {
      return Quartile_50($Array);
    }

    function Quartile_25($Array) {
      return Quartile($Array, 0.25);
    }

    function Quartile_50($Array) {
      return Quartile($Array, 0.5);
    }

    function Quartile_75($Array) {
      return Quartile($Array, 0.75);
    }

    function Quartile($Array, $Quartile) {
      sort($Array);
      $pos = (count($Array) - 1) * $Quartile;

      $base = floor($pos);
      $rest = $pos - $base;

      if( isset($Array[$base+1]) ) {
        return $Array[$base] + $rest * ($Array[$base+1] - $Array[$base]);
      } else {
        return $Array[$base];
      }
    }

    function Average($Array) {
      return array_sum($Array) / count($Array);
    }

    function StdDev($Array) {
      if( count($Array) < 2 ) {
        return;
      }

      $avg = Average($Array);

      $sum = 0;
      foreach($Array as $value) {
        $sum += pow($value - $avg, 2);
      }

      return sqrt((1 / (count($Array) - 1)) * $sum);
    }

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
      echo "DB OK got ".$result->num_rows." rows.\n";
      while($row = $result->fetch_assoc()) {
        $temp_array[] = $row["temp"];
        $humi_array[] = $row["humi"];
      }
      $t_avg=Average($temp_array)/100.;
      $t_std=StdDev($temp_array)/100.;
      $t_min=min($temp_array)/100;
      $t_max=max($temp_array)/100;
      $t_median=Median($temp_array)/100.;
      $t_q25=Quartile_25($temp_array)/100.;
      $t_q75=Quartile_75($temp_array)/100.;
      $h_avg=Average($humi_array)/100.;
      $h_std=StdDev($humi_array)/100.;
      $h_min=min($humi_array)/100;
      $h_max=max($humi_array)/100;
      $h_median=Median($humi_array)/100.;
      $h_q25=Quartile_25($humi_array)/100.;
      $h_q75=Quartile_75($humi_array)/100.;
      //echo "t_avg=".$t_avg." t_std=".$t_std." t_median=".$t_median."t_min=".$t_min." t_max=".$t_max." t_q25=".$t_q25." t_q75=".$t_q75."\n";
      return[$t_min,$t_max,$t_avg,$t_std,$t_median,$t_q25,$t_q75,$h_min,$h_max,$h_avg,$h_std,$h_median,$h_q25,$h_q75];
    }


    //Connect to database
    $servername = "localhost";
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
    echo "today: ".$today_s."\n";
    if(isset($_POST['id'])){
        $id = $_POST['id'];
        echo "id: ".$id."\n";

        $lastStatDay=getLastStatRow($conn, $id);
        echo "last row in stat ".$lastStatDay."\n";

        $day = new DateTime($lastStatDay);
        $day->modify('+1 day');
        echo "first day to processs ".$day->format("Y-m-d")."\n";
        while($day < $today){
          echo "   ".$day->format("Y-m-d")."\n";
          [$t_min,$t_max,$t_avg,$t_std,$t_median,$t_q25,$t_q75,$h_min,$h_max,$h_avg,$h_std,$h_median,$h_q25,$h_q75]=processDay($conn, $id, $day);
          //Put in dB
          $day_s=$day->format("Y-m-d");
          $sql = "INSERT INTO homeMeteoStats (day, id, t_avg, t_std, t_median, t_min, t_max, t_q25, t_q75,  h_avg, h_std, h_median, h_min, h_max, h_q25, h_q75) VALUES('".$day_s."',".$id.",".$t_avg.",".$t_std.",".$t_median.",".$t_min.",".$t_max.",".$t_q25.",".$t_q75.",".$h_avg.",".$h_std.",".$h_median.",".$h_min.",".$h_max.",".$h_q25.",".$h_q75.")";

          if ($conn->query($sql) === TRUE) {
              echo "DB OK - INSERTED DAY\n";
          } else {
              echo "Error: " . $sql . "<br>" . $conn->error;
          }
          $day->modify('+1 day');
        }

    } else {
        echo "Error - invalid host id\n";
      }


    $conn->close();
?>
