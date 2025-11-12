# 🔌 Hệ Thống Quản Lý Điện

Ứng dụng quản lý điện năng được xây dựng bằng **Laravel 11** và **Filament Admin Panel**.

## ️ Công Nghệ

- **Backend**: Laravel 11.46.1
- **Admin Panel**: Filament PHP v4
- **Database**: MariaDB 11.4.2
- **Frontend**: Vite + Tailwind CSS 3.4
- **Container**: Docker

## 📋 Yêu Cầu

- PHP >= 8.4
- Composer
- Node.js >= 18
- Docker & Docker Compose

## 🚀 Hướng Dẫn Chạy Dự Án

### Sử Dụng Docker (Khuyến Nghị)

#### 1. Khởi động Docker containers
```bash
cd docker/environment
docker compose up -d
```

#### 2. Cài đặt dependencies
```bash
docker compose exec cli composer install
```

#### 3. Tạo file environment
```bash
docker compose exec cli cp .env.example .env
docker compose exec cli php artisan key:generate
```

#### 4. Chạy migrations
```bash
docker compose exec cli php artisan migrate
```

#### 5. Import dữ liệu demo
```bash
docker compose exec cli php artisan db:seed
```

**Dữ liệu được tạo:**
- ✅ 1 tài khoản Admin (admin@example.com / password)
- ✅ 3 loại biểu giá (Sinh hoạt, Thương mại, Sản xuất)
- ✅ 3 biểu giá điện với giá hiện tại
- ✅ 11 trạm biến áp
- ✅ 15 tòa nhà
- ✅ 10 đơn vị tổ chức (cấu trúc phân cấp)
- ✅ 210 công tơ điện với loại biểu giá và số kWh bao cấp
- ✅ 420 chỉ số công tơ (2 kỳ/công tơ)
- ✅ 210 hóa đơn tự động với chi tiết

#### 6. Truy cập ứng dụng
- **URL**: http://electric.test (hoặc http://localhost:port theo cấu hình)
- **Admin Panel**: http://electric.test/admin
- **Email**: admin@example.com
- **Password**: password

### Chạy Lệnh Artisan

Mọi lệnh artisan chạy qua Docker:
```bash
docker compose exec cli php artisan [command]
```

Ví dụ:
```bash
# Tạo migration
docker compose exec cli php artisan make:migration create_example_table

# Clear cache
docker compose exec cli php artisan cache:clear

# Tinker
docker compose exec cli php artisan tinker
```

## � Import Dữ Liệu CSV

### Cách 1: Import từ file CSV thô (Dữ liệu thực tế)

Nếu bạn có file CSV thô từ hệ thống cũ (như file `storage/app/data.csv`), sử dụng script để làm sạch và tách dữ liệu:

```bash
# Đặt file CSV gốc vào storage/app/data.csv
# Sau đó chạy script parse
docker compose exec cli php scripts/parse-csv-data.php
```

Script sẽ tự động:
- ✅ Loại bỏ header/footer thừa
- ✅ Lọc dữ liệu không hợp lệ
- ✅ Tách thành 5 file CSV chuẩn trong `database/csv/`
- ✅ Mapping quan hệ giữa các bảng (codes, foreign keys)
- ✅ Xử lý nhiều công tơ trong 1 dòng
- ✅ Tính toán consumption tự động

Sau khi parse xong, chạy seeder:
```bash
docker compose exec cli php artisan db:seed
```

### Cách 2: Sử dụng file CSV chuẩn có sẵn

Hệ thống đã có sẵn file CSV mẫu trong thư mục `database/csv/`:

### Cấu trúc file CSV:

#### `tariff_types.csv`
```csv
name,description,color,icon,status,sort_order
Sinh hoạt,Biểu giá cho hộ gia đình,#3b82f6,heroicon-o-home,ACTIVE,1
Thương mại,Biểu giá cho cơ sở kinh doanh,#22c55e,heroicon-o-building-office,ACTIVE,2
Sản xuất,Biểu giá cho nhà máy sản xuất,#f59e0b,heroicon-o-wrench-screwdriver,ACTIVE,3
```

