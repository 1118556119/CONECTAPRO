<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Procesa y guarda una foto de perfil
     */
    public static function processProfilePhoto(UploadedFile $file, $userId = null)
    {
        // Generar nombre único para el archivo
        $extension = $file->getClientOriginalExtension();
        $filename = 'profile_' . ($userId ?? uniqid()) . '_' . time() . '.' . $extension;
        
        // Validar que sea una imagen
        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw new \Exception('Formato de imagen no válido');
        }
        
        // Guardar en storage
        $path = $file->storeAs('profile-photos', $filename, 'public');
        
        return $path;
    }
    
    /**
     * Procesa y redimensiona una foto de perfil (versión avanzada)
     */
    public static function processAndResizeProfilePhoto(UploadedFile $file, $userId = null)
    {
        // Generar nombre único
        $filename = 'profile_' . ($userId ?? uniqid()) . '_' . time() . '.jpg';
        
        // Crear imagen desde el archivo
        $imageInfo = getimagesize($file->getRealPath());
        if (!$imageInfo) {
            throw new \Exception('El archivo no es una imagen válida');
        }
        
        // Crear imagen según el tipo
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($file->getRealPath());
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($file->getRealPath());
                break;
            default:
                throw new \Exception('Tipo de imagen no soportado');
        }
        
        // Redimensionar a 300x300
        $newWidth = 300;
        $newHeight = 300;
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Mantener transparencia para PNG
        if ($imageInfo['mime'] == 'image/png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }
        
        imagecopyresampled(
            $resizedImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $imageInfo[0], $imageInfo[1]
        );
        
        // Guardar como JPG
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        imagejpeg($resizedImage, $tempPath, 80);
        
        // Limpiar memoria
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        
        // Guardar en storage
        $path = 'profile-photos/' . $filename;
        Storage::disk('public')->put($path, file_get_contents($tempPath));
        
        // Eliminar archivo temporal
        unlink($tempPath);
        
        return $path;
    }
    
    /**
     * Elimina una foto del storage
     */
    public static function deletePhoto($photoPath)
    {
        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene la URL completa de una foto
     */
    public static function getPhotoUrl($photoPath)
    {
        if ($photoPath) {
            return asset('storage/' . $photoPath);
        }
        return 'https://via.placeholder.com/300x300/cccccc/ffffff?text=Sin+Foto';
    }
}