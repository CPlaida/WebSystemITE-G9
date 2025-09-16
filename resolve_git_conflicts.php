<?php
/**
 * Git Conflict Resolution Script for WebSystemITE-G9
 * This script helps resolve merge conflicts safely
 */

echo "=== Git Conflict Resolution for WebSystemITE-G9 ===\n\n";

// Step 1: Backup untracked files that would be overwritten
echo "Step 1: Backing up untracked files...\n";
$untrackedFiles = [
    'app/Database/Migrations/2025-09-16-000001_CreateFinancialTables.php',
    'app/Database/Seeds/FinancialDataSeeder.php'
];

$backupDir = 'git_backup_' . date('Y-m-d_H-i-s');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

foreach ($untrackedFiles as $file) {
    if (file_exists($file)) {
        $backupPath = $backupDir . '/' . basename($file);
        copy($file, $backupPath);
        echo "✓ Backed up: $file -> $backupPath\n";
    }
}

// Step 2: Clean up session files and logs (these can be safely removed)
echo "\nStep 2: Cleaning up temporary files...\n";
$tempFiles = glob('writable/sessions/ci_session*');
$logFiles = glob('writable/logs/log-2025-09-15.log');

foreach (array_merge($tempFiles, $logFiles) as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✓ Removed: $file\n";
    }
}

echo "\n=== Manual Steps Required ===\n";
echo "1. Run: git add .\n";
echo "2. Run: git commit -m \"Backup local changes before merge\"\n";
echo "3. Run: git pull\n";
echo "4. If conflicts occur, resolve them manually\n";
echo "5. Restore backed up files from: $backupDir/\n\n";

echo "Backup directory created: $backupDir\n";
echo "You can now safely proceed with git operations.\n";
?>