#### `electricity_tariffs.csv`
```csv
tariff_type_id,price_per_kwh,effective_from,effective_to,tier_level,tier_min_kwh,tier_max_kwh
1,2500,2025-01-01,,1,0,100
2,4169,2025-01-01,,1,,,
3,3500,2025-01-01,,1,,,
```

#### `substations.csv`
```csv
name,code,location,capacity_kva,voltage_level,status
Trạm BA số 1,TBA-001,"Khu A, Tòa nhà chính",1000,22,ACTIVE
```

#### `organization_units.csv`
```csv
name,code,type,parent_code,contact_person,contact_phone,email,address
Công ty TNHH ABC,ORG-001,ORGANIZATION,,,info@abc.com,"123 Đường ABC"
Phòng Kỹ thuật,UNIT-001,UNIT,ORG-001,Nguyễn Văn A,0901234567,kythuat@abc.com,
Anh Nguyễn Văn B,CONSUMER-001,CONSUMER,UNIT-001,Nguyễn Văn B,0912345678,nvb@abc.com,"Phòng 101, Tầng 1"
```

#### `electric_meters.csv`
```csv
meter_number,organization_unit_code,building_code,substation_code,tariff_type_id,subsidized_kwh,location,installation_date,status
EM-2025-001,CONSUMER-001,BLD-001,TBA-001,1,50,"Phòng 101",2025-01-15,ACTIVE
EM-2025-002,CONSUMER-002,BLD-001,TBA-001,2,0,"Văn phòng tầng 2",2025-01-15,ACTIVE
```

### Chạy import:

Seeder tự động đọc và import tất cả file CSV:
```bash
docker compose exec cli php artisan db:seed
```

Hoặc chạy từng seeder cụ thể:
```bash
docker compose exec cli php artisan db:seed --class=TariffTypeSeeder
docker compose exec cli php artisan db:seed --class=ElectricityTariffSeeder
docker compose exec cli php artisan db:seed --class=SubstationSeeder
docker compose exec cli php artisan db:seed --class=OrganizationUnitSeeder
docker compose exec cli php artisan db:seed --class=ElectricMeterSeeder
```

### Lưu ý khi import CSV:

1. **Thứ tự import**: Phải tuân thủ thứ tự dependencies
   - TariffTypes → ElectricityTariffs
   - Substations → Buildings
   - OrganizationUnits (parent trước, child sau)
   - ElectricMeters (sau khi có OrganizationUnits, Buildings, Substations, TariffTypes)

2. **Encoding**: File CSV phải UTF-8 (có BOM) để hỗ trợ tiếng Việt

3. **Foreign Keys**: 
   - `tariff_type_id` phải tồn tại trong bảng `tariff_types`
   - Codes (`parent_code`, `organization_unit_code`, etc.) phải khớp chính xác

4. **Định dạng ngày**: `YYYY-MM-DD` (vd: 2025-01-15)

5. **Enum values**: Phải đúng giá trị định nghĩa trong model
   - `type`: ORGANIZATION, UNIT, CONSUMER
   - `status`: ACTIVE, INACTIVE, MAINTENANCE

### File CSV thô (data.csv):

File `storage/app/data.csv` là bảng tổng hợp thực tế từ hệ thống cũ với cấu trúc:

