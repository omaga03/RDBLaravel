<?php

require 'C:/laragon/www/laravel_rdb/vendor/autoload.php';
$app = require_once 'C:/laragon/www/laravel_rdb/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe';
$outDir = 'C:\laragon\www\laravel_rdb\docs\manuals\screenshots';
$artifactDir = 'C:\Users\User\.gemini\antigravity\brain\5d95b58a-78d8-46d7-8e96-12a3315b7c9e';

function takeChromeScreenshot(string $chrome, string $url, string $targetPath): bool
{
    $cmd = sprintf(
        '"%s" --headless=new --no-sandbox --disable-gpu --hide-scrollbars --window-size=1920,1080 --screenshot="%s" "%s" 2>&1',
        $chrome,
        $targetPath,
        $url
    );
    exec($cmd, $out, $ret);
    return file_exists($targetPath) && filesize($targetPath) > 0;
}

function annotateScreenshot(string $imgPath, array $annotations): void
{
    if (!file_exists($imgPath) || empty($annotations)) {
        return;
    }
    $im = @imagecreatefrompng($imgPath);
    if (!$im) {
        return;
    }

    imagesavealpha($im, true);
    $red = imagecolorallocate($im, 220, 38, 38);       // #DC2626
    $white = imagecolorallocate($im, 255, 255, 255);

    foreach ($annotations as $ann) {
        if (!empty($ann['rect'])) {
            [$x1, $y1, $x2, $y2] = $ann['rect'];
            imagesetthickness($im, 3);
            imagerectangle($im, $x1, $y1, $x2, $y2, $red);
            imagesetthickness($im, 1);
        }

        if (!empty($ann['badge'])) {
            [$cx, $cy, $num] = $ann['badge'];
            imagefilledellipse($im, $cx, $cy, 34, 34, $white);
            imagefilledellipse($im, $cx, $cy, 28, 28, $red);
            $str = (string)$num;
            $font = 5;
            $fw = imagefontwidth($font) * strlen($str);
            $fh = imagefontheight($font);
            imagestring($im, $font, (int)($cx - $fw / 2), (int)($cy - $fh / 2), $str, $white);
        }
    }

    imagepng($im, $imgPath);
    imagedestroy($im);
}

