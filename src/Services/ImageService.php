<?php

namespace admin\admin_auth\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Upload an image to the given path and return its stored path.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @return string
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', string $disk = 'public'): string
    {
        $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $imageName, $disk);
    }

    /**
     * Update an image by deleting the old one and uploading the new one.
     *
     * @param UploadedFile $file
     * @param string|null $oldImagePath
     * @param string $folder
     * @param string $disk
     * @return string
     */
    public function update(UploadedFile $file, ?string $oldImagePath, string $folder = 'uploads', string $disk = 'public'): string
    {
        if ($oldImagePath && Storage::disk($disk)->exists($oldImagePath)) {
            Storage::disk($disk)->delete($oldImagePath);
        }
        return $this->upload($file, $folder, $disk);
    }
}
