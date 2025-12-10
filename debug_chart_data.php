<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Project Types ---\n";
$types = \App\Models\RdbProjectType::all();
foreach ($types as $t) {
    echo "ID: {$t->pt_id}, Name: {$t->pt_name}, For: {$t->pt_for}\n";
}

echo "\n--- Projects in Year 2568 (year_id=18) ---\n";
// Assuming year_id 18 is 2568 based on previous dump
$projects = \App\Models\RdbProject::where('year_id', 18)->limit(20)->get();
echo "Total Projects found: " . \App\Models\RdbProject::where('year_id', 18)->count() . "\n";
foreach ($projects as $p) {
    echo "Project ID: {$p->pro_id}, Type ID: {$p->pt_id}\n";
}
