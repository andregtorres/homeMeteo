<?php
header('Content-Type: application/json; charset=utf-8');
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
    $data = [ 'temp' => round($temp/100, 1), 'humi' => round($humi/100, 0), "status" => "OK" ];    

} else {
    $data = [ 'temp' => 0, 'humi' => 0, "status" => "NOT OK" ];    
}
echo json_encode($data);

$conn->close();
?>

