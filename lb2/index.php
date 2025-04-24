<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

// Підключення до MongoDB
$client = new Client("mongodb://localhost:27017");
$db = $client->task_management;
$tasksCollection = $db->tasks;
$projectsCollection = $db->projects;

// Функція для відображення завдань
function displayTasks($cursor) {
    $html = "";
    foreach ($cursor as $task) {
        $html .= "<div style='margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid #eee'>";
        $html .= "<div><strong>Проєкт:</strong> ".($task['project_name'] ?? 'Невідомо')."</div>";
        $html .= "<div><strong>Завдання:</strong> ".($task['title'] ?? 'Невідомо')."</div>";
        $html .= "<div><strong>Виконавці:</strong> ".implode(", ", (array)$task['employees'] ?? [])."</div>";
        
        $startTime = $task['start_time']->toDateTime()->format('Y-m-d H:i');
        $endTime = $task['end_time']->toDateTime()->format('Y-m-d H:i');
        $html .= "<div><strong>Період:</strong> $startTime - $endTime</div>";
        $html .= "</div>";
    }
    return $html;
}

// Обробка запитів
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchType = $_POST['search_type'] ?? '';
    $result = "";
    
    switch ($searchType) {
        case 'project_tasks':
            $projectName = $_POST['project_name'] ?? '';
            $date = $_POST['date'] ?? '';
            
            if (!empty($projectName) && !empty($date)) {
                try {
                    $startOfDay = new UTCDateTime(new DateTime($date.' 00:00:00'));
                    $endOfDay = new UTCDateTime(new DateTime($date.' 23:59:59'));
                    
                    $tasks = $tasksCollection->find([
                        'project_name' => $projectName,
                        '$or' => [
                            ['start_time' => ['$gte' => $startOfDay, '$lte' => $endOfDay]],
                            ['end_time' => ['$gte' => $startOfDay, '$lte' => $endOfDay]]
                        ]
                    ]);
                    
                    $result .= "<h3>Завдання проекту '$projectName' за $date</h3>";
                    $result .= displayTasks($tasks);
                    
                    // Збереження в історію
                    $historyItem = [
                        'type' => 'project_tasks',
                        'title' => "Проект: $projectName, Дата: $date",
                        'html' => $result,
                        'time' => time()
                    ];
                } catch (Exception $e) {
                    $result .= "<p>Помилка: ".$e->getMessage()."</p>";
                }
            }
            break;
            
        case 'manager_projects':
            $managerName = $_POST['manager_name_projects'] ?? '';
            
            if (!empty($managerName)) {
                $projectsCount = $projectsCollection->countDocuments(['manager' => $managerName]);
                $result .= "<h3>Кількість проектів: $projectsCount</h3>";
                
                $projects = $projectsCollection->find(['manager' => $managerName]);
                foreach ($projects as $project) {
                    $result .= "<div>".$project['name']."</div>";
                }
                
                // Збереження в історію
                $historyItem = [
                    'type' => 'manager_projects',
                    'title' => "Керівник: $managerName",
                    'html' => $result,
                    'time' => time()
                ];
            }
            break;
            
        case 'manager_employees':
            $managerName = $_POST['manager_name_employees'] ?? '';
            
            if (!empty($managerName)) {
                $employees = $tasksCollection->distinct('employees', ['manager' => $managerName]);
                $result .= "<h3>Співробітники:</h3>";
                foreach ($employees as $employee) {
                    $result .= "<div>$employee</div>";
                }
                
                // Збереження в історію

                $historyItem = [
                    'type' => 'manager_employees',
                    'title' => "Співробітники $managerName",
                    'html' => $result,
                    'time' => time()
                ];
            }
            break;
    }
    
    // Вивід результату
    echo "<div style='margin:20px;padding:15px;background:#f5f5f5'>";
    echo $result ? $result : "<p>Немає результатів</p>";
    echo "<p><a href='".$_SERVER['PHP_SELF']."'>Назад</a></p>";
    echo "</div>";
    
    // Збереження історії
    if (isset($historyItem)) {
        echo "<script>
            let history = JSON.parse(localStorage.getItem('taskHistory')) || [];
            history.unshift(".json_encode($historyItem).");
            if (history.length > 5) history = history.slice(0,5);
            localStorage.setItem('taskHistory', JSON.stringify(history));
            updateHistory();
        </script>";
    }
} else {
    // Відображення форми
    ?>
    <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Управління завданнями</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px; 
                line-height: 1.5;
            }
            h1 { font-size: 1.5em; margin-bottom: 20px; }
            form { margin-bottom: 20px; }
            select, input[type="text"], input[type="date"] { 
                width: 100%; 
                padding: 8px; 
                margin: 5px 0 15px; 
                box-sizing: border-box; 
            }
            button, input[type="submit"] { 
                background: #4CAF50; 
                color: white; 
                padding: 8px 15px; 
                border: none; 
                cursor: pointer; 
            }
            .search-option { display: none; margin: 10px 0; }
            .history-item { 
                padding: 8px; 
                margin: 5px 0; 
                background: #f0f0f0; 
                cursor: pointer;
            }
        </style>
        <script>
            function showOptions(type) {
                document.querySelectorAll('.search-option').forEach(el => {
                    el.style.display = 'none';
                });
                document.getElementById(type+'_option').style.display = 'block';
            }
            
            function updateHistory() {
                const history = JSON.parse(localStorage.getItem('taskHistory')) || [];
                const container = document.getElementById('history');
                container.innerHTML = '<h3>Історія</h3>';
                
                if (history.length === 0) {
                    container.innerHTML += '<p>Немає історії</p>';
                    return;
                }
                
                history.forEach((item, i) => {
                    const div = document.createElement('div');
                    div.className = 'history-item';
                    div.textContent = item.title;
                    div.onclick = () => {
                        const result = document.createElement('div');
                        result.innerHTML = item.html;
                        result.style.margin = '20px 0';
                        container.appendChild(result);
                    };
                    container.appendChild(div);
                });
            }
            
            document.addEventListener('DOMContentLoaded', updateHistory);
        </script>
    </head>
    <body>
        <h1>Управління завданнями</h1>
        
        <form method="post">
            <div>
                <label>Тип запиту:</label>
                <select name="search_type" onchange="showOptions(this.value)" required>
                    <option value="">-- Виберіть --</option>
                    <option value="project_tasks">Завдання за проектом</option>

                    <option value="manager_projects">Проекти керівника</option>
                    <option value="manager_employees">Співробітники керівника</option>
                </select>
            </div>
            
            <div id="project_tasks_option" class="search-option">
                <input type="text" name="project_name" placeholder="Назва проекту">
                <input type="date" name="date">
            </div>
            
            <div id="manager_projects_option" class="search-option">
                <input type="text" name="manager_name_projects" placeholder="Ім'я керівника">
            </div>
            
            <div id="manager_employees_option" class="search-option">
                <input type="text" name="manager_name_employees" placeholder="Ім'я керівника">
            </div>
            
            <input type="submit" value="Пошук">
        </form>
        
        <div id="history"></div>
    </body>
    </html>
    <?php
}
?>
