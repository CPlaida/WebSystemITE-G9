<?php
// Simple debug script to check appointments
$host = 'localhost';
$dbname = 'hms_g9'; // Update this to your actual database name
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== DEBUGGING APPOINTMENTS ===\n\n";

    // Check appointments count
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments");
    $count = $stmt->fetchColumn();
    echo "Total appointments in database: $count\n\n";
    
    // Get recent appointments
    $stmt = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent appointments:\n";
    foreach ($appointments as $apt) {
        echo "ID: {$apt['id']}, Appointment ID: {$apt['appointment_id']}, Patient ID: {$apt['patient_id']}, Doctor ID: {$apt['doctor_id']}, Date: {$apt['appointment_date']}, Time: {$apt['appointment_time']}, Status: {$apt['status']}\n";
    }
    
    echo "\n=== TESTING JOIN QUERY ===\n";
    
    // Test the join query used in getAppointmentsWithDetails
    $sql = "SELECT a.*, 
                   p.first_name as patient_first_name, 
                   p.last_name as patient_last_name, 
                   p.phone as patient_phone, 
                   u.username as doctor_name, 
                   u.email as doctor_email
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.id
            LEFT JOIN users u ON a.doctor_id = u.id AND u.role = 'doctor'
            ORDER BY a.appointment_date DESC, a.appointment_time ASC";
    
    $stmt = $pdo->query($sql);
    $joinResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Join query results count: " . count($joinResults) . "\n";
    foreach ($joinResults as $result) {
        echo "Appointment: {$result['appointment_id']}, Patient: {$result['patient_first_name']} {$result['patient_last_name']}, Doctor: {$result['doctor_name']}, Date: {$result['appointment_date']}\n";
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
