<?php
session_start(); // Start the session for sessionID tracking

// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Leave blank if using XAMPP default
$database = "survey_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🟢 Fetch all question IDs and their corresponding text
$question_map = [];
$result = $conn->query("SELECT question_id, LOWER(TRIM(question_text)) AS question_text FROM survey_questions");
while ($row = $result->fetch_assoc()) {
    $question_map[$row['question_text']] = $row['question_id']; // Store question ID using lowercase text as key
}
$result->free(); // Free the result set

// 🟢 Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $survey_id = 1; // Fixed survey ID for now
    $sessionID = session_id(); // Unique session ID for tracking responses

    // 🔵 Mapping form fields to actual question texts
    $question_text_map = [
        "q1" => "Did you participate in an Introductory Academic Program (IAP)?",
        "q2" => "Please explain why not:",
        "q3a" => "My course/curriculum is current",
        "q3b" => "My course/curriculum is well designed",
        "q3c" => "My course/curriculum is well aligned with the learning and developmental outcomes I am seeking",
        "q3d" => "My course/curriculum has a good balance of theoretical and practical components",
        "q4" => "If you did not agree with any of the above statements, please provide details:"
    ];

    foreach ($question_text_map as $field_name => $question_text) {
        // 🔵 Check if the response exists
        if (!isset($_POST[$field_name])) {
            continue; // Skip if no response was given
        }

        $responseValue = trim($_POST[$field_name]); // Remove whitespace
        $question_text_lower = strtolower(trim($question_text)); // Normalize for lookup

        // 🔵 Ensure the question ID exists
        if (!isset($question_map[$question_text_lower])) {
            error_log("Error: No matching question ID for ($field_name - $question_text). Skipping...");
            continue;
        }

        $question_id = $question_map[$question_text_lower];

        // 🔵 Insert response into database
        $stmt = $conn->prepare("INSERT INTO survey_responses (survey_id, question_id, responseValue, sessionID) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("SQL Prepare Error: " . $conn->error);
            continue;
        }

        $stmt->bind_param("iiss", $survey_id, $question_id, $responseValue, $sessionID);

        if (!$stmt->execute()) {
            error_log("Error inserting response for question $question_id: " . $stmt->error);
        }

        $stmt->close();
    }

    echo "Survey submitted successfully!";
}

// Close connection
$conn->close();
?>