<?php
include("connect.php");

header('Content-Type: application/json');

$chief_name = isset($_GET['chief']) ? $_GET['chief'] : '';

try {
    $sqlSelect = "SELECT d.chief AS department_chief, COUNT(w.ID_WORKER) AS workers_count 
                  FROM department d 
                  JOIN worker w ON d.ID_DEPARTMENT = w.FID_DEPARTMENT 
                  WHERE d.chief = :chief_name 
                  GROUP BY d.chief";
    
    $stmt = $dbh->prepare($sqlSelect);
    $stmt->bindParam(':chief_name', $chief_name, PDO::PARAM_STR);
    $stmt->execute();
    
    $results = array();
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = array(
                'department_chief' => $row['department_chief'],
                'workers_count' => $row['workers_count']
            );
        }
    }
    
    echo json_encode($results);
} catch(PDOException $ex) {
    echo json_encode(array('error' => $ex->getMessage()));
}

$dbh = null;
?>