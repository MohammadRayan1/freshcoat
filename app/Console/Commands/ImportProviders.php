<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Provider;
use App\Models\Service;
use App\Support\States;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Loads unclaimed business listings from a CSV so painters can find and claim them.
 *
 * Columns: business_name, phone, email, website, city, state, latitude, longitude, services
 * "services" is a list of service slugs separated by "|". Leave it empty to list all services.
 * Only import data you have the right to use, and don't add businesses that asked to be removed.
 */
#[Signature('providers:import {file : Path to the CSV file} {--dry-run : Validate rows without saving}')]
#[Description('Import unclaimed painting business listings from a CSV file')]
class ImportProviders extends Command
{
    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_readable($path)) {
            $this->error("Can't read {$path}.");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = array_map(fn ($h) => Str::snake(trim((string) $h)), fgetcsv($handle) ?: []);
        $required = ['business_name', 'city', 'state'];

        if ($missing = array_diff($required, $header)) {
            $this->error('Missing columns: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $allServices = Service::pluck('id', 'slug');
        $created = $updated = $skipped = 0;
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            $data = array_combine($header, array_pad($row, count($header), null));
            $state = strtoupper(trim((string) $data['state']));

            if (! States::isLaunched($state) || trim((string) $data['business_name']) === '') {
                $this->warn("Line {$line}: skipped (missing name, or {$state} isn't a launch state).");
                $skipped++;

                continue;
            }

            $lat = is_numeric($data['latitude'] ?? null) ? (float) $data['latitude'] : null;
            $lng = is_numeric($data['longitude'] ?? null) ? (float) $data['longitude'] : null;

            $city = City::firstOrCreate(
                ['state_code' => $state, 'slug' => Str::slug($data['city'])],
                ['name' => trim($data['city']), 'state_name' => States::ALL[$state], 'latitude' => $lat ?? 0, 'longitude' => $lng ?? 0],
            );

            if ($lat === null && (float) $city->latitude === 0.0) {
                $this->warn("Line {$line}: skipped (no coordinates for {$data['city']}, {$state}).");
                $skipped++;

                continue;
            }

            $slug = Str::slug("{$data['business_name']} {$city->name} {$state}");
            $serviceIds = collect(explode('|', (string) ($data['services'] ?? '')))
                ->map(fn ($s) => trim($s))->filter()
                ->map(fn ($s) => $allServices[$s] ?? null)->filter()->values();

            if ($this->option('dry-run')) {
                $created++;

                continue;
            }

            $provider = Provider::where('slug', $slug)->first();

            if ($provider?->isClaimed()) {
                $skipped++;

                continue;
            }

            $provider = Provider::updateOrCreate(['slug' => $slug], [
                'business_name' => trim($data['business_name']),
                'city_id' => $city->id,
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'website' => $data['website'] ?: null,
                'latitude' => $lat ?? $city->latitude,
                'longitude' => $lng ?? $city->longitude,
            ]);
            $provider->services()->sync($serviceIds->isEmpty() ? $allServices->values() : $serviceIds);

            $provider->wasRecentlyCreated ? $created++ : $updated++;
        }

        fclose($handle);

        $this->info(($this->option('dry-run') ? 'Dry run: ' : '')."{$created} created, {$updated} updated, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
