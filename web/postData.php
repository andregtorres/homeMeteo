<?php
//Creates new record as per request
    //Connect to database
    $servername = "db.tecnico.ulisboa.pt";
    $username = "";
    $password = "";
    $dbname = "";


    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }


    //Get current date and time
    $timestamp = date("Y-m-d H:i:s");
    if(!empty($_POST['temp']))
    {
        $id = $_POST['id'];
        $temp = $_POST['temp'];
        $humi = $_POST['humi'];
        $sql = "INSERT INTO homeMeteoLogs (timestamp,host,temp,humi) VALUES ('".$timestamp."', '".$id."', '".$temp."', '".$humi."')";
        //echo $timestamp;

        if (intval($temp) < 4500){
          if ($conn->query($sql) === TRUE) {
              echo "DB OK";
          } else {
              echo "Error: " . $sql . "<br>" . $conn->error;
          }
        }  else {
            echo "Sensor Error\n";
          }
    } else {
        echo "Error\n";
      }


    $conn->close();
?>
