<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Індивідуальне завдання №2</title>
    <link rel="stylesheet" href="styles.css">
    <script>
       async function logRequest(url, browserInfo, coordinates) {
        try {
            await fetch('log_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `request_url=${encodeURIComponent(url)}&browser_info=${encodeURIComponent(browserInfo)}&user_coordinates=${encodeURIComponent(coordinates)}`
            });
        } catch (error) {
            console.error('Error logging request:', error);
        }
    }

    function getBrowserInfo() {
        return navigator.userAgent + ' | ' + navigator.platform;
    }

    function sendRequest1(event) {
        event.preventDefault();
        const projectId = document.getElementById('project_id').value;
        const workDate = document.getElementById('work_date').value;
        
        if (!projectId || !workDate) {
            alert('Будь ласка, заповніть всі поля');
            return;
        }
        
        const url = `request1.php?project_id=${encodeURIComponent(projectId)}&work_date=${encodeURIComponent(workDate)}`;
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = `${position.coords.latitude},${position.coords.longitude}`;
                    const browserInfo = getBrowserInfo();
                    logRequest(url, browserInfo, coords);

                    executeRequest1(url);
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    const browserInfo = getBrowserInfo();
                    logRequest(url, browserInfo, 'unavailable');
                    
                    executeRequest1(url);
                }
            );
        } else {
            const browserInfo = getBrowserInfo();
            logRequest(url, browserInfo, 'unsupported');
            
            executeRequest1(url);
        }
    }
    
    function executeRequest1(url) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('result1').innerHTML = this.responseText;
                document.getElementById('result-box1').style.display = 'block';
            } else {
                document.getElementById('result1').innerHTML = 'Помилка при виконанні запиту';
                document.getElementById('result-box1').style.display = 'block';
            }
        };
        
        xhr.send();
    }
    
    function sendRequest2(event) {
        event.preventDefault();
        const projectId = document.getElementById('project_id2').value;
        
        if (!projectId) {
            alert('Будь ласка, введіть ID проєкту');
            return;
        }
        
        const url = `request2.php?project_id=${encodeURIComponent(projectId)}`;
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = `${position.coords.latitude},${position.coords.longitude}`;
                    const browserInfo = getBrowserInfo();
                    logRequest(url, browserInfo, coords);
                    executeRequest2(url);
                },
                (error) => {
                    const browserInfo = getBrowserInfo();
                    logRequest(url, browserInfo, 'unavailable');
                    executeRequest2(url);
                }
            );
        } else {
            const browserInfo = getBrowserInfo();
            logRequest(url, browserInfo, 'unsupported');
            executeRequest2(url);
        }
    }
    
    function executeRequest2(url) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        
        xhr.onload = function() {
            if (this.status === 200) {
                const xmlDoc = this.responseXML;
                const projects = xmlDoc.getElementsByTagName("project");
                
                let html = '<table><thead><tr><th>Project Name</th><th>Time Start</th><th>Time End</th><th>Total Days</th></tr></thead><tbody>';
                
                if (projects.length > 0) {
                    for (let i = 0; i < projects.length; i++) {
                        const project = projects[i];
                        html += `<tr>
                            <td>${project.getElementsByTagName("name")[0].textContent}</td>
                            <td>${project.getElementsByTagName("time_start")[0].textContent}</td>
                            <td>${project.getElementsByTagName("time_end")[0].textContent}</td>
                            <td>${project.getElementsByTagName("total_days")[0].textContent}</td>
                        </tr>`;
                    }
                } else {
                    html += '<tr><td colspan="4">No results found</td></tr>';
                }
                
                html += '</tbody></table>';
                document.getElementById('result2').innerHTML = html;
                document.getElementById('result-box2').style.display = 'block';
            } else {
                document.getElementById('result2').innerHTML = 'Помилка при виконанні запиту';
                document.getElementById('result-box2').style.display = 'block';
            }
        };
        
        xhr.send();
    }
    
    async function sendRequest3(event) {
        event.preventDefault();
        const chiefName = document.getElementById('chief_name').value;
        
        if (!chiefName) {
            alert('Будь ласка, введіть ім\'я керівника');
            return;
        }
        
        const url = `request3.php?chief=${encodeURIComponent(chiefName)}`;
        
        try {
            let coords = 'unsupported';
            if (navigator.geolocation) {
                try {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject);
                    });
                    coords = `${position.coords.latitude},${position.coords.longitude}`;
                } catch (error) {
                    coords = 'unavailable';
                }
            }
            
            const browserInfo = getBrowserInfo();
            await logRequest(url, browserInfo, coords);
            
            const response = await fetch(url);
            const data = await response.json();
            
            let html = '<table><thead><tr><th>Department Chief</th><th>Number of Workers</th></tr></thead><tbody>';
            
            if (data.length > 0) {
                data.forEach(item => {
                    html += `<tr>
                        <td>${item.department_chief}</td>
                        <td>${item.workers_count}</td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="2">No results found</td></tr>';
            }
            
            html += '</tbody></table>';
            document.getElementById('result3').innerHTML = html;
            document.getElementById('result-box3').style.display = 'block';
        } catch (error) {
            document.getElementById('result3').innerHTML = 'Помилка при виконанні запиту';
            document.getElementById('result-box3').style.display = 'block';
        }
    }
    </script>
</head>
<body>
    <div class="forms-row">
        <div class="form-container">
            <form onsubmit="sendRequest1(event)">
                <h3>Інформація про виконані завдання за обраним проєктом на зазначену дату</h3>
                <div class="input-group">
                    <input type="text" name="project_id" id="project_id" placeholder="Project">
                </div>
                <div class="input-group">
                    <input type="date" name="work_date" id="work_date" placeholder="дд.мм.рррр">
                </div>
                <input type="submit" value="Пошук">
            </form>
            <div id="result-box1" class="result-box">
                <div id="result1"></div>
            </div>
        </div>
        
        <div class="form-container">
            <form onsubmit="sendRequest2(event)">
                <h3>Загальний час роботи над обраним проєктом</h3>
                <div class="input-group">
                    <input type="text" name="project_id" id="project_id2" placeholder="Project">
                </div>
                <input type="submit" value="Пошук">
            </form>
            <div id="result-box2" class="result-box">
                <div id="result2"></div>
            </div>
        </div>
        
        <div class="form-container">
            <form onsubmit="sendRequest3(event)">
                <h3>Кількість співробітників відділу обраного керівника</h3>
                <div class="input-group">
                    <input type="text" name="chief" id="chief_name" placeholder="Jobs">
                </div>
                <input type="submit" value="Пошук">
            </form>
            <div id="result-box3" class="result-box">
                <div id="result3"></div>
            </div>
        </div>
    </div>
</body>
</html>