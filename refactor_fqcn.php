<?php

$dirs = ['app/', 'tests/'];

function processDir($dir) {
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') continue;
        
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $original = $content;

        // Replace inline UserRole
        $content = str_replace('\App\Enums\UserRole::', 'UserRole::', $content);
        
        // Add use App\Enums\UserRole; if UserRole:: is present but use is missing
        if (strpos($content, 'UserRole::') !== false && strpos($content, 'use App\Enums\UserRole;') === false) {
            $content = preg_replace('/(namespace\s+[A-Za-z0-9_\\\\]+;)/', "$1\n\nuse App\\Enums\\UserRole;", $content);
        }

        // Replace \App\Observers\BillObserver::class
        $content = str_replace('\App\Observers\BillObserver::class', 'BillObserver::class', $content);
        if (strpos($content, 'BillObserver::class') !== false && strpos($content, 'use App\Observers\BillObserver;') === false) {
             $content = str_replace('use Illuminate\Support\ServiceProvider;', "use Illuminate\Support\ServiceProvider;\nuse App\\Observers\\BillObserver;", $content);
        }

        // Group imports: find all use App\Models\XXX; and merge them.
        preg_match_all('/use\s+App\\\\Models\\\\([A-Za-z0-9_]+);/', $content, $matches);
        if (!empty($matches[1]) && count($matches[1]) > 1) {
            $models = array_unique($matches[1]);
            sort($models);
            $grouped = "use App\Models\\{\n    " . implode(",\n    ", $models) . "\n};";
            
            // Remove existing Model imports
            $content = preg_replace('/use\s+App\\\\Models\\\\[A-Za-z0-9_]+;\n/', '', $content);
            
            // Insert grouped Model imports after namespace
            $content = preg_replace('/(namespace\s+[A-Za-z0-9_\\\\]+;)/', "$1\n\n$grouped", $content);
        }

        // Group Rules/Requests if the user likes grouping (skipping for now, strict rule only mentioned Models)

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: $path\n";
        }
    }
}

foreach ($dirs as $dir) {
    processDir(__DIR__ . '/' . $dir);
}
echo "Done.\n";