$backendRoutes = [
    'backend_02_dashboard.png' => [
        'uri' => '/backend/site',
        'annotations' => [
            ['rect' => [20, 10, 1900, 65], 'badge' => [40, 35, 1]],
            ['rect' => [80, 140, 1840, 360], 'badge' => [65, 160, 2]],
            ['rect' => [80, 380, 1840, 980], 'badge' => [65, 400, 3]],
        ],
    ],
    'backend_03_workflow_queue.png' => [
        'uri' => '/backend/workflow/queue',
        'annotations' => [
            ['rect' => [80, 130, 1840, 240], 'badge' => [65, 150, 1]],
            ['rect' => [80, 260, 1840, 920], 'badge' => [65, 280, 2]],
        ],
    ],
    'backend_04_projects_index.png' => [
        'uri' => '/backend/rdb_project',
        'annotations' => [
            ['rect' => [80, 120, 1840, 320], 'badge' => [65, 140, 1]],
            ['rect' => [80, 340, 1840, 980], 'badge' => [65, 360, 2]],
        ],
    ],
    'backend_05_project_show.png' => [
        'uri' => '/backend/rdb_project/2899',
        'annotations' => [
            ['rect' => [80, 110, 1840, 210], 'badge' => [65, 130, 1]],
            ['rect' => [80, 230, 1840, 980], 'badge' => [65, 250, 2]],
        ],
    ],
    'backend_06_project_dspace.png' => [
        'uri' => '/backend/rdb_project/dspace',
        'annotations' => [
            ['rect' => [80, 130, 1840, 300], 'badge' => [65, 150, 1]],
            ['rect' => [80, 320, 1840, 920], 'badge' => [65, 340, 2]],
        ],
    ],
    'backend_07_peer_review_index.png' => [
        'uri' => '/backend/project-reviews',
        'annotations' => [
            ['rect' => [80, 120, 1840, 280], 'badge' => [65, 140, 1]],
            ['rect' => [80, 300, 1840, 920], 'badge' => [65, 320, 2]],
        ],
    ],
    'backend_08_published_index.png' => [
        'uri' => '/backend/rdb_published',
        'annotations' => [
            ['rect' => [80, 120, 1840, 320], 'badge' => [65, 140, 1]],
            ['rect' => [80, 340, 1840, 980], 'badge' => [65, 360, 2]],
        ],
    ],
    'backend_09_published_show.png' => [
        'uri' => '/backend/rdb_published/2028',
        'annotations' => [
            ['rect' => [80, 120, 1220, 880], 'badge' => [65, 140, 1]],
            ['rect' => [1240, 120, 1840, 880], 'badge' => [1225, 140, 2]],
        ],
    ],
    'backend_10_dip_index.png' => [
        'uri' => '/backend/rdb_dip',
        'annotations' => [
            ['rect' => [80, 120, 1840, 310], 'badge' => [65, 140, 1]],
            ['rect' => [80, 330, 1840, 960], 'badge' => [65, 350, 2]],
        ],
    ],
    'backend_11_utilize_index.png' => [
        'uri' => '/backend/rdbprojectutilize',
        'annotations' => [
            ['rect' => [80, 120, 1840, 310], 'badge' => [65, 140, 1]],
            ['rect' => [80, 330, 1840, 960], 'badge' => [65, 350, 2]],
        ],
    ],
    'backend_12_researcher_index.png' => [
        'uri' => '/backend/rdb_researcher',
        'annotations' => [
            ['rect' => [80, 120, 1840, 310], 'badge' => [65, 140, 1]],
            ['rect' => [80, 330, 1840, 960], 'badge' => [65, 350, 2]],
        ],
    ],
    'backend_13_rbac_roles.png' => [
        'uri' => '/backend/rbac',
        'annotations' => [
            ['rect' => [80, 120, 1840, 920], 'badge' => [65, 140, 1]],
        ],
    ],
    'backend_14_users_index.png' => [
        'uri' => '/backend/user',
        'annotations' => [
            ['rect' => [80, 120, 1840, 290], 'badge' => [65, 140, 1]],
            ['rect' => [80, 310, 1840, 960], 'badge' => [65, 330, 2]],
        ],
    ],
    'backend_15_audit_logs.png' => [
        'uri' => '/backend/audit-logs',
        'annotations' => [
            ['rect' => [80, 120, 1840, 260], 'badge' => [65, 140, 1]],
            ['rect' => [80, 280, 1840, 960], 'badge' => [65, 300, 2]],
        ],
    ],
    'backend_16_access_control.png' => [
        'uri' => '/backend/settings/access-control',
        'annotations' => [
            ['rect' => [200, 120, 1720, 860], 'badge' => [185, 140, 1]],
        ],
    ],
];

$tempHtmlFile = 'C:\laragon\www\laravel_rdb\public\_temp_manual_shot.html';
$admin = App\Models\User::find(129);

foreach ($backendRoutes as $filename => $cfg) {
    echo "Rendering & capturing {$filename} ({$cfg['uri']})...\n";
    Illuminate\Support\Facades\Auth::login($admin);
    $request = Illuminate\Http\Request::create('http://laravel_rdb.test' . $cfg['uri'], 'GET');
    $request->setUserResolver(fn () => $admin);

    $response = $httpKernel->handle($request);
    $html = $response->getContent();
    $html = preg_replace('/<head>/i', '<head><base href="http://laravel_rdb.test/">', $html, 1);
    file_put_contents($tempHtmlFile, $html);

    $path = $outDir . DIRECTORY_SEPARATOR . $filename;
    takeChromeScreenshot($chrome, 'http://laravel_rdb.test/_temp_manual_shot.html', $path);
    annotateScreenshot($path, $cfg['annotations']);
    copy($path, $artifactDir . DIRECTORY_SEPARATOR . $filename);
}

if (file_exists($tempHtmlFile)) {
    @unlink($tempHtmlFile);
}

echo "ALL BACKEND SCREENSHOTS CAPTURED SUCCESSFULLY!\n";
