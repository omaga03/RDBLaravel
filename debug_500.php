<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/rdbproject/1', 'GET')
);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo substr(strip_tags($response->getContent()), 0, 2000);
}
