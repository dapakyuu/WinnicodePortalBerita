<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Prakiraan cuaca dari Open-Meteo (gratis, tanpa API key).
 * Satu request mengambil data 5 kota sekaligus.
 */
class WeatherService
{
    private const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    private const CITIES = [
        ['name' => 'DKI Jakarta', 'lat' => -6.2088, 'lon' => 106.8456, 'tz' => 'Asia/Jakarta'],
        ['name' => 'Banten', 'lat' => -6.1200, 'lon' => 106.1503, 'tz' => 'Asia/Jakarta', 'theme' => 'orange', 'image' => 'images/weather/city-banten.png'],
        ['name' => 'Jawa Barat', 'lat' => -6.9175, 'lon' => 107.6191, 'tz' => 'Asia/Jakarta', 'theme' => 'blue', 'image' => 'images/weather/city-jabar.png'],
        ['name' => 'Bali', 'lat' => -8.6500, 'lon' => 115.2167, 'tz' => 'Asia/Makassar', 'theme' => 'green', 'image' => 'images/weather/city-bali.png'],
        ['name' => 'Jawa Tengah', 'lat' => -6.9932, 'lon' => 110.4203, 'tz' => 'Asia/Jakarta', 'theme' => 'purple', 'image' => 'images/weather/city-jateng.png'],
    ];

    /** @return array{main: ?array, cities: array} */
    public function forecast(): array
    {
        $raw = $this->fetch();
        if ($raw === null) {
            return ['main' => null, 'cities' => []];
        }

        $main = $this->buildMain(self::CITIES[0], $raw[0]);
        $cities = [];
        foreach (array_slice(self::CITIES, 1) as $i => $city) {
            $cities[] = $this->buildCity($city, $raw[$i + 1]);
        }

        return ['main' => $main, 'cities' => $cities];
    }

    private function fetch(): ?array
    {
        if ($cached = Cache::get('weather:forecast')) {
            return $cached;
        }

        try {
            $data = Http::timeout(10)->get(self::ENDPOINT, [
                'latitude' => implode(',', array_column(self::CITIES, 'lat')),
                'longitude' => implode(',', array_column(self::CITIES, 'lon')),
                'current' => 'temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,is_day',
                'hourly' => 'temperature_2m,precipitation_probability',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                'timezone' => 'auto',
                'forecast_days' => 8,
            ])->throw()->json();

            // Untuk banyak lokasi, Open-Meteo mengembalikan array; untuk satu lokasi berupa objek.
            $data = array_is_list($data) ? $data : [$data];
            if (count($data) !== count(self::CITIES)) {
                return null;
            }
            Cache::put('weather:forecast', $data, now()->addMinutes(30));

            return $data;
        } catch (Throwable $e) {
            Log::warning('Gagal mengambil data cuaca: ' . $e->getMessage());

            return null;
        }
    }

    private function buildMain(array $city, array $data): array
    {
        $now = Carbon::now($city['tz']);
        $current = $data['current'];

        // Grafik: 8 titik tiap 3 jam mulai dari jam sekarang
        $times = $data['hourly']['time'];
        $startIndex = array_search($now->copy()->startOfHour()->format('Y-m-d\TH:00'), $times, true) ?: 0;
        $points = [];
        for ($i = 0; $i < 8; $i++) {
            $idx = min($startIndex + $i * 3, count($times) - 1);
            $points[] = [
                'hour' => Carbon::parse($times[$idx])->format('g A'),
                'temp' => (int) round($data['hourly']['temperature_2m'][$idx]),
            ];
        }

        $days = [];
        foreach ($data['daily']['time'] as $i => $date) {
            $days[] = [
                'day' => Carbon::parse($date)->format('D'),
                'icon' => $this->dayIcon($data['daily']['weather_code'][$i], true),
                'high' => (int) round($data['daily']['temperature_2m_max'][$i]),
                'low' => (int) round($data['daily']['temperature_2m_min'][$i]),
                'active' => $i === 0,
            ];
        }

        return [
            'city' => $city['name'],
            'time' => $now->format('l H:i'),
            'temp' => (int) round($current['temperature_2m']),
            'icon' => $this->dayIcon($current['weather_code'], (bool) $current['is_day']),
            'precipitation' => ($data['hourly']['precipitation_probability'][$startIndex] ?? 0) . '%',
            'humidity' => $current['relative_humidity_2m'] . '%',
            'wind' => round($current['wind_speed_10m']) . ' km/h',
            'chart' => $this->chart($points),
            'days' => $days,
        ];
    }

