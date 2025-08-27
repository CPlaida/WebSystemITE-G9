<?php
// Fix pharmacy user role in database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hms_g9", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, check current table structure
    $stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
    $roleColumn = $stmt->fetch();
    echo "Current role column definition:\n";
    print_r($roleColumn);
    
    // Add 'pharmacist' to the enum if it's not there
    echo "\nAdding 'pharmacist' to role enum...\n";
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','doctor','nurse','receptionist','accounting','itstaff','labstaff','pharmacist') NOT NULL DEFAULT 'receptionist'");
    echo "✓ Role enum updated successfully!\n";
    
    // Update pharmacy user with correct role
    echo "\nUpdating pharmacy user role...\n";
    $stmt = $pdo->prepare("UPDATE users SET role = 'pharmacist' WHERE username = 'pharmacy'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "✓ Pharmacy user role updated successfully!\n";
        
        // Verify the update
        $stmt = $pdo->prepare("SELECT id, username, email, role, status FROM users WHERE username = 'pharmacy'");
        $stmt->execute();
        $user = $stmt->fetch();
        echo "\nVerification:\n";
        print_r($user);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
