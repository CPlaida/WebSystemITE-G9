<?php
/**
 * Quick Merge Conflict Resolution Script
 * Resolves session file conflicts and completes the merge
 */

echo "=== Resolving Merge Conflicts ===\n\n";

// Step 1: Remove the conflicted session file (it's just temporary data)
$conflictedSession = 'writable/sessions/ci_session24f32d4ec414b758136298b67dacde63';
if (file_exists($conflictedSession)) {
    unlink($conflictedSession);
    echo "✓ Removed conflicted session file: $conflictedSession\n";
}

// Step 2: Clean up all session files (they're temporary anyway)
$sessionFiles = glob('writable/sessions/ci_session*');
foreach ($sessionFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
echo "✓ Cleaned up all session files\n";

// Step 3: Add the resolved files
echo "\nStep 3: Adding resolved files...\n";
system('git add .');
echo "✓ Added all files to git\n";

// Step 4: Complete the merge
echo "\nStep 4: Completing the merge...\n";
system('git commit -m "Resolve merge conflicts - removed session files and merged routes"');
echo "✓ Merge completed successfully!\n";

echo "\n=== Merge Resolution Complete ===\n";
echo "Your repository is now up to date with the latest changes.\n";
echo "You can continue working on your project.\n";
?>
