<?php
// Direct database test for appointment insertion
$host = 'localhost';
$dbname = 'hms_g9';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== TESTING DIRECT APPOINTMENT INSERTION ===\n\n";

    // First, create a test patient
    $patientSql = "INSERT INTO patients (patient_id, first_name, last_name, phone, email, date_of_birth, gender, address, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $patientData = [
        'PAT' . date('Ymd') . '0001',
        'Test',
        'Patient',
        '1234567890',
        'test@example.com',
        '1990-01-01',
        'other',
        'Test Address',
        'active'
    ];
    
    $stmt = $pdo->prepare($patientSql);
    $patientResult = $stmt->execute($patientData);
    $patientId = $pdo->lastInsertId();
    
    echo "Patient creation: " . ($patientResult ? "SUCCESS - ID: $patientId" : "FAILED") . "\n";
    
    // Get a doctor ID
    $doctorStmt = $pdo->query("SELECT id FROM users WHERE role = 'doctor' LIMIT 1");
    $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        echo "No doctors found in database\n";
        exit;
    }
    
    $doctorId = $doctor['id'];
    echo "Using doctor ID: $doctorId\n";
    
    // Now try to insert appointment
    $appointmentSql = "INSERT INTO appointments (appointment_id, patient_id, doctor_id, appointment_date, appointment_time, appointment_type, reason, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $appointmentData = [
        'APT' . date('Ymd') . '0001',
        $patientId,
        $doctorId,
        date('Y-m-d', strtotime('+1 day')),
        '09:00:00',
        'consultation',
        'Test appointment',
        'scheduled'
    ];
    
    $stmt = $pdo->prepare($appointmentSql);
    $appointmentResult = $stmt->execute($appointmentData);
    $appointmentId = $pdo->lastInsertId();
    
    echo "Appointment creation: " . ($appointmentResult ? "SUCCESS - ID: $appointmentId" : "FAILED") . "\n";
    
    if ($appointmentResult) {
        // Verify the appointment was saved
        $verifyStmt = $pdo->query("SELECT COUNT(*) FROM appointments");
        $count = $verifyStmt->fetchColumn();
        echo "Total appointments in database after insert: $count\n";
        
        // Test the join query
        $joinSql = "SELECT a.*, 
                           p.first_name as patient_first_name, 
                           p.last_name as patient_last_name, 
                           u.username as doctor_name
                    FROM appointments a
                    LEFT JOIN patients p ON a.patient_id = p.id
                    LEFT JOIN users u ON a.doctor_id = u.id AND u.role = 'doctor'
                    WHERE a.id = ?";
        
        $joinStmt = $pdo->prepare($joinSql);
        $joinStmt->execute([$appointmentId]);
        $joinResult = $joinStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Join query result:\n";
        print_r($joinResult);
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