    private function buildCity(array $city, array $data): array
    {
        $now = Carbon::now($city['tz']);
        $current = $data['current'];
        $hourIndex = array_search($now->copy()->startOfHour()->format('Y-m-d\TH:00'), $data['hourly']['time'], true) ?: 0;

        return [
            'city' => $city['name'],
            'time' => $now->format('l g:i A'),
            'temp' => (int) round($current['temperature_2m']),
            'precipitation' => ($data['hourly']['precipitation_probability'][$hourIndex] ?? 0) . '%',
            'humidity' => $current['relative_humidity_2m'] . '%',
            'wind' => round($current['wind_speed_10m']) . ' km/h',
            'icon' => $this->cityIcon($current['weather_code'], (bool) $current['is_day']),
            'image' => $city['image'],
            'theme' => $city['theme'],
        ];
    }

    /**
     * Posisi label & kurva grafik suhu (koordinat di dalam widget 744px).
     * Label jam berada di x = 60 + 89i, suhu dipetakan ke rentang y 147–247.
     */
    private function chart(array $points): array
    {
        $temps = array_column($points, 'temp');
        $min = min($temps);
        $range = max(max($temps) - $min, 1);

        foreach ($points as $i => &$p) {
            $p['x'] = 60 + $i * 89;
            $p['y'] = (int) round(247 - (($p['temp'] - $min) / $range) * 100);
        }
        unset($p);

        // Kurva halus melewati titik (label berada 30px di atas garis)
        $line = array_map(fn ($p) => ['x' => $p['x'], 'y' => $p['y'] + 30], $points);
        array_unshift($line, ['x' => 4, 'y' => $line[0]['y']]);
        $line[] = ['x' => 740, 'y' => end($line)['y']];
        $path = 'M' . $line[0]['x'] . ',' . $line[0]['y'];
        for ($k = 0; $k < count($line) - 1; $k++) {
            $p0 = $line[max($k - 1, 0)];
            $p1 = $line[$k];
            $p2 = $line[$k + 1];
            $p3 = $line[min($k + 2, count($line) - 1)];
            $path .= sprintf(
                ' C%.1f,%.1f %.1f,%.1f %d,%d',
                $p1['x'] + ($p2['x'] - $p0['x']) / 6, $p1['y'] + ($p2['y'] - $p0['y']) / 6,
                $p2['x'] - ($p3['x'] - $p1['x']) / 6, $p2['y'] - ($p3['y'] - $p1['y']) / 6,
                $p2['x'], $p2['y']
            );
        }

        return ['points' => $points, 'path' => $path];
    }

    /** Ikon cuaca berwarna (aset Figma) berdasarkan kode cuaca WMO. */
    private function dayIcon(int $code, bool $isDay): string
    {
        $file = match (true) {
            $code >= 95 => 'day-thunder',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'day-snow',
            in_array($code, [63, 65, 81, 82, 67], true) => 'day-heavy-rain',
            $code >= 51 => 'day-rain',
            $code >= 2 => 'day-partly',
            default => $isDay ? 'day-tue' : 'day-night',
        };

        return "images/weather/{$file}.svg";
    }

    /** Ikon putih untuk kartu kota. */
    private function cityIcon(int $code, bool $isDay): string
    {
        $file = match (true) {
            $code >= 95 || in_array($code, [63, 65, 81, 82, 67], true) => 'icon-jabar',
            $code >= 51 => 'icon-bali',
            ! $isDay => 'icon-jateng',
            default => 'icon-banten',
        };

        return "images/weather/{$file}.svg";
    }
}
