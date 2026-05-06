<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageMetadataAnalyzer
{
    private const WHATSAPP_MAX_SIZE_BYTES = 300 * 1024;

    private const SCREENSHOT_SOFTWARE_KEYWORDS = ['apple', 'android', 'microsoft', 'screenshot', 'miui', 'samsung', 'oneui'];

    // Common mobile portrait resolutions (width x height)
    private const MOBILE_RESOLUTIONS = [
        [750, 1334], [828, 1792], [1080, 1920], [1080, 2160], [1080, 2220],
        [1080, 2340], [1080, 2400], [1170, 2532], [1179, 2556], [1206, 2622],
        [1242, 2688], [1284, 2778], [1290, 2796], [1440, 2560], [1440, 3040],
        [1440, 3088], [1440, 3200],
    ];

    // Common desktop resolutions
    private const DESKTOP_RESOLUTIONS = [
        [1280, 720], [1366, 768], [1920, 1080], [2560, 1080],
        [2560, 1440], [3840, 2160], [2560, 1600], [2880, 1800],
    ];

    public function analyzeFromPath(string $localPath): array
    {
        $mime = mime_content_type($localPath) ?: '';
        $exif = in_array($mime, ['image/jpeg', 'image/jpg'])
            ? (@exif_read_data($localPath, null, true) ?: [])
            : [];

        $dimensions = @getimagesize($localPath) ?: null;
        $size       = filesize($localPath);

        return $this->buildObservations($mime, $exif, $dimensions, $size);
    }

    public function analyze(UploadedFile $file): array
    {
        $mime       = $file->getMimeType();
        $exif       = in_array($mime, ['image/jpeg', 'image/jpg'])
            ? (@exif_read_data($file->path(), null, true) ?: [])
            : [];
        $dimensions = @getimagesize($file->path()) ?: null;
        $size       = $file->getSize();

        return $this->buildObservations($mime, $exif, $dimensions, $size);
    }

    private function buildObservations(string $mime, array $exif, ?array $dimensions, int $size): array
    {
        $observations = [];

        if ($this->isWhatsAppCompressedRaw($size, $exif, $dimensions)) {
            $observations[] = 'Imagem parece ter sido comprimida e redimensionada pelo WhatsApp, o que pode reduzir a precisão da análise. Para resultados mais confiáveis, envie a imagem original.';
        } elseif ($this->isScreenshotRaw($mime, $exif, $dimensions)) {
            $observations[] = 'Imagem parece ser um screenshot. Capturas de tela podem conter padrões visuais que confundem detectores de IA. Para melhor análise, envie a imagem original gerada.';
        }

        return $observations;
    }

    private function isWhatsAppCompressedRaw(int $size, array $exif, ?array $dimensions): bool
    {
        $software = $exif['IFD0']['Software'] ?? '';
        if (stripos($software, 'WhatsApp') !== false) {
            return true;
        }

        return $this->hasStrippedExif($exif)
            && $this->isWithinWhatsAppDimensions($dimensions)
            && $size < self::WHATSAPP_MAX_SIZE_BYTES;
    }

    private function isScreenshotRaw(string $mime, array $exif, ?array $dimensions): bool
    {
        $software = strtolower($exif['IFD0']['Software'] ?? '');
        foreach (self::SCREENSHOT_SOFTWARE_KEYWORDS as $keyword) {
            if (str_contains($software, $keyword)) {
                return $this->hasStrippedExif($exif);
            }
        }

        if ($mime === 'image/png' && $this->hasStrippedExif($exif)) {
            return $this->matchesScreenResolution($dimensions);
        }

        return false;
    }

    private function hasStrippedExif(array $exif): bool
    {
        $hasCamera   = isset($exif['IFD0']['Make']) || isset($exif['IFD0']['Model']);
        $hasGps      = isset($exif['GPS']);
        $hasDatetime = isset($exif['EXIF']['DateTimeOriginal']);

        return ! $hasCamera && ! $hasGps && ! $hasDatetime;
    }

    private function isWithinWhatsAppDimensions(?array $dimensions): bool
    {
        if (! $dimensions) {
            return false;
        }

        return max($dimensions[0], $dimensions[1]) <= 1600;
    }

    private function matchesScreenResolution(?array $dimensions): bool
    {
        if (! $dimensions) {
            return false;
        }

        [$w, $h] = [$dimensions[0], $dimensions[1]];
        $all = array_merge(
            self::MOBILE_RESOLUTIONS,
            array_map(fn ($r) => [$r[1], $r[0]], self::MOBILE_RESOLUTIONS), // landscape variants
            self::DESKTOP_RESOLUTIONS,
            array_map(fn ($r) => [$r[1], $r[0]], self::DESKTOP_RESOLUTIONS),
        );

        foreach ($all as [$rw, $rh]) {
            if ($w === $rw && $h === $rh) {
                return true;
            }
        }

        return false;
    }
}
