<?php

namespace App\Http\Controllers;

use App\Utils\UploadFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function UploadFileLocal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $folder = preg_replace('/[^A-Za-z0-9_\/-]/', '', trim((string) ($validated['folder'] ?? 'uploads')));
        $folder = trim($folder, '/') ?: 'uploads';

        $path = UploadFile::uploadLocal($validated['file'], $folder);

        if (! $path) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal upload file ke local storage.',
                'data' => [],
            ], 500);
        }

        return response()->json([
            'status' => 200,
            'message' => 'File uploaded successfully.',
            'data' => [
                'path' => $path,
                'url' => asset($path),
                'name' => basename($path),
            ],
        ]);
    }
}
