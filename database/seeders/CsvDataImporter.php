<?php

namespace Database\Seeders;

use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CsvDataImporter extends Seeder
{
    private array $substations = [];
    private array $organizations = [];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = storage_path('app/data.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $this->command->info("Starting CSV import from: {$csvPath}");
        
        DB::beginTransaction();
        
        try {
            // Step 1: Create parent organization (HĐKT)
            $this->createParentOrganization();
            
            // Step 2: Parse CSV and extract unique substations and organizations
            $csvData = $this->parseCSV($csvPath);
            
            // Step 3: Create substations
            $this->createSubstations($csvData);
            
            // Step 4: Create organization units
            $this->createOrganizationUnits($csvData);
            
            // Step 5: Import electric meters and readings
            $this->importMetersAndReadings($csvData);
            
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

    /**
     * Create parent organization (HĐKT)
     */
    private function createParentOrganization(): void
    {
        $parent = OrganizationUnit::firstOrCreate(
            ['code' => 'HDKT'],
            [
                'name' => 'Hội đồng Khoa học và Đào tạo',
                'parent_id' => null,
                'type' => 'ORGANIZATION',
                'address' => 'Đại học Bách Khoa Hà Nội',
                'contact_name' => null,
                'contact_phone' => null,
            ]
        );
        
        $this->organizations['HĐKT'] = $parent;
        $this->command->info("Created parent organization: HĐKT");
    }

    /**
     * Parse CSV file and return data array
     */
    private function parseCSV(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        // Skip first 2 header rows
        fgetcsv($handle);
        fgetcsv($handle);
        
        // Read column headers
        $headers = fgetcsv($handle);
        
        // Skip the row with (1), (2), etc.
        fgetcsv($handle);
        
        $rowNumber = 5; // Start from row 5 (after headers)
        
        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Skip if no meter number (column 9)
            if (empty($row[9])) {
                continue;
            }
            
            $data[] = [
                'stt' => $row[0] ?? '',
                'consumer_name' => $row[1] ?? '',
                'parent_org' => $row[2] ?? '',
                'address' => $row[3] ?? '',
                'phone' => $row[4] ?? '',
                'representative' => $row[5] ?? '',
                'representative_phone' => $row[6] ?? '',
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
                'row_number' => $rowNumber++,
            ];
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
        $uniqueSubstations = array_unique(array_column($csvData, 'substation'));
        
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
     * Create organization units from CSV data
     */
    private function createOrganizationUnits(array $csvData): void
    {
        $parentOrg = $this->organizations['HĐKT'];
        
        foreach ($csvData as $row) {
            $orgName = $row['consumer_name'];
            
            if (empty($orgName) || isset($this->organizations[$orgName])) {
                continue;
            }
            
            // Determine parent
            $parentId = $parentOrg->id;
            if (!empty($row['parent_org']) && $row['parent_org'] !== 'HĐKT') {
                // If parent organization is specified, find or create it
                if (!isset($this->organizations[$row['parent_org']])) {
                    $parent = OrganizationUnit::firstOrCreate(
                        ['name' => $row['parent_org']],
                        [
                            'code' => $this->generateCode($row['parent_org']),
                            'parent_id' => $parentOrg->id,
                            'type' => 'ORGANIZATION',
                            'address' => 'Đại học Bách Khoa Hà Nội',
                        ]
                    );
                    $this->organizations[$row['parent_org']] = $parent;
                }
                $parentId = $this->organizations[$row['parent_org']]->id;
            }
            
            $org = OrganizationUnit::firstOrCreate(
                ['name' => $orgName],
                [
                    'code' => $this->generateCode($orgName),
                    'parent_id' => $parentId,
                    'type' => 'CONSUMER',
                    'contact_phone' => $row['phone'],
                    'address' => $row['address'],
                    'contact_name' => $row['representative'],
                ]
            );
            
            $this->organizations[$orgName] = $org;
        }
        
        $this->command->info("Created " . (count($this->organizations) - 1) . " organization units");
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
    private function importMetersAndReadings(array $csvData): void
    {
        $imported = 0;
        $skipped = 0;
        
        foreach ($csvData as $row) {
            try {
                // Get organization
                if (!isset($this->organizations[$row['consumer_name']])) {
                    $this->command->warn("Organization not found: {$row['consumer_name']}");
                    $skipped++;
                    continue;
                }
                
                // Get substation
                if (!isset($this->substations[$row['substation']])) {
                    $this->command->warn("Substation not found: {$row['substation']}");
                    $skipped++;
                    continue;
                }
                
                $org = $this->organizations[$row['consumer_name']];
                $substation = $this->substations[$row['substation']];
                
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
                
                // Create or update electric meter
                $meter = ElectricMeter::updateOrCreate(
                    ['meter_number' => $row['meter_number']],
                    [
                        'organization_unit_id' => $org->id,
                        'substation_id' => $substation->id,
                        'building' => $row['building'],
                        'floor' => $row['floor'],
                        'installation_location' => $row['installation_location'],
                        'meter_type' => $meterType,
                        'phase_type' => $phaseType,
                        'hsn' => $row['hsn'],
                        'subsidized_kwh' => $row['subsidized'],
                        'status' => 'ACTIVE',
                    ]
                );
                
                // Create meter reading for June 2025
                $readingDate = '2025-06-30';
                
                // Create old reading (beginning of month)
                if ($row['old_reading'] > 0) {
                    MeterReading::updateOrCreate(
                        [
                            'electric_meter_id' => $meter->id,
                            'reading_date' => '2025-06-01',
                        ],
                        [
                            'reading_value' => $row['old_reading'],
                        ]
                    );
                }
                
                // Create new reading (end of month)
                if ($row['new_reading'] > 0) {
                    MeterReading::updateOrCreate(
                        [
                            'electric_meter_id' => $meter->id,
                            'reading_date' => $readingDate,
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
}
