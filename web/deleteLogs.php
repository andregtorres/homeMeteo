<?php
    //Connect to database
    include("include/dbConn.php");

    $interval = "5";
    //Get current date
    $today_s = date("Y-m-d");
    $today = new DateTime($today_s);
    echo "today: ".$today_s."\n";
    if(isset($_POST['id'])){
        $id = test_input($_POST['id']);
        if(isset($_POST['interval'])){
          $interval = test_input($_POST['interval']);
        }
        echo "id: ".$id."\n";
        echo "interval: ".$interval." DAY\n";
        try {
          $stmt = $conn->prepare("DELETE FROM homeMeteoLogs WHERE host = ? AND timestamp <= (CURDATE() - INTERVAL ? DAY)");
          $stmt -> bind_param("ss", $id, $interval);
          $stmt->execute();
          $stmt -> close();
        }catch (Exception $e){
          $error = $e->getMessage();
          echo "ERROR: ";
          echo $error;
          echo "\n";
        }
        echo "DELETED ROWS";
    } else {
        echo "Error - invalid host id\n";
      }

    $conn->close();
?>
