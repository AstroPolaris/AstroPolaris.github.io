<?php
// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Leave blank if using XAMPP default
$database = "survey_db";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch survey responses with question text
$query = "
    SELECT q.question_text, r.responseValue, COUNT(*) as count
    FROM survey_responses r
    JOIN survey_questions q ON r.question_id = q.question_id
    GROUP BY q.question_text, r.responseValue
    ORDER BY q.question_text;
";
$result = $conn->query($query);

// Process data for visualization
$data = [];
while ($row = $result->fetch_assoc()) {
    $question = $row['question_text'];
    $response = $row['responseValue'];
    $count = $row['count'];

    if (!isset($data[$question])) {
        $data[$question] = [];
    }
    $data[$question][$response] = $count;
}

// Close DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Survey Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        select {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background: #007BFF;
            color: white;
        }
        canvas {
            margin-top: 20px;
            width: 100% !important;
            height: 500px !important;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Survey Summary</h2>

    <!-- Selection Dropdown -->
    <label for="viewSelect"><b>Choose View:</b></label>
    <select id="viewSelect" onchange="toggleView()">
        <option value="table">Table View</option>
        <option value="chart">Chart View</option>
    </select>

    <!-- Table View -->
    <div id="tableView">
        <table>
            <tr>
                <th>Question</th>
                <th>Response</th>
                <th>Count</th>
            </tr>
            <?php foreach ($data as $question => $responses): ?>
                <?php foreach ($responses as $response => $count): ?>
                    <tr>
                        <td><?= htmlspecialchars($question) ?></td>
                        <td><?= htmlspecialchars($response) ?></td>
                        <td><?= $count ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Chart View -->
    <div id="chartView" class="hidden">
        <canvas id="surveyChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const surveyData = <?= json_encode($data) ?>;
        const questions = Object.keys(surveyData);
        const datasets = [];

        const responseLabels = new Set();
        questions.forEach(question => {
            Object.keys(surveyData[question]).forEach(response => responseLabels.add(response));
        });

        const colors = ["red", "blue", "green", "orange", "purple", "cyan"]; // Color set
        let colorIndex = 0;

        responseLabels.forEach(response => {
            datasets.push({
                label: response,
                data: questions.map(q => surveyData[q][response] || 0),
                backgroundColor: colors[colorIndex % colors.length]
            });
            colorIndex++;
        });

        const ctx = document.getElementById('surveyChart').getContext('2d');
        const surveyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: questions,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Function to toggle between table and chart view
        window.toggleView = function() {
            const viewSelect = document.getElementById("viewSelect").value;
            const tableView = document.getElementById("tableView");
            const chartView = document.getElementById("chartView");

            if (viewSelect === "table") {
                tableView.classList.remove("hidden");
                chartView.classList.add("hidden");
            } else {
                tableView.classList.add("hidden");
                chartView.classList.remove("hidden");
            }
        };
    });
</script>

</body>
</html>
