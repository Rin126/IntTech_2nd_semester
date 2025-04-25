<?php
include("connect.php");
include("connect_log.php");

$chief_name = isset($_GET['chief']) ? $_GET['chief'] : '';

try {
    $logStmt = $logDbh->prepare("INSERT INTO request_logs 
        (request_url, endpoint, param1_name, param1_value, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $logStmt->execute([
        $_SERVER['REQUEST_URI'],
        'request3.php',
        'chief',
        $chief_name,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
} catch(PDOException $e) {
    error_log("Помилка логування: " . $e->getMessage());
}

echo "Chief: " . $chief_name;
echo "<br><br>";

try {
    $sqlSelect = "SELECT d.chief AS department_chief, COUNT(w.ID_WORKER) AS workers_count 
                  FROM department d 
                  JOIN worker w ON d.ID_DEPARTMENT = w.FID_DEPARTMENT 
                  WHERE d.chief = :chief_name 
                  GROUP BY d.chief";
    
    $stmt = $dbh->prepare($sqlSelect);
    $stmt->bindParam(':chief_name', $chief_name, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>Department Chief</th>";
        echo "<th>Number of Workers</th>";
        echo "</tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['department_chief'] . "</td>";
            echo "<td>" . $row['workers_count'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No results found!";
    }
} catch(PDOException $ex) {
    echo "Error: " . $ex->getMessage();
}

$dbh = null;
$logDbh = null;
?>