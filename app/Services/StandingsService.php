<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Klasemen liga sepak bola dari API publik ESPN (tanpa API key).
 * Hasil di-cache agar tidak memanggil API di setiap request.
 */
class StandingsService
{
    private const ENDPOINT = 'https://site.api.espn.com/apis/v2/sports/soccer/%s/standings';

    /** Liga yang ditampilkan (bisa diganti lewat tombol panah di halaman utama). */
    private const LEAGUES = [
        'eng.1' => ['title' => ['English', 'Premier League', 'Standings'], 'logo' => 'images/sport/premier-league.png'],
        'esp.1' => ['title' => ['Spanish', 'La Liga', 'Standings'], 'logo' => 'https://a.espncdn.com/i/leaguelogos/soccer/500/15.png'],
        'ita.1' => ['title' => ['Italian', 'Serie A', 'Standings'], 'logo' => 'https://a.espncdn.com/i/leaguelogos/soccer/500/12.png'],
        'ger.1' => ['title' => ['German', 'Bundesliga', 'Standings'], 'logo' => 'https://a.espncdn.com/i/leaguelogos/soccer/500/10.png'],
    ];

    /** @return array<int, array{code:string,title:string[],logo:string,season:string,teams:array}> */
    public function leagues(int $limit = 6): array
    {
        $leagues = [];
        foreach (self::LEAGUES as $code => $meta) {
            $teams = $this->table($code);
            if ($teams === []) {
                continue;
            }
            $leagues[] = $meta + [
                'code' => $code,
                'logo' => media_url($meta['logo']),
                'season' => $teams[0]['season'] ?? '',
                'teams' => array_slice($teams, 0, $limit),
            ];
        }

        return $leagues;
    }

    private function table(string $code): array
    {
        $key = "standings:{$code}";
        if ($cached = Cache::get($key)) {
            return $cached;
        }

        try {
            $json = Http::timeout(10)->acceptJson()->get(sprintf(self::ENDPOINT, $code))->throw()->json();
            $standing = $json['children'][0]['standings'] ?? [];
            $teams = collect($standing['entries'] ?? [])->map(function (array $entry) use ($standing) {
                $stats = collect($entry['stats'] ?? [])->mapWithKeys(fn ($s) => [$s['name'] => $s['displayValue'] ?? $s['value'] ?? '']);

                return [
                    'rank' => (int) $stats->get('rank', 0),
                    'club' => $entry['team']['displayName'] ?? '-',
                    'logo' => $entry['team']['logos'][0]['href'] ?? null,
                    'gp' => $stats->get('gamesPlayed', 0),
                    'w' => $stats->get('wins', 0),
                    'd' => $stats->get('ties', 0),
                    'l' => $stats->get('losses', 0),
                    'f' => $stats->get('pointsFor', 0),
                    'a' => $stats->get('pointsAgainst', 0),
                    'gd' => $stats->get('pointDifferential', 0),
                    'pts' => $stats->get('points', 0),
                    'season' => $standing['seasonDisplayName'] ?? '',
                ];
            })->sortBy('rank')->values()->all();

            if ($teams !== []) {
                Cache::put($key, $teams, now()->addMinutes(30));
            }

            return $teams;
        } catch (Throwable $e) {
            Log::warning("Gagal mengambil klasemen {$code}: {$e->getMessage()}");

            return [];
        }
    }
}
