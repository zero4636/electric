<?php

/**
 * Script làm sạch và tách file CSV thô thành các file CSV chuẩn cho từng bảng
 * 
 * Cách chạy: php scripts/parse-csv-data.php
 */

$inputFile = __DIR__ . '/../database/csv/data.csv';
$outputDir = __DIR__ . '/../database/csv';

// Tạo thư mục output nếu chưa có
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "🔧 BẮT ĐẦU XỬ LÝ FILE CSV\n";
echo "═══════════════════════════════════════\n";
echo "Input:  {$inputFile}\n";
echo "Output: {$outputDir}/\n\n";

// Đọc file CSV
$rows = [];
if (($handle = fopen($inputFile, 'r')) !== false) {
    while (($data = fgetcsv($handle, 10000, ',')) !== false) {
        $rows[] = $data;
    }
    fclose($handle);
}

echo "✓ Đọc được " . count($rows) . " dòng\n\n";

// Bỏ qua 3 dòng đầu (header thừa) và lấy dòng header thực tế ở dòng 4
$dataRows = array_slice($rows, 4);

// Lọc bỏ các dòng trống hoặc không hợp lệ
$validRows = array_filter($dataRows, function($row) {
    // Bỏ dòng nếu STT trống hoặc không phải số
    if (empty($row[0]) || !is_numeric($row[0])) {
        return false;
    }
    // Bỏ dòng nếu không có tên hộ tiêu thụ
    if (empty($row[1])) {
        return false;
    }
    return true;
});

echo "✓ Lọc còn " . count($validRows) . " dòng hợp lệ\n\n";

// ═══════════════════════════════════════════════════════════════
// 1. TRẠM BIẾN ÁP (SUBSTATIONS)
// ═══════════════════════════════════════════════════════════════
echo "📍 Tạo file substations.csv...\n";

$substations = [];
foreach ($validRows as $row) {
    $code = trim($row[12] ?? ''); // Cột Trạm biến áp
    if (!empty($code) && !isset($substations[$code])) {
        $substations[$code] = [
            'code' => $code,
            'name' => "Trạm {$code}",
            'location' => 'Khu vực Bách Khoa Hà Nội',
            'capacity_kva' => 1000,
            'voltage_level' => 22,
            'status' => 'ACTIVE'
        ];
    }
}

writeCSV($outputDir . '/substations.csv', 
    ['code', 'name', 'location', 'capacity_kva', 'voltage_level', 'status'],
    array_values($substations)
);
echo "  → Tạo " . count($substations) . " trạm biến áp\n\n";

// ═══════════════════════════════════════════════════════════════
// 2. TÒA NHÀ (BUILDINGS)
// ═══════════════════════════════════════════════════════════════
echo "🏢 Tạo file buildings.csv...\n";

$buildings = [];
foreach ($validRows as $row) {
    $buildingName = trim($row[7] ?? ''); // Cột Nhà/Tòa nhà
    $substationCode = trim($row[12] ?? '');
    
    if (!empty($buildingName) && !isset($buildings[$buildingName])) {
        $buildings[$buildingName] = [
            'code' => 'BLD-' . str_pad(count($buildings) + 1, 3, '0', STR_PAD_LEFT),
            'name' => "Nhà {$buildingName}",
            'substation_code' => $substationCode,
            'address' => 'Đại học Bách Khoa Hà Nội',
            'floors' => null,
            'status' => 'ACTIVE'
        ];
    }
}

writeCSV($outputDir . '/buildings.csv',
    ['code', 'name', 'substation_code', 'address', 'floors', 'status'],
    array_values($buildings)
);
echo "  → Tạo " . count($buildings) . " tòa nhà\n\n";

// ═══════════════════════════════════════════════════════════════
// 3. ĐƠN VỊ TỔ CHỨC (ORGANIZATION UNITS)
// ═══════════════════════════════════════════════════════════════
echo "🏛️ Tạo file organization_units.csv...\n";

$organizations = [];
$orgCounter = 1;

