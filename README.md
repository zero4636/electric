# ⚡ Electric Management System

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://www.php.net)
[![Filament](https://img.shields.io/badge/Filament-4.x-orange.svg)](https://filamentphp.com)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11-green.svg)](https://mariadb.org)

> Hệ thống quản lý điện năng và thu tiền điện toàn diện với Laravel & Filament

Quản lý công tơ, đọc số điện, biểu giá bậc thang và hóa đơn. Dashboard analytics với 10+ widgets thời gian thực.


---

## 📊 Tài liệu kỹ thuật

### Tài liệu văn bản
- **[FEATURES.md](docs/FEATURES.md)**: 📋 **Danh sách đầy đủ 80+ tính năng hệ thống** (Review nhanh)
- **[RBAC_LOGIC.md](docs/RBAC_LOGIC.md)**: 🔐 **Chi tiết logic phân quyền, flow authorization** (Mới)
- **[PERMISSION_MATRIX.md](docs/PERMISSION_MATRIX.md)**: 📊 **Ma trận quyền 4 roles x 30 permissions** (Mới)
- **[SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md)**: Mô hình tổng quan, kiến trúc 3-tier, ERD, use cases (Mermaid diagrams)
- **[ARCHITECTURE.md](docs/ARCHITECTURE.md)**: Chi tiết kiến trúc, sequence diagrams, data flow (Mermaid diagrams)
- **[USER_MANUAL.md](docs/USER_MANUAL.md)**: Hướng dẫn sử dụng cho người dùng cuối

### Sơ đồ trực quan (Dễ hiểu, dễ in)
- **[VISUAL_GUIDE.md](docs/VISUAL_GUIDE.md)**: 🎨 Hướng dẫn render sơ đồ PlantUML (PNG/SVG cho báo cáo)
- **[ASCII_DIAGRAMS.md](docs/ASCII_DIAGRAMS.md)**: 📐 Sơ đồ ASCII art xem nhanh (không cần tool)

### Sơ đồ PlantUML (Chất lượng cao)
Trong thư mục `docs/diagrams/`:
- `system-overview.puml` - Kiến trúc 4 tầng với màu sắc
- `use-case.puml` - 18 use cases, 4 actors
- `deployment.puml` - Docker containers + network
- `sequence-create-reading.puml` - Luồng đọc số chi tiết
- `erd.puml` - ERD với 8 entities

**Cách sử dụng:**
1. Mở https://www.plantuml.com/plantuml/uml/
2. Copy nội dung file `.puml`
3. Download PNG/SVG → Chèn vào báo cáo

---

## 1. Yêu cầu

| Thành phần | Phiên bản gợi ý |
|------------|-----------------|
| PHP        | 8.2+            |
| Composer   | 2.x             |
| Node.js    | 18+ (nếu build front) |
| Docker     | 24+ (tuỳ chọn)  |
| MySQL/PostgreSQL | Tuỳ biến (mặc định theo docker-compose) |

## 2. Chạy bằng Docker (Khuyến nghị)

```bash
cd docker
docker compose up -d

# Vào container CLI
docker compose exec cli bash

# Cài dependency PHP (nếu chưa có vendor/)
composer install

# Reset & seed database
php artisan migrate:fresh --seed
```

Sau khi chạy seed thành công:
```
Email: admin@example.com
Password: password
```

## 3. Chạy Local (Không Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate

# Cấu hình DB trong .env rồi tạo bảng
php artisan migrate

# Seed
php artisan db:seed
```

## 4. Import dữ liệu từ CSV

Seeder chính: `DatabaseSeeder` gọi `CsvDataImporter`.

File CSV phải đặt tại: `storage/app/data.csv`.

Chỉ cần chạy:
```bash
php artisan migrate:fresh --seed
```

### Quy trình của `CsvDataImporter`
1. Tạo đơn vị cha HĐKT
2. Parse `data.csv` (bỏ qua header đặc biệt)
3. Tạo trạm biến áp (substations)
4. Tạo đơn vị tổ chức & hộ tiêu thụ (organization_units)
5. Tạo công tơ (electric_meters) + gán `tariff_type_id` tự động
6. Tạo chỉ số công tơ (meter_readings)

### Logic phân loại biểu giá
| Điều kiện tên hộ | Loại công tơ | Tariff type |
|------------------|--------------|-------------|
| chứa: `phòng`, `ký túc` | RESIDENTIAL | RESIDENTIAL |
| chứa: `kiot`, `quán`, `nhà ăn` | COMMERCIAL | COMMERCIAL |
| khác | COMMERCIAL | COMMERCIAL |

## 5. Các lệnh hữu ích

```bash
php artisan migrate            # Tạo bảng
php artisan migrate:fresh --seed   # Reset & seed đầy đủ từ CSV
php artisan db:seed --class=CsvDataImporter  # Chỉ import CSV sau khi migrate
php artisan tinker             # Kiểm tra nhanh dữ liệu
```

## 6. Kiểm tra nhanh sau khi seed

```bash
php artisan tinker <<'EOF'
echo 'Substations: '.App\Models\Substation::count();
echo "\nOrganization Units: ".App\Models\OrganizationUnit::count();
echo "\nElectric Meters: ".App\Models\ElectricMeter::count();
echo "\nMeter Readings: ".App\Models\MeterReading::count();
EOF
```

## 7. Cấu trúc quan trọng

```text
database/
	├─ seeders/
	│   ├─ DatabaseSeeder.php      # Seeder chính
	│   └─ CsvDataImporter.php     # Import từ data.csv
storage/
	├─ app/
	│   └─ data.csv                # File dữ liệu thực tế
app/Filament/Pages/
	├─ BulkMeterReading.php        # Ghi chỉ số hàng loạt
	└─ GenerateBills.php           # Tạo hóa đơn tự động
```

## 8. Mẹo & Lỗi thường gặp

| Lỗi | Nguyên nhân | Cách xử lý |
|-----|-------------|------------|
| CSV file not found | Chưa copy `data.csv` vào `storage/app` | `cp database/csv/data.csv storage/app/data.csv` |
| Substation not found | Dòng trong CSV thiếu mã trạm | Bổ sung mã hoặc xoá dòng |
| Integrity constraint | Dữ liệu trùng sau khi seed lại | Dùng `migrate:fresh` trước khi seed |

## 9. Tài khoản mặc định

| Email | Password |
|-------|----------|
| admin@example.com | password |

Đổi mật khẩu ngay sau khi đăng nhập lần đầu.

## 10. License

Private internal project (chưa khai báo license công khai).

---
Maintained by internal team.
