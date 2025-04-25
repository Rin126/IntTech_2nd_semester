<?php
include("connect.php");

// Перевірка параметрів
if (!isset($_GET['project_id']) || empty($_GET['project_id']) || !isset($_GET['work_date']) || empty($_GET['work_date'])) {
    die("Помилка: необхідні параметри project_id і work_date!");
}

$project_id = $_GET['project_id'];
$selected_date = $_GET['work_date'];

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
        echo "<h4>Проєкт ID: " . htmlspecialchars($project_id) . "</h4>";
        echo "<p>Дата: " . htmlspecialchars($selected_date) . "</p>";
        echo "<div class='task-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<p class='task'>" . htmlspecialchars($row['description']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "<p>Немає виконаних завдань за цим проєктом на вказану дату.</p>";
    }
} catch(PDOException $ex) {
    echo "<p class='error'>Помилка: " . $ex->getMessage() . "</p>";
}

$dbh = null;
?>