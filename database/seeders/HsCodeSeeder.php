<?php

namespace Database\Seeders;

use App\Models\HsCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class HsCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/hs_codes.json');

        if (!File::exists($jsonPath)) {
            $this->command?->error("HS codes JSON file not found at: {$jsonPath}");
            return;
        }

        $this->command?->info('Loading HS codes from JSON...');
        $jsonContent = File::get($jsonPath);
        $records = json_decode($jsonContent, true);

        if (empty($records)) {
            $this->command?->error('No HS codes found in JSON file.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        HsCode::truncate();
        Schema::enableForeignKeyConstraints();

        $this->command?->info("Seeding " . count($records) . " HS codes...");

        $now = now();
        $batch = [];

        foreach ($records as $item) {
            $batch[] = [
                'business_id' => null,
                'pct_code' => $item['pct_code'],
                'description' => $item['description'],
                'default_tax_rate' => 18.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                HsCode::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            HsCode::insert($batch);
        }

        $this->command?->info('HS codes successfully seeded!');
    }
}

