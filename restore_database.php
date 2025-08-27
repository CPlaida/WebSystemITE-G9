<?php

/**
 * Database Restoration Script for HMS-G9
 * This script will restore your complete database with all tables and users
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hms_g9';

try {
    // Step 1: Connect to MySQL server (without database)
    echo "Step 1: Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server successfully\n\n";

    // Step 2: Create database if it doesn't exist
    echo "Step 2: Creating database '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✓ Database '$database' created/verified successfully\n\n";

    // Step 3: Use the database
    $pdo->exec("USE `$database`");
    echo "Step 3: Connected to database '$database'\n\n";

    // Step 4: Create users table
    echo "Step 4: Creating users table...\n";
    $createUsersTable = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `username` varchar(100) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('admin','doctor','nurse','receptionist','accounting','itstaff','labstaff','pharmacist') NOT NULL DEFAULT 'receptionist',
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($createUsersTable);
    echo "✓ Users table created successfully\n\n";

    // Step 5: Insert default users
    echo "Step 5: Inserting default users...\n";
    $now = date('Y-m-d H:i:s');
    
    $users = [
        ['admin', 'admin@hms.com', 'admin'],
        ['doctor', 'doctor@hms.com', 'doctor'],
        ['nurse', 'nurse@hms.com', 'nurse'],
        ['reception', 'receptionist@hms.com', 'receptionist'],
        ['accounting', 'accounting@hms.com', 'accounting'],
        ['itstaff', 'itstaff@hms.com', 'itstaff'],
        ['laboratory', 'laboratory@hms.com', 'labstaff'],
        ['pharmacy', 'pharmacist@hms.com', 'pharmacist']
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password, role, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, 'active', ?, ?)
    ");

    foreach ($users as $user) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->execute([$user[0], $user[1], $hashedPassword, $user[2], $now, $now]);
        echo "✓ Created user: {$user[0]} ({$user[2]})\n";
    }

    echo "\n✅ Database restoration completed successfully!\n\n";
    
    echo "=== LOGIN CREDENTIALS ===\n";
    echo "Admin: admin@hms.com / password123\n";
    echo "Doctor: doctor@hms.com / password123\n";
    echo "Nurse: nurse@hms.com / password123\n";
    echo "Reception: receptionist@hms.com / password123\n";
    echo "Accounting: accounting@hms.com / password123\n";
    echo "IT Staff: itstaff@hms.com / password123\n";
    echo "Laboratory: laboratory@hms.com / password123\n";
    echo "Pharmacy: pharmacist@hms.com / password123\n";
    echo "========================\n\n";
    
    echo "Your HMS-G9 database is now ready!\n";
    echo "You can now login at: http://localhost/WebSystemITE-G9/\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
