<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Індивідуальне домашнє завдання №1</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form action="request1.php" method="get">
        <h3>Інформація про виконані завдання за обраним проєктом на зазначену дату</h3>
        <div>
            <label for="project_id">ID проєкту:</label>
            <input type="text" name="project_id" id="project_id" placeholder="ID_Project">
        </div>
        <div>
            <label for="work_date">Дата:</label>
            <input type="date" name="work_date" id="work_date">
        </div>
        <input type="submit" value="Пошук">
    </form>
    
    <form action="request2.php" method="get">
        <h3>Загальний час роботи над обраним проєктом</h3>
        <div>
            <label for="project_id2">ID проєкту:</label>
            <input type="text" name="project_id" id="project_id2" placeholder="ID_Project">
        </div>
        <input type="submit" value="Пошук">
    </form>
    
    <form action="request3.php" method="get">
        <h3>Кількість співробітників відділу обраного керівника</h3>
        <div>
            <label for="chief_name">Ім'я керівника:</label>
            <input type="text" name="chief" id="chief_name" placeholder="Jobs">
        </div>
        <input type="submit" value="Пошук">
    </form>
</body>
</html>