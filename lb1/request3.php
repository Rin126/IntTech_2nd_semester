<?php
include("connect.php");

$chief_name = $_GET['chief'];
echo "Chief: " . $chief_name;
echo "<br><br>";

try {
    $chief_name = isset($_GET['chief']) ? $_GET['chief'] : '';
    
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
?>
