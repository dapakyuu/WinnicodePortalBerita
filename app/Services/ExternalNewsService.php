<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

/**
 * Berita dari sumber luar (bagian "Other News") lewat RSS ANTARA News.
 * Sumber RSS bisa diganti lewat .env: EXTERNAL_NEWS_RSS=...
 */
class ExternalNewsService
{
    public function latest(int $limit = 4): array
    {
        $feed = config('services.external_news.rss');

        $key = 'external-news:' . md5($feed) . ":{$limit}";
        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $items = (function () use ($feed, $limit) {
            try {
                $body = Http::timeout(10)->withUserAgent('WinniCodePortalBerita/1.0')->get($feed)->throw()->body();
                $xml = new SimpleXMLElement($body, LIBXML_NOCDATA);
            } catch (Throwable $e) {
                Log::warning('Gagal mengambil RSS berita: ' . $e->getMessage());

                return [];
            }

            $items = [];
            foreach ($xml->channel->item as $item) {
                $image = (string) ($item->enclosure['url'] ?? '');
                if ($image === '') {
                    $media = $item->children('http://search.yahoo.com/mrss/');
                    $image = (string) ($media->content?->attributes()?->url ?? $media->thumbnail?->attributes()?->url ?? '');
                }
                if ($image === '') {
                    continue; // kartu di desain selalu bergambar
                }

                $items[] = [
                    'title' => trim((string) $item->title),
                    'excerpt' => Str::limit(trim(html_entity_decode(strip_tags((string) $item->description))), 200),
                    'image' => $image,
                    'url' => (string) $item->link,
                    'date' => Carbon::parse((string) $item->pubDate)->timezone(config('app.timezone'))->format('F d , Y'),
                ];
                if (count($items) >= $limit) {
                    break;
                }
            }

            return $items;
        })();

        if ($items !== []) {
            Cache::put($key, $items, now()->addMinutes(30));
        }

        return $items;
    }

    public function sourceUrl(): string
    {
        return config('services.external_news.site');
    }
}
