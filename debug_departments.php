<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$yearId = 18; // 2568

echo "--- Total Projects in Year 2568 ---\n";
$total = \App\Models\RdbProject::where('year_id', $yearId)->where('data_show', 1)->count();
echo "Total: $total\n\n";

echo "--- Projects Grouped by Department ---\n";
$projectsByDep = \App\Models\RdbProject::where('year_id', $yearId)
    ->where('data_show', 1)
    ->select('department_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('department_id')
    ->get();

foreach ($projectsByDep as $p) {
    $dep = \App\Models\RdbDepartment::find($p->department_id);
    $depName = $dep ? $dep->department_nameTH : "Unknown (ID: {$p->department_id})";
    echo "Department: $depName (ID: {$p->department_id}) - Count: {$p->total}\n";
}

echo "\n--- Departments used in Chart (Type 1) ---\n";
$chartDeps = \App\Models\RdbDepartment::getProDepTea(1);
$chartDepIds = explode(',', $chartDeps);
echo "Chart Filter IDs: " . implode(', ', $chartDepIds) . "\n";

echo "\n--- Mismatch Analysis ---\n";
foreach ($projectsByDep as $p) {
    if (!in_array($p->department_id, $chartDepIds)) {
        $dep = \App\Models\RdbDepartment::find($p->department_id);
        $depName = $dep ? $dep->department_nameTH : "Unknown";
        echo "[MISSING] Department ID {$p->department_id} ($depName) has {$p->total} projects but is excluded from chart.\n";
    }
}
