<?php

namespace Database\Seeders;

use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use App\Models\TariffType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CsvDataImporter extends Seeder
{
    private array $substations = [];
    private array $units = [];
    private array $consumers = [];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $csvPath = storage_path('app/Bảng tổng hợp thu tháng 10 năm 2025.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $this->command->info("Starting CSV import from: {$csvPath}");
        
        DB::beginTransaction();
        
        try {
            // Step 1: Parse CSV rows
            $csvData = $this->parseCSV($csvPath);
            
            // Step 2: Create substations
            $this->createSubstations($csvData);
            
            // Step 3: Create organization units (two levels: UNIT -> CONSUMER)
            $this->createOrganizationUnits($csvData);
            
            // Step 4: Import electric meters and readings for the CSV month
            $this->importMetersAndReadings($csvPath, $csvData);
            
            DB::commit();
            
            $this->command->info("CSV import completed successfully!");
            $this->command->info("Imported " . count($csvData) . " records");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Import failed: " . $e->getMessage());
            Log::error("CSV Import Error", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // two-level model, no post-fix needed

    // no root organization in two-level model

    /**
     * Parse CSV file and return data array
     */
    private function parseCSV(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        // Read first header line
        $headers = fgetcsv($handle);
        // Some files include an ordinal row like (1),(2),...
        $maybeOrdinals = fgetcsv($handle);
        if (!empty($maybeOrdinals) && isset($maybeOrdinals[0]) && preg_match('/^\(1\)/', trim($maybeOrdinals[0] ?? ''))) {
            // consume it and proceed
        } else {
            if ($maybeOrdinals && !empty(array_filter($maybeOrdinals))) {
                $this->normalizeCsvRow($maybeOrdinals);
                $first = $this->mapCsvRow($maybeOrdinals);
                $first['row_number'] = 2;
                $data[] = $first;
            }
        }
        
        $rowNumber = 5; // Start from row 5 (after headers)
        
        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Normalize strings
            $this->normalizeCsvRow($row);

            // Skip if no meter number (column 9)
            if (empty($row[9]) || strtolower(trim($row[9])) === 'số công tơ') {
                continue;
            }
            $mapped = $this->mapCsvRow($row);
            $mapped['row_number'] = $rowNumber++;
            $data[] = $mapped;
        }
        
        fclose($handle);
        
        $this->command->info("Parsed " . count($data) . " records from CSV");
        
        return $data;
    }

    /**
     * Parse Vietnamese number format (with commas and quotes)
     */
    private function parseNumber(string $value): float
    {
        // Remove quotes, spaces, and commas
        $cleaned = str_replace(['"', "'", ' ', ','], '', $value);
        return $cleaned === '' ? 0 : (float) $cleaned;
    }

    /**
     * Create substations from CSV data
     */
    private function createSubstations(array $csvData): void
    {
    $uniqueSubstations = array_unique(array_filter(array_map(fn($r) => $r['substation'] ?? '', $csvData)));
        
        foreach ($uniqueSubstations as $substationCode) {
            if (empty($substationCode)) {
                continue;
            }
            
            $substation = Substation::firstOrCreate(
                ['code' => $substationCode],
                [
                    'name' => 'Trạm biến áp ' . $substationCode,
                    'location' => 'Đại học Bách Khoa Hà Nội',
                ]
            );
            
            $this->substations[$substationCode] = $substation;
        }
        
        $this->command->info("Created " . count($this->substations) . " substations");
    }

    /**
     * Create organization units (UNIT parent, CONSUMER child or independent)
     */
    private function createOrganizationUnits(array $csvData): void
    {
        $createdUnits = 0;
        $createdConsumers = 0;
        $createdIndependent = 0;

        foreach ($csvData as $row) {
            $unitName = trim($row['parent_org'] ?? '');
            $consumerName = trim($row['consumer_name'] ?? '');
            
            // Skip if consumer name is empty
            if ($consumerName === '') {
                continue;
            }

            // Case 1: Consumer has parent UNIT (standard case)
            if ($unitName !== '') {
                // Create/find UNIT (no parent)
                if (!isset($this->units[$unitName])) {
                    $unit = OrganizationUnit::firstOrCreate(
                        ['name' => $unitName, 'type' => 'UNIT'],
                        [
                            'code' => $this->generateCode($unitName),
                            'parent_id' => null,
                            'type' => 'UNIT',
                            'address' => null,
                            'status' => 'ACTIVE',
                        ]
                    );
                    $this->units[$unitName] = $unit;
                    $createdUnits++;
                } else {
                    $unit = $this->units[$unitName];
                }

                // Create/find CONSUMER (child of UNIT)
                // Use composite key: consumerName + parentId to handle duplicates
                $consumerKey = $consumerName . '_' . $unit->id;
                if (!isset($this->consumers[$consumerKey])) {
                    $consumer = OrganizationUnit::firstOrCreate(
                        ['name' => $consumerName, 'parent_id' => $unit->id],
                        [
                            'code' => $this->generateCode($consumerName),
                            'parent_id' => $unit->id,
                            'type' => 'CONSUMER',
                            'contact_phone' => $row['phone'] ?? null,
                            'address' => $row['address'] ?? null,
                            'building' => $row['building'] ?? null,
                            'contact_name' => $row['representative'] ?? null,
                            'status' => 'ACTIVE',
                        ]
                    );
                    $this->consumers[$consumerKey] = $consumer;
                    $createdConsumers++;
                }
            } 
            // Case 2: Independent consumer (Hợp đồng tự do) - no parent UNIT
            else {
                $consumerKey = $consumerName . '_independent';
                if (!isset($this->consumers[$consumerKey])) {
                    $consumer = OrganizationUnit::firstOrCreate(
                        ['name' => $consumerName, 'parent_id' => null, 'type' => 'CONSUMER'],
                        [
                            'code' => $this->generateCode($consumerName),
                            'parent_id' => null,
                            'type' => 'CONSUMER',
                            'contact_phone' => $row['phone'] ?? null,
                            'address' => $row['address'] ?? null,
                            'building' => $row['building'] ?? null,
                            'contact_name' => $row['representative'] ?? null,
                            'status' => 'ACTIVE',
                        ]
                    );
                    $this->consumers[$consumerKey] = $consumer;
                    $createdIndependent++;
                }
            }
        }

        $this->command->info("Created {$createdUnits} units, {$createdConsumers} consumers, and {$createdIndependent} independent consumers");
    }

    /**
     * Generate unique code from name
     */
    private function generateCode(string $name): string
    {
        // Remove Vietnamese accents and get first letters
        $words = explode(' ', $name);
        $code = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(mb_substr($word, 0, 1));
            }
        }
        
        // If code is empty or too short, use first 4 chars of name
        if (strlen($code) < 2) {
            $code = strtoupper(substr($name, 0, 4));
        }
        
        // Ensure uniqueness by adding suffix if needed
        $originalCode = $code;
        $counter = 1;
        
        while (OrganizationUnit::where('code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Import electric meters and meter readings
     */
    private function importMetersAndReadings(string $csvPath, array $csvData): void
    {
        $imported = 0;
        $skipped = 0;
        $ym = $this->parseMonthYearFromFilename($csvPath) ?? ['2025','10'];
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $ym[0], $ym[1]));
        $endDate = $startDate->copy()->endOfMonth();
        
        foreach ($csvData as $row) {
            try {
                // Get UNIT and CONSUMER
                $unitName = trim($row['parent_org'] ?? '');
                $consumerName = trim($row['consumer_name'] ?? '');
                
                if ($consumerName === '') {
                    $skipped++;
                    continue;
                }
                
                // Determine consumer key based on whether it has a parent UNIT
                $consumerKey = null;
                $org = null;
                
                if ($unitName !== '') {
                    // Standard consumer with parent UNIT
                    $unit = $this->units[$unitName] ?? null;
                    if (!$unit) {
                        $this->command->warn("Unit not found: {$unitName}");
                        $skipped++;
                        continue;
                    }
                    
                    $consumerKey = $consumerName . '_' . $unit->id;
                    $org = $this->consumers[$consumerKey] ?? null;
                    if (!$org) {
                        $this->command->warn("Consumer not found: {$consumerName} (under {$unitName})");
                        $skipped++;
                        continue;
                    }
                } else {
                    // Independent consumer (Hợp đồng tự do)
                    $consumerKey = $consumerName . '_independent';
                    $org = $this->consumers[$consumerKey] ?? null;
                    if (!$org) {
                        $this->command->warn("Independent consumer not found: {$consumerName}");
                        $skipped++;
                        continue;
                    }
                }
                
                // Get substation
                $substation = null;
                if (!empty($row['substation']) && isset($this->substations[$row['substation']])) {
                    $substation = $this->substations[$row['substation']];
                }
                
                // Determine phase type
                $phaseType = null;
                if (stripos($row['phase_type'], '3') !== false) {
                    $phaseType = '3_PHASE';
                } elseif (stripos($row['phase_type'], '1') !== false) {
                    $phaseType = '1_PHASE';
                }
                
                // Determine meter type from organization or default to COMMERCIAL
                $meterType = 'COMMERCIAL'; // Default for businesses/organizations
                
                // Check if it's residential based on consumer name
                $consumerName = strtolower($row['consumer_name']);
                if (strpos($consumerName, 'kiot') !== false || 
                    strpos($consumerName, 'quán') !== false ||
                    strpos($consumerName, 'nhà ăn') !== false) {
                    $meterType = 'COMMERCIAL';
                } elseif (strpos($consumerName, 'phòng') !== false || 
                          strpos($consumerName, 'ký túc') !== false) {
                    $meterType = 'RESIDENTIAL';
                }
                
                // Get tariff type ID based on meter type
                $tariffTypeCode = $meterType === 'RESIDENTIAL' ? 'RESIDENTIAL' : 'COMMERCIAL';
                $tariffType = TariffType::where('code', $tariffTypeCode)->first();
                $tariffTypeId = $tariffType ? $tariffType->id : null;
                
                // Create or update electric meter
                $meter = ElectricMeter::updateOrCreate(
                    ['meter_number' => $row['meter_number']],
                    [
                        'organization_unit_id' => $org->id,
                        'substation_id' => $substation?->id,
                        'tariff_type_id' => $tariffTypeId,
                        'installation_location' => $row['installation_location'],
                        'meter_type' => $meterType,
                        'phase_type' => $phaseType,
                        'hsn' => $row['hsn'],
                        'subsidized_kwh' => 0,
                        'status' => 'ACTIVE',
                    ]
                );
                
                // Create old reading (beginning of month)
                if ($row['old_reading'] >= 0) {
                    MeterReading::updateOrCreate(
                        [
                            'electric_meter_id' => $meter->id,
                            'reading_date' => $startDate->toDateString(),
                        ],
                        [
                            'reading_value' => $row['old_reading'],
                        ]
                    );
                }
                
                // Create new reading (end of month)
                if ($row['new_reading'] >= 0) {
                    MeterReading::updateOrCreate(
                        [
                            'electric_meter_id' => $meter->id,
                            'reading_date' => $endDate->toDateString(),
                        ],
                        [
                            'reading_value' => $row['new_reading'],
                        ]
                    );
                }
                
                $imported++;
                
                if ($imported % 10 == 0) {
                    $this->command->info("Imported {$imported} meters...");
                }
                
            } catch (\Exception $e) {
                $this->command->error("Error importing row {$row['row_number']}: " . $e->getMessage());
                $skipped++;
            }
        }
        
        $this->command->info("Import complete: {$imported} meters imported, {$skipped} skipped");
    }

    private function normalizeCsvRow(array &$row): void
    {
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $row[$k] = trim(preg_replace('/\s+/', ' ', $v));
            }
        }
    }

    private function mapCsvRow(array $row): array
    {
        return [
            'stt' => $row[0] ?? '',
            'consumer_name' => $row[1] ?? '',
            'parent_org' => $row[2] ?? '',
            'address' => $row[3] ?? '',
            'phone' => $row[4] ?? '',
            'representative' => $row[5] ?? '',
            // row[6] = Điện thoại người đại diện (bỏ qua - không lưu)
            'building' => $row[7] ?? '',
            'floor' => $row[8] ?? '',
            'meter_number' => $row[9] ?? '',
            'phase_type' => $row[10] ?? '',
            'installation_location' => $row[11] ?? '',
            'substation' => $row[12] ?? '',
            'page' => $row[13] ?? '',
            'new_reading' => $this->parseNumber($row[14] ?? '0'),
            'old_reading' => $this->parseNumber($row[15] ?? '0'),
            'hsn' => $this->parseNumber($row[16] ?? '1'),
            'consumption' => $this->parseNumber($row[17] ?? '0'),
            'subsidized' => $this->parseNumber($row[18] ?? '0'),
            'payable_consumption' => $this->parseNumber($row[19] ?? '0'),
            'unit_price' => $this->parseNumber($row[20] ?? '0'),
            'amount' => $this->parseNumber($row[21] ?? '0'),
            'executor' => $row[22] ?? '',
        ];
    }

    private function parseMonthYearFromFilename(string $csvPath): ?array
    {
        $basename = basename($csvPath);
        if (preg_match('/tháng\s+(\d{1,2})\s+năm\s+(\d{4})/ui', $basename, $m)) {
            return [$m[2], $m[1]]; // [year, month]
        }
        return null;
    }
}
