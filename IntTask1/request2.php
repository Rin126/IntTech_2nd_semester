<?php
include("connect.php");
include("connect_log.php");

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';

try {
    $logStmt = $logDbh->prepare("INSERT INTO request_logs 
        (request_url, endpoint, param1_name, param1_value, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $logStmt->execute([
        $_SERVER['REQUEST_URI'],
        'request2.php',
        'project_id',
        $project_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
} catch(PDOException $e) {
    error_log("Помилка логування: " . $e->getMessage());
}

echo "Project ID: " . $project_id;
echo "<br><br>";

try {
    $sqlSelect = "SELECT p.name AS project_name, 
                         w.time_start, 
                         w.time_end, 
                         SUM(DATEDIFF(w.time_end, w.time_start)) AS total_days
                  FROM work w
                  JOIN project p ON w.FID_PROJECTS = p.ID_PROJECTS
                  WHERE w.FID_PROJECTS = :project_id
                  GROUP BY p.name, w.time_start, w.time_end";

    $stmt = $dbh->prepare($sqlSelect);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr><th>Project Name</th><th>Time Start</th><th>Time End</th><th>Total Days</th></tr>";
        echo "</thead>";
        echo "<tbody>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['time_start']) . "</td>";
            echo "<td>" . htmlspecialchars($row['time_end']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_days']) . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
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