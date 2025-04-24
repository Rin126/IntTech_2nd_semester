<?php
include("connect.php");

header('Content-Type: application/xml');

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';

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

    $xml = new SimpleXMLElement('<results/>');
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $project = $xml->addChild('project');
            $project->addChild('name', htmlspecialchars($row['project_name']));
            $project->addChild('time_start', htmlspecialchars($row['time_start']));
            $project->addChild('time_end', htmlspecialchars($row['time_end']));
            $project->addChild('total_days', htmlspecialchars($row['total_days']));
        }
    }
    
    echo $xml->asXML();
} catch(PDOException $ex) {
    $xml = new SimpleXMLElement('<error/>');
    $xml->addChild('message', 'Error: ' . $ex->getMessage());
    echo $xml->asXML();
}

$dbh = null;
?>