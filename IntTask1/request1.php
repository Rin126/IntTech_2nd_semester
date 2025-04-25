<?php
include("connect.php");
include("connect_log.php");

if (!isset($_GET['project_id']) || empty($_GET['project_id']) || !isset($_GET['work_date']) || empty($_GET['work_date'])) {
    die("Помилка: необхідні параметри project_id і work_date!");
}

$project_id = $_GET['project_id'];
$selected_date = $_GET['work_date'];

try {
    $logStmt = $logDbh->prepare("INSERT INTO request_logs 
        (request_url, endpoint, param1_name, param1_value, param2_name, param2_value, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $logStmt->execute([
        $_SERVER['REQUEST_URI'],
        'request1.php',
        'project_id',
        $project_id,
        'work_date',
        $selected_date,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
} catch(PDOException $e) {
    error_log("Помилка логування: " . $e->getMessage());
}

echo "Проєкт ID: " . htmlspecialchars($project_id) . "<br>";
echo "Дата: " . htmlspecialchars($selected_date) . "<br><br>";

try {
    $sqlSelect = "SELECT w.description 
                  FROM work w
                  WHERE w.FID_PROJECTS = :project_id
                  AND w.date = :selected_date";

    $stmt = $dbh->prepare($sqlSelect);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':selected_date', $selected_date, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "<div>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "Немає виконаних завдань за цим проєктом на вказану дату.";
    }
} catch(PDOException $ex) {
    echo "Помилка: " . $ex->getMessage();
}

$dbh = null;
$logDbh = null;
?>