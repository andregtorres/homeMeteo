<?php
//Creates new record as per request
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
    //Connect to database
    include("include/dbConn.php");

    //Get current date and time
    $timestamp = date("Y-m-d H:i:s");
    if(!empty($_POST['temp'])){
        $id = test_input($_POST['id']);
        $temp = test_input($_POST['temp']);
        $humi = test_input($_POST['humi']);

        if (intval($temp) < 4500){
          $stmt = $conn->prepare("INSERT INTO homeMeteoLogs (timestamp,host,temp,humi) VALUES (?,?,?,?)");
          $stmt -> bind_param("ssss", $timestamp, $id, $temp, $humi);
          $stmt->execute();
          echo "DB OK\n";
          $stmt -> close();
        }  else {
            echo "Sensor Error\n";
          }
    } else {
        echo "Error\n";
      }
    $conn->close();
?>
