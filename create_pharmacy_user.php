<?php
// Quick script to create pharmacy user directly
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hms_g9", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'pharmacy' OR email = 'pharmacist@hms.com'");
    $stmt->execute();
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "Pharmacy user already exists:\n";
        print_r($existing);
        
        // Delete existing user first
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'pharmacy' OR email = 'pharmacist@hms.com'");
        $stmt->execute();
        echo "Deleted existing pharmacy user.\n";
    }
    
    // Create new pharmacy user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $now = date('Y-m-d H:i:s');
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $result = $stmt->execute([
        'pharmacy',
        'pharmacist@hms.com', 
        $hashedPassword,
        'pharmacist',
        'active',
        $now,
        $now
    ]);
    
    if ($result) {
        echo "✓ Pharmacy user created successfully!\n";
        echo "Username: pharmacy\n";
        echo "Email: pharmacist@hms.com\n";
        echo "Password: password123\n";
        echo "Role: pharmacist\n";
        
        // Verify creation
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'pharmacy'");
        $stmt->execute();
        $user = $stmt->fetch();
        echo "\nVerification:\n";
        print_r($user);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
