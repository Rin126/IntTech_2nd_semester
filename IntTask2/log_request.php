<?php
include("connect.php");

$request_url = $_POST['request_url'] ?? '';
$browser_info = $_POST['browser_info'] ?? '';
$user_coordinates = $_POST['user_coordinates'] ?? '';
$client_ip = $_SERVER['REMOTE_ADDR'];
$request_time = date('Y-m-d H:i:s');

try {
    $sql = "INSERT INTO request_logs (request_time, request_url, browser_info, user_coordinates, client_ip) 
            VALUES (:request_time, :request_url, :browser_info, :user_coordinates, :client_ip)";
    
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':request_time', $request_time);
    $stmt->bindParam(':request_url', $request_url);
    $stmt->bindParam(':browser_info', $browser_info);
    $stmt->bindParam(':user_coordinates', $user_coordinates);
    $stmt->bindParam(':client_ip', $client_ip);
    
    $stmt->execute();
    
    echo "Logged successfully";
} catch(PDOException $ex) {
    error_log("Error logging request: " . $ex->getMessage());
    http_response_code(500);
    echo "Error logging request";
}

$dbh = null;
?>