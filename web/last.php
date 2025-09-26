<head>

</head>

<body>
  <?php
  //Connect to database
  include("include/dbConn.php");

  $sql1 = "SELECT timestamp, temp, humi FROM homeMeteoLogs WHERE host = 1 AND timestamp >= ( CURDATE() - INTERVAL 0 DAY)";
  $result1 = $conn->query($sql1);
  if ($result1->num_rows > 0) {
		//this is a bit stupid, but i forgot to add an auto incrementing id to the table :(
    while($row = $result1->fetch_assoc()) {
      $time = $row["timestamp"];
      $temp = $row["temp"];
      $humi = $row["humi"];
    }
		echo $time . "<br>";
		echo $temp/100 . "<br>";
		echo $humi/100 . "<br>";
  } else {
    echo "Sorry, no data collected today.";
  }

  $conn->close();
  ?>
</body>
