<?php
$manifestPath = 'storage/app/laravel-brain/.graph-manifest.json';
if (!file_exists($manifestPath)) {
    echo "Manifest not found\n";
    exit;
}

$data = json_decode(file_get_contents($manifestPath), true);
$isolated = [];
$security = [];

// Since they might not be at the top level, let's look through the tabs.
if (isset($data['tabs'])) {
    foreach ($data['tabs'] as $tab) {
        if (!empty($tab['issueCount'])) {
            echo "Tab: " . $tab['id'] . " has issues.\n";
        }
    }
}

// Check other files in laravel-brain directory for security or isolated keywords.
$dir = new DirectoryIterator('storage/app/laravel-brain');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'json') {
        $content = json_decode(file_get_contents($fileinfo->getPathname()), true);
        if (isset($content['isolatedNodes']) && !empty($content['isolatedNodes'])) {
            $isolated = array_merge($isolated, $content['isolatedNodes']);
        }
        if (isset($content['security']) && !empty($content['security'])) {
            $security = array_merge($security, $content['security']);
        }
        if (isset($content['issues']) && !empty($content['issues'])) {
            foreach ($content['issues'] as $issue) {
                if (isset($issue['type']) && str_contains(strtolower($issue['type']), 'security')) {
                    $security[] = $issue;
                }
            }
        }
    }
}

echo "\n--- Isolated Nodes ---\n";
print_r($isolated);

echo "\n--- Security Issues ---\n";
print_r($security);
