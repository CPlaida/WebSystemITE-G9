<?php
// Debug session script
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Logged in: " . (isset($_SESSION['logged_in']) ? ($_SESSION['logged_in'] ? 'Yes' : 'No') : 'Not set') . "\n";
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n";
echo "Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'Not set') . "\n";
echo "Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . "\n";
echo "\nAll session data:\n";
print_r($_SESSION);
echo "</pre>";

// Test database connection and check pharmacy user
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hms_g9", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Check</h2>";
    echo "<pre>";
    
    // Check if pharmacy user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'pharmacy' OR email = 'pharmacist@hms.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Pharmacy user found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
    } else {
        echo "Pharmacy user NOT found in database!\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}
?>