| Cột | Nội dung | Mapping vào bảng |
|-----|----------|------------------|
| 1 | STT | - |
| 2 | Hộ tiêu thụ điện | organization_units.name |
| 3 | Đơn vị chủ quản | organization_units.parent |
| 4 | Địa chỉ | organization_units.address |
| 5-6 | Điện thoại | organization_units.contact_phone |
| 7 | Đại diện | organization_units.contact_person |
| 8 | Nhà/Tòa nhà | buildings.name |
| 9 | Tầng | - |
| 10 | Số công tơ | electric_meters.meter_number |
| 11 | Loại công tơ | Xác định tariff_type_id |
| 12 | Vị trí đặt công tơ | electric_meters.location |
| 13 | Trạm biến áp | substations.code |
| 14 | Trang | - |
| 15 | Chỉ số mới | meter_readings.current_reading |
| 16 | Chỉ số cũ | meter_readings.previous_reading |
| 17 | Hệ số nhân | meter_readings.multiplier |
| 18 | Tổng tiêu thụ | Tính toán từ (15-16)*17 |
| 19 | Bao cấp | electric_meters.subsidized_kwh |
| 20 | Điện năng phải trả | bill_details.chargeable_kwh |
| 21 | Đơn giá | electricity_tariffs.price_per_kwh |
| 22 | Thành tiền | bill_details.amount |
| 23 | Người thực hiện | - |

**Script tự động xử lý:**
- Loại bỏ 3 dòng header thừa
- Loại bỏ dòng tổng cộng cuối file
- Tách nhiều công tơ trong 1 ô (vd: "9094, 4383" → 2 records)
- Tự động mapping codes giữa các bảng
- Tạo 2 kỳ chỉ số (tháng 5 và tháng 6/2025)

## 🗄️ Database Schema

### Các bảng chính:

| Bảng | Mô tả |
|------|-------|
| `users` | Tài khoản người dùng |
| `tariff_types` | Loại biểu giá (Sinh hoạt, Thương mại, Sản xuất) |
| `electricity_tariffs` | Biểu giá điện theo loại và thời gian |
| `substations` | Trạm biến áp |
| `buildings` | Tòa nhà |
| `organization_units` | Đơn vị tổ chức (phân cấp) |
| `electric_meters` | Công tơ điện |
| `meter_readings` | Chỉ số công tơ |
| `bills` | Hóa đơn |
| `bill_details` | Chi tiết hóa đơn (có subsidized_applied, chargeable_kwh) |

### Tính năng đặc biệt:

#### Subsidized kWh (Điện bao cấp)
- Mỗi công tơ có trường `subsidized_kwh` (số kWh được bao cấp/tháng)
- Khi tính hóa đơn, hệ thống tự động trừ số kWh bao cấp trước khi tính giá
- `bill_details` lưu:
  - `subsidized_applied`: Số kWh bao cấp đã áp dụng
  - `chargeable_kwh`: Số kWh phải tính tiền (sau khi trừ bao cấp)

#### Tariff Type FK-based
- Thay vì dùng enum cố định, hệ thống dùng foreign key đến bảng `tariff_types`
- Linh hoạt thêm/sửa loại biểu giá không cần migration
- Mỗi loại biểu giá có màu sắc (hex) và icon (heroicons) tùy chỉnh

## 🎯 Tính Năng

- **Quản lý công tơ**: Tạo, sửa, xem chi tiết với thông tin loại biểu giá và giá hiện tại
- **Chỉ số công tơ**: Ghi nhận định kỳ, tự động tính tiêu thụ
- **Hóa đơn**: Tạo tự động từ chỉ số, tính toán với bao cấp điện
- **Biểu giá linh hoạt**: Quản lý giá theo loại, thời gian hiệu lực
- **Loại biểu giá**: Tùy chỉnh màu sắc, icon cho từng loại
- **Cấu trúc tổ chức**: Phân cấp đơn vị (Organization → Unit → Consumer)
- **Redirect thông minh**: Sau khi save tự động chuyển về trang detail hoặc list

## � Lệnh Hữu Ích

```bash
# Parse file CSV thô thành các file chuẩn
docker compose exec cli php scripts/parse-csv-data.php

# Reset database và import lại
docker compose exec cli php artisan migrate:fresh --seed

# Chỉ import data, không xóa
docker compose exec cli php artisan db:seed

# Xem logs
docker compose logs -f cli

# Clear cache
docker compose exec cli php artisan optimize:clear

# Tạo user mới
docker compose exec cli php artisan make:filament-user
```

---

**Phiên bản**: 2.0.0 | **Cập nhật**: November 2025

