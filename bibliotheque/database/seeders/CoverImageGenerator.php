<?php

namespace Database\Seeders;

class CoverImageGenerator
{
    public static function generate(string $dir, int $width = 400, int $height = 600): ?string
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.jpg';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $img = @imagecreatetruecolor($width, $height);
        if (!$img) {
            return null;
        }

        $bg = imagecolorallocate($img, rand(30, 200), rand(30, 200), rand(30, 200));
        imagefill($img, 0, 0, $bg);

        $textColor = imagecolorallocate($img, 255, 255, 255);
        $text = 'Cover ' . rand(1, 999);
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        imagestring($img, $fontSize, (int)$x, (int)$y, $text, $textColor);

        $x2 = ($width - $textWidth) / 2;
        $y2 = $y + $textHeight + 30;
        imagestring($img, 3, (int)$x2, (int)$y2, 'Bibliotheque', $textColor);

        imagejpeg($img, $path, 80);
        imagedestroy($img);

        return $filename;
    }
}