foreach ($validRows as $row) {
    $consumerName = trim($row[1] ?? ''); // Hộ tiêu thụ điện
    $parentName = trim($row[2] ?? ''); // Đơn vị chủ quản
    $phone = trim($row[4] ?? ''); // Điện thoại hộ tiêu thụ
    $representative = trim($row[5] ?? ''); // Đại diện
    $repPhone = trim($row[6] ?? ''); // Điện thoại người đại diện
    
    if (empty($consumerName)) continue;
    
    // Tạo đơn vị cha nếu chưa có
    $parentCode = null;
    if (!empty($parentName) && !isset($organizations[$parentName])) {
        $parentCode = 'ORG-' . str_pad($orgCounter++, 3, '0', STR_PAD_LEFT);
        $organizations[$parentName] = [
            'code' => $parentCode,
            'name' => $parentName,
            'type' => 'ORGANIZATION',
            'parent_code' => null,
            'contact_person' => null,
            'contact_phone' => null,
            'email' => null,
            'address' => null
        ];
    } elseif (isset($organizations[$parentName])) {
        $parentCode = $organizations[$parentName]['code'];
    }
    
    // Tạo hộ tiêu thụ
    if (!isset($organizations[$consumerName])) {
        $organizations[$consumerName] = [
            'code' => 'CONSUMER-' . str_pad($orgCounter++, 3, '0', STR_PAD_LEFT),
            'name' => $consumerName,
            'type' => 'CONSUMER',
            'parent_code' => $parentCode,
            'contact_person' => $representative ?: null,
            'contact_phone' => $repPhone ?: $phone ?: null,
            'email' => null,
            'address' => trim($row[3] ?? '') ?: null
        ];
    }
}

writeCSV($outputDir . '/organization_units.csv',
    ['code', 'name', 'type', 'parent_code', 'contact_person', 'contact_phone', 'email', 'address'],
    array_values($organizations)
);
echo "  → Tạo " . count($organizations) . " đơn vị\n\n";

// ═══════════════════════════════════════════════════════════════
// 4. CÔNG TƠ ĐIỆN (ELECTRIC METERS)
// ═══════════════════════════════════════════════════════════════
echo "⚡ Tạo file electric_meters.csv...\n";

$meters = [];
$meterCounter = 1;

// Tạo mapping tên → code cho lookup nhanh
$orgNameToCode = array_column($organizations, 'code', 'name');
$buildingNameToCode = [];
foreach ($buildings as $name => $building) {
    $buildingNameToCode[$name] = $building['code'];
}

foreach ($validRows as $row) {
    $meterNumber = trim($row[9] ?? ''); // Số công tơ
    $consumerName = trim($row[1] ?? '');
    $buildingName = trim($row[7] ?? '');
    $substationCode = trim($row[12] ?? '');
    $meterType = trim($row[10] ?? ''); // Loại công tơ
    $location = trim($row[11] ?? ''); // Vị trí đặt công tơ
    $subsidized = (int)trim($row[18] ?? 0); // Bao cấp
    
    if (empty($meterNumber)) continue;
    
    // Xử lý trường hợp nhiều công tơ trong 1 ô (VD: "9094, 4383")
    $meterNumbers = array_map('trim', explode(',', $meterNumber));
    
    foreach ($meterNumbers as $meter) {
        if (empty($meter)) continue;
        
        // Xác định tariff_type_id dựa vào loại công tơ
        $tariffTypeId = 2; // Mặc định: Thương mại
        if (stripos($meterType, '1 pha') !== false) {
            $tariffTypeId = 1; // Sinh hoạt
        } elseif (stripos($meterType, '3 pha') !== false) {
            $tariffTypeId = 2; // Thương mại
        }
        
        $meters[] = [
            'meter_number' => $meter,
            'organization_unit_code' => $orgNameToCode[$consumerName] ?? null,
            'building_code' => $buildingNameToCode[$buildingName] ?? null,
            'substation_code' => $substationCode ?: null,
            'tariff_type_id' => $tariffTypeId,
            'subsidized_kwh' => $subsidized > 0 ? $subsidized : 0,
            'location' => $location ?: null,
            'installation_date' => '2025-01-01',
            'status' => 'ACTIVE'
        ];
    }
}

