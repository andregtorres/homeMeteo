<?php
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

    $interval = "5 DAY";
    //Get current date
    $today_s = date("Y-m-d");
    $today = new DateTime($today_s);
    echo "today: ".$today_s."\n";
    if(isset($_POST['id'])){
        $id = $_POST['id'];
        if(isset($_POST['interval'])){
          $interval = $_POST['interval'];
        }
        echo "id: ".$id."\n";
        echo "interval: ".$interval."\n";

        $sql = "DELETE FROM homeMeteoLogs WHERE host = ".$id." AND timestamp <= ( CURDATE() - INTERVAL ".$interval.")";
        echo $sql;
        $result = $conn->query($sql);

    } else {
        echo "Error - invalid host id\n";
      }

    $conn->close();
?>
