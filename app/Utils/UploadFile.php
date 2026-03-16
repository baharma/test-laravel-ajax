<?php

namespace App\Utils;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadFile
{
    public static function uploadLocal(UploadedFile $file, $folder = "images")
    {
        try {
            $destinationPath = public_path($folder);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true); // Buat folder jika tidak ada
            }
            $extension = $file->getClientOriginalExtension();
            $filenameSave = time() . '.' . $extension;
            $file->move($destinationPath, $filenameSave);
            chmod($destinationPath . '/' . $filenameSave, 0777);

            return "$folder/$filenameSave"; // Path relatif dari public
        } catch (Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return null;
        }
    }
}
