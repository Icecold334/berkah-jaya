<?php

use App\Models\TransaksiKas;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;

Route::get('/debug', function () {
    $logDir = storage_path('logs');

    // Ambil 1 file .log paling baru
    $latestLog = collect(File::files($logDir))
        ->filter(fn($f) => str_ends_with($f->getFilename(), '.log'))
        ->sortByDesc(fn($f) => $f->getMTime())
        ->first();

    if (!$latestLog) {
        return response('No log file found.', 404);
    }

    // Baca isi file, ambil 3 baris terakhir, lalu urutkan terbaru di atas
    $lines = file($latestLog->getRealPath(), FILE_IGNORE_NEW_LINES);
    if (!$lines) {
        return response('Log file is empty.', 200);
    }

    $last3 = array_reverse(array_slice($lines, -100));

    // Render seperti log (dark theme + highlight)
    $html = '<style>
        body { background:#1e1e1e;color:#ccc;font-family:monospace;margin:0;padding:12px }
        .file { color:#9CDCFE;margin-bottom:8px }
        .timestamp { color:#6A9955 }
        .level-error { color:#F44747;font-weight:bold }
        .level-warning { color:#CCA700;font-weight:bold }
        .level-info { color:#569CD6;font-weight:bold }
        pre { white-space:pre-wrap;word-wrap:break-word;margin:0 }
    </style>';

    $html .= '<div class="file">FILE: ' . e($latestLog->getFilename()) . '</div><pre>';

    foreach ($last3 as $line) {
        $lineHtml = e($line);
        // highlight pola umum Laravel: [YYYY-MM-DD HH:MM:SS]
        $lineHtml = preg_replace('/\[(\d{4}-\d{2}-\d{2}.*?)\]/', '<span class="timestamp">[$1]</span>', $lineHtml);
        // highlight level
        $lineHtml = preg_replace('/\bERROR\b/i', '<span class="level-error">ERROR</span>', $lineHtml);
        $lineHtml = preg_replace('/\bWARNING\b/i', '<span class="level-warning">WARNING</span>', $lineHtml);
        $lineHtml = preg_replace('/\bINFO\b/i', '<span class="level-info">INFO</span>', $lineHtml);

        $html .= $lineHtml . "\n";
    }

    $html .= '</pre>';

    // return $last3;

    return response($html);
});


Route::get('/', function () {
    $saldo = TransaksiKas::selectRaw("
    SUM(CASE WHEN tipe = 'masuk' THEN jumlah ELSE -jumlah END) as saldo
")->value('saldo');
    // return view('test', compact('saldo'));
    return redirect()->route('dashboard');
})->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});


Route::post('/github-webhook', function (\Illuminate\Http\Request $request) {
    $secret = 'berkahjaya'; // samakan dengan secret di GitHub

    // Validasi signature
    $signature = $request->header('X-Hub-Signature-256');
    $hash = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

    if (!hash_equals($hash, $signature)) {
        Log::warning('Invalid webhook signature');
        abort(403, 'Invalid signature');
    }

    // Jalankan perintah deploy
    $commands = [
        'cd /home/devnfa/public_html/berkah',
        'git reset --hard',
        'git clean -fd',
        'git pull origin main',
        'composer install --no-dev --optimize-autoloader',
        'npm install',
        'npm run build',
        // 'php artisan migrate --force',
        'php artisan config:clear',
        'php artisan cache:clear',
        'php artisan route:clear',
        'php artisan view:clear',
    ];

    $process = new Process(implode(' && ', $commands));
    $process->setTimeout(300); // 5 menit
    $process->run();

    if (!$process->isSuccessful()) {
        Log::error('Deploy failed: ' . $process->getErrorOutput());
        return response('Deploy failed', 500);
    }

    Log::info('Deploy success: ' . $process->getOutput());
    return response('Deployed', 200);
});

require __DIR__ . '/auth.php';
require __DIR__ . '/app.php';