writeCSV($outputDir . '/electric_meters.csv',
    ['meter_number', 'organization_unit_code', 'building_code', 'substation_code', 'tariff_type_id', 'subsidized_kwh', 'location', 'installation_date', 'status'],
    $meters
);
echo "  → Tạo " . count($meters) . " công tơ điện\n\n";

// ═══════════════════════════════════════════════════════════════
// 5. CHỈ SỐ CÔNG TƠ (METER READINGS) - Tháng 6/2025
// ═══════════════════════════════════════════════════════════════
echo "📊 Tạo file meter_readings.csv...\n";

$readings = [];
foreach ($validRows as $row) {
    $meterNumber = trim($row[9] ?? '');
    $newReading = trim(str_replace([',', ' '], '', $row[14] ?? '')); // Chỉ số mới
    $oldReading = trim(str_replace([',', ' '], '', $row[15] ?? '')); // Chỉ số cũ
    $multiplier = (int)trim($row[16] ?? 1); // Hệ số nhân
    
    if (empty($meterNumber) || empty($newReading)) continue;
    
    // Xử lý nhiều công tơ
    $meterNumbers = array_map('trim', explode(',', $meterNumber));
    
    foreach ($meterNumbers as $meter) {
        if (empty($meter)) continue;
        
        // Chỉ số cũ (tháng 5/2025)
        if (!empty($oldReading) && is_numeric($oldReading)) {
            $readings[] = [
                'meter_number' => $meter,
                'reading_date' => '2025-05-30',
                'current_reading' => (float)$oldReading,
                'previous_reading' => null,
                'consumption' => 0,
                'multiplier' => $multiplier,
                'notes' => 'Chỉ số tháng 5/2025'
            ];
        }
        
        // Chỉ số mới (tháng 6/2025)
        if (is_numeric($newReading)) {
            $consumption = 0;
            if (is_numeric($oldReading)) {
                $consumption = ((float)$newReading - (float)$oldReading) * $multiplier;
            }
            
            $readings[] = [
                'meter_number' => $meter,
                'reading_date' => '2025-06-30',
                'current_reading' => (float)$newReading,
                'previous_reading' => is_numeric($oldReading) ? (float)$oldReading : null,
                'consumption' => $consumption,
                'multiplier' => $multiplier,
                'notes' => 'Chỉ số tháng 6/2025'
            ];
        }
    }
}

writeCSV($outputDir . '/meter_readings.csv',
    ['meter_number', 'reading_date', 'current_reading', 'previous_reading', 'consumption', 'multiplier', 'notes'],
    $readings
);
echo "  → Tạo " . count($readings) . " chỉ số công tơ\n\n";

// ═══════════════════════════════════════════════════════════════
// HOÀN TẤT
// ═══════════════════════════════════════════════════════════════
echo "═══════════════════════════════════════\n";
echo "✅ HOÀN TẤT!\n\n";
echo "📁 Các file đã tạo:\n";
echo "   - substations.csv (" . count($substations) . " records)\n";
echo "   - buildings.csv (" . count($buildings) . " records)\n";
echo "   - organization_units.csv (" . count($organizations) . " records)\n";
echo "   - electric_meters.csv (" . count($meters) . " records)\n";
echo "   - meter_readings.csv (" . count($readings) . " records)\n\n";
echo "🚀 Tiếp theo, chạy:\n";
echo "   docker compose exec cli php artisan db:seed\n\n";

// ═══════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════

function writeCSV($filename, $headers, $data) {
    $handle = fopen($filename, 'w');
    
    // Thêm BOM để Excel đọc được UTF-8
    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Ghi header
    fputcsv($handle, $headers);
    
    // Ghi dữ liệu
    foreach ($data as $row) {
        $rowData = [];
        foreach ($headers as $header) {
            $rowData[] = $row[$header] ?? '';
        }
        fputcsv($handle, $rowData);
    }
    
    fclose($handle);
}
