<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$yearId = 18; // 2568

echo "--- Project Counts by Type and Department (Year 2568) ---\n";

$projects = \App\Models\RdbProject::where('year_id', $yearId)
    ->where('data_show', 1)
    ->select('department_id', 'pt_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('department_id', 'pt_id')
    ->orderBy('department_id')
    ->get();

foreach ($projects as $p) {
    $dep = \App\Models\RdbDepartment::find($p->department_id);
    $depName = $dep ? $dep->department_nameTH : "Unknown";
    
    // Try to find type name, even if ID is weird
    $type = \App\Models\RdbProjectType::find($p->pt_id);
    $typeName = $type ? $type->pt_name : "Unknown Type";

    echo "Dep: $depName (ID: {$p->department_id}) | Type: $typeName (ID: {$p->pt_id}) | Count: {$p->total}\n";
}
