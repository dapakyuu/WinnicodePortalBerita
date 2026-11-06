<?php

namespace App\Support;

/**
 * Mengubah isi berita dari editor (teks + penanda sederhana) menjadi HTML aman.
 *
 * Penanda yang didukung (sesuai tombol toolbar editor):
 *   ## Judul bagian            -> <h2>
 *   **tebal**                  -> <strong>
 *   [link](https://...)        -> <a>
 *   ![keterangan](https://...) -> <img>   (tombol "Image")
 *   [color=#f81539]teks[/color]
 *   [center]teks[/center]      (tombol "Align")
 * Baris kosong memisahkan paragraf. Semua teks di-escape terlebih dahulu.
 */
class ContentFormatter
{
    public static function toHtml(?string $content): string
    {
        $blocks = preg_split("/\R{2,}/", trim((string) $content)) ?: [];
        $html = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            if (str_starts_with($block, '## ')) {
                $html[] = '<h2 class="article__heading">' . self::inline(substr($block, 3)) . '</h2>';
                continue;
            }

            if (preg_match('/^!\[([^\]]*)\]\(((?:https?:\/\/|images\/)[^\s)]+)\)$/', $block, $m)) {
                $html[] = '<img src="' . e(media_url($m[2])) . '" alt="' . e($m[1]) . '" class="article__figure" loading="lazy">';
                continue;
            }

            $html[] = '<p class="article__text">' . nl2br(self::inline($block), false) . '</p>';
        }

        return implode("\n", $html);
    }

    public static function toPlainText(?string $content): string
    {
        $text = preg_replace([
            '/^## /m',
            '/!\[[^\]]*\]\([^)]*\)/',
            '/\[([^\]]+)\]\([^)]*\)/',
            '/\[\/?(center|color[^\]]*)\]/',
            '/\*\*/',
        ], ['', '', '$1', '', ''], (string) $content);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private static function inline(string $text): string
    {
        $text = e($text);

        $text = preg_replace_callback(
            '/!\[([^\]]*)\]\(((?:https?:\/\/|images\/)[^\s)]+)\)/',
            fn ($m) => '<img src="' . media_url(html_entity_decode($m[2])) . '" alt="' . $m[1] . '" class="article__figure" loading="lazy">',
            $text
        );
        $text = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener nofollow">$1</a>', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\[color=(#[0-9a-fA-F]{3,8})\](.*?)\[\/color\]/s', '<span style="color:$1">$2</span>', $text);
        $text = preg_replace('/\[center\](.*?)\[\/center\]/s', '<span class="text-center">$1</span>', $text);

        return $text;
    }
}
