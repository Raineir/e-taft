<?php
// Other existing functions...

/**
 * Logs user activity to the system_logs table.
 *
 * @param int $userId The ID of the user performing the action.
 * @param string $action Description of the action.
 * @param string|null $details Optional additional details.
 */
function logActivity($userId, $action, $details = null) {
    global $conn;

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ipAddress]);
}

/**
 * Fetches all students from the database.
 *
 * @return array List of students.
 */
function getAllStudents() {
    global $conn;
    $stmt = $conn->query("SELECT * FROM students ORDER BY last_name, first_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
