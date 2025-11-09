<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ObjectController extends Controller
{
    /**
     * Display a listing of objects from R2 bucket.
     */
    public function index(): Response
    {
        $disk = Storage::disk('s3');
        $files = $disk->allFiles();

        $objects = collect($files)->map(function ($file) use ($disk) {
            $url = '';
            try {
                $url = $disk->temporaryUrl($file, now()->addHour());
            } catch (\Exception $e) {
                // If temporaryUrl is not supported, try regular url
                $url = config('app.url').'/storage/'.$file;
            }

            return [
                'path' => $file,
                'name' => basename($file),
                'size' => $disk->size($file),
                'last_modified' => $disk->lastModified($file),
                'url' => $url,
            ];
        })->sortByDesc('last_modified')->values();

        return Inertia::render('admin/objects/Index', [
            'objects' => $objects,
        ]);
    }

    /**
     * Delete the specified object from R2 bucket.
     */
    public function destroy(string $path): RedirectResponse
    {
        $disk = Storage::disk('s3');

        if ($disk->exists($path)) {
            $disk->delete($path);

            return redirect()->route('admin.objects.index')
                ->with('success', 'Object deleted successfully.');
        }

        return redirect()->route('admin.objects.index')
            ->with('error', 'Object not found.');
    }
}
