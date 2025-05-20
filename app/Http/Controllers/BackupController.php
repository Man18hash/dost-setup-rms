<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function download(): StreamedResponse
    {
        // Run only the DB backup
        Artisan::call('backup:run', [
            '--only-db'              => true,
            '--disable-notifications'=> true,
        ]);

        // Find the latest zip
        $files = Storage::disk('local')->files('laravel-backup');
        $latest = collect($files)
            ->filter(fn($f) => str_ends_with($f, '.zip'))
            ->sortByDesc(fn($f) => Storage::disk('local')->lastModified($f))
            ->first();

        abort_unless($latest, 404, 'No backup found.');

        // Stream it back to the browser
        return Storage::disk('local')->download($latest, basename($latest));
    }
}
