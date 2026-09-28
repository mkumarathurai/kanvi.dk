<?php

namespace App\Domain\Polls\Services;

final class PollPreview
{
    public function render(string $title): string
    {
        $tokens = json_decode(file_get_contents(base_path('design-tokens.json')), true, flags: JSON_THROW_ON_ERROR)['brand'];
        $image = imagecreatetruecolor(1200, 630);
        $color = function (string $name) use ($image, $tokens): int {
            $hex = ltrim($tokens[$name], '#');

            return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        };
        $font = resource_path('fonts/Inter.ttf');
        imagefill($image, 0, 0, $color('cream'));
        $mark = imagecreatefrompng(resource_path('images/kanvi-mark.png'));
        imagecopyresampled($image, $mark, 72, 44, 0, 0, 144, 108, imagesx($mark), imagesy($mark));
        imagettftext($image, 20, 0, 210, 110, $color('navy'), $font, 'Find ud af det sammen.');
        // Wrap by grapheme, including unbroken titles; input is limited to 140 characters.
        $title = preg_replace('/\s+/u', ' ', trim($title));
        $size = 44;
        do {
            $lines = $this->wrap($title, $font, $size, 1030);
            if (count($lines) <= 3) {
                break;
            }
            $size -= 2;
        } while ($size >= 14);
        foreach ($lines as $index => $line) {
            imagettftext($image, $size, 0, 80, 235 + $index * 70, $color('navy'), $font, $line);
        }
        imagettftext($image, 25, 0, 80, 445, $color('navy'), $font, 'Find en dag, der passer gruppen.');
        imageline($image, 80, 488, 1120, 488, $color('border'));
        imagettftext($image, 24, 0, 80, 553, $color('navy'), $font, 'Svar på Kanvi');
        imagesetthickness($image, 3);
        imageline($image, 308, 542, 340, 542, $color('navy'));
        imageline($image, 331, 533, 340, 542, $color('navy'));
        imageline($image, 331, 551, 340, 542, $color('navy'));
        imagefilledellipse($image, 1090, 547, 18, 18, $color('green'));
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($mark);
        imagedestroy($image);

        return $png;
    }

    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $lines = [];
        $line = '';
        preg_match_all('/\X/u', $text, $characters);
        foreach ($characters[0] as $character) {
            $box = imagettfbbox($size, 0, $font, $line.$character);
            if ($maxWidth < $box[2] - $box[0] && $line !== '') {
                $space = mb_strrpos($line, ' ');
                if ($space !== false && $space > mb_strlen($line) / 2) {
                    $lines[] = mb_substr($line, 0, $space);
                    $line = ltrim(mb_substr($line, $space + 1).$character);
                } else {
                    $lines[] = $line;
                    $line = ltrim($character);
                }
            } else {
                $line .= $character;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
