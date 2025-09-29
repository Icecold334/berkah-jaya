<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;

Route::post('/github-webhook', function (Request $request) {
  $secret = 'mysecret'; // sama dengan secret di GitHub webhook

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
    'php artisan migrate --force',
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan route:clear',
    'php artisan view:clear',
  ];

  $process = Process::fromShellCommandline(implode(' && ', $commands));
  $process->setTimeout(300);
  $process->run();

  if (!$process->isSuccessful()) {
    Log::error('Deploy failed: ' . $process->getErrorOutput());
    return response('Deploy failed', 500);
  }

  Log::info('Deploy success: ' . $process->getOutput());
  return response('Deployed', 200);
});
