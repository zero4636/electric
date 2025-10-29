# 📊 Database Design & Schema

Tài liệu chi tiết về cấu trúc database, relationships, migrations, và indexes của hệ thống quản lý điện.

## 📑 Mục Lục

1. [Tổng Quan](#tổng-quan)
2. [Cấu Trúc Bảng](#cấu-trúc-bảng)
3. [Relationships](#relationships)
4. [Migrations](#migrations)
5. [Indexes & Performance](#indexes--performance)
6. [Data Integrity](#data-integrity)

## Tổng Quan

### Thống Kê Database

| Thông Số | Giá Trị |
|---------|--------|
| Tổng số bảng | 10 |
| Tổng số migrations | 15+ |
| Indexes | 35+ |
| Models | 9 |
| Factories | 9 |

### Entity Relationship Diagram

```
┌──────────────────────────┐
│    TARIFF_TYPES (3)      │
│ - RESIDENTIAL            │
│ - COMMERCIAL             │
│ - INDUSTRIAL             │
└──────────────┬───────────┘
               │ Has Many
               ▼
┌──────────────────────────────┐
│ ELECTRICITY_TARIFFS (3+)     │
│ - price_per_kwh              │
│ - effective_from/to          │
└──────────────────────────────┘

┌──────────────────────────┐
│   SUBSTATIONS (11)       │
│ - code, name, location   │
└──────────────┬───────────┘
               │ Has Many
       ┌───────┴──────────┐
       ▼                  ▼
┌────────────────┐  ┌─────────────────┐
│ BUILDINGS (15) │  │ ELECTRIC_METERS │
└────────┬───────┘  └────────┬────────┘
         │ Has Many          │ Has Many
         └────────┬──────────┘
                  ▼
         ┌──────────────────────┐
         │ ORGANIZATION_UNITS   │
         │ (Hierarchical: 10)   │
         └──────────┬───────────┘
                    │ Has Many
         ┌──────────┼─────────────┐
         ▼          ▼             ▼
    METERS   BILLS (12)    METER_READINGS (18)
                  │ Has Many
                  ▼
          ┌───────────────────┐
          │ BILL_DETAILS (40+)│
          └───────────────────┘
```

## Cấu Trúc Bảng

### 1. USERS
Tài khoản người dùng của hệ thống.
- `id` (PK)
- `email` (UNIQUE)
- `password` (hashed)
- `created_at`, `updated_at`

### 2. TARIFF_TYPES
Phân loại loại khách hàng/biểu giá.
- `id` (PK)
- `code` (UNIQUE): RESIDENTIAL, COMMERCIAL, INDUSTRIAL
- `name`: Tên loại
- `description`: Mô tả
- `status`: ACTIVE/INACTIVE

### 3. ELECTRICITY_TARIFFS
Biểu giá điện theo loại và thời gian.
- `id` (PK)
- `tariff_type_id` (FK)
- `price_per_kwh`: Giá/kWh
- `effective_from`, `effective_to`: Khoảng thời gian

**Ví dụ**:
- Residential: 2,500 VND/kWh (từ 2024-01-01)
- Commercial: 4,169 VND/kWh (từ 2024-01-01)
- Industrial: 3,500 VND/kWh (từ 2024-01-01)

### 4. SUBSTATIONS
Trạm biến áp cung cấp điện.
- `id` (PK)
- `code` (UNIQUE): B1, ĐLK, KTX, TVĐT, BK1, BK2, BK3B, SVĐ, THCK, VVL, ĐCĐT
- `name`: Tên trạm
- `location`: Địa chỉ
- `status`: ACTIVE/INACTIVE

### 5. BUILDINGS
Tòa nhà được cung cấp điện.
- `id` (PK)
- `code` (UNIQUE)
- `name`: Tên tòa nhà
- `substation_id` (FK): Trạm cung cấp
- `total_floors`: Số tầng
- `status`: ACTIVE/INACTIVE

**Ví dụ**: 15 tòa nhà (D5, A17, B1, D3, D9, C10, C8, SVĐ, A15, B7, D6, D2A, B4, TC, 10TQB)

### 6. ORGANIZATION_UNITS
Đơn vị tổ chức/khách hàng (hỗ trợ cấu trúc phân cấp).
- `id` (PK)
- `parent_id` (FK): Tham chiếu parent (self-reference)
- `code` (UNIQUE)
- `name`: Tên đơn vị
- `type`: ORGANIZATION, UNIT, CONSUMER
- `email`, `address`, `contact_name`, `contact_phone`
- `status`: ACTIVE/INACTIVE

**Cấu trúc dữ liệu**:
- 1 tập đoàn cấp cao
- 5 công ty độc lập
- 4 khách hàng cá nhân

### 7. ELECTRIC_METERS
Công tơ điện (thiết bị đo tiêu thụ).
- `id` (PK)
- `meter_number` (UNIQUE): 3564, 5089, 1738...
- `organization_unit_id` (FK)
- `building_id` (FK)
- `substation_id` (FK)
- `meter_type`: RESIDENTIAL, COMMERCIAL, INDUSTRIAL
- `hsn`: Hệ số sử dụng (default = 1)
- `installation_location`: Vị trí lắp đặt
- `status`: ACTIVE/INACTIVE

**Ví dụ**: 9 công tơ phân bố trên các tòa nhà

### 8. METER_READINGS
Chỉ số công tơ ghi nhận định kỳ.
- `id` (PK)
- `electric_meter_id` (FK)
- `reading_date` (DATE)
- `reading_value`: Chỉ số (kWh)
- `reader_name`: Tên người ghi
- `notes`: Ghi chú

**Business Logic**:
- Tiêu thụ = Chỉ số hiện tại - Chỉ số kỳ trước
- Dữ liệu mẫu: 18 chỉ số (2 per công tơ, tháng 9 & 10/2025)

### 9. BILLS
Hóa đơn điện.
- `id` (PK)
- `organization_unit_id` (FK)
- `billing_date` (DATE)
- `total_amount`: Tổng tiền
- `status`: PENDING, PAID, CANCELLED

**Ví dụ dữ liệu**: 12 hóa đơn (2 per đơn vị)

### 10. BILL_DETAILS
Chi tiết hóa đơn (một dòng per công tơ).
- `id` (PK)
- `bill_id` (FK)
- `electric_meter_id` (FK)
- `consumption`: Tiêu thụ (kWh)
- `price_per_kwh`: Giá áp dụng
- `hsn`: Hệ số sử dụng
- `amount`: Tiền = consumption × price_per_kwh × hsn

**Tính toán**: 40+ chi tiết (3-4 per hóa đơn)

## Relationships

### One-to-Many
```
TariffType (1) → (∞) ElectricityTariff
Substation (1) → (∞) Building
Substation (1) → (∞) ElectricMeter
Building (1) → (∞) ElectricMeter
OrganizationUnit (1) → (∞) ElectricMeter
OrganizationUnit (1) → (∞) Bill
ElectricMeter (1) → (∞) MeterReading
ElectricMeter (1) → (∞) BillDetail
Bill (1) → (∞) BillDetail
```

### Self-Referencing (Hierarchical)
```
OrganizationUnit (parent) → (children) OrganizationUnit
```

### Belongs-To
```
ElectricityTariff → TariffType
Building → Substation
ElectricMeter → OrganizationUnit, Building, Substation
MeterReading → ElectricMeter
Bill → OrganizationUnit
BillDetail → Bill, ElectricMeter
```

## Migrations

### Files
```
2025_01_01_000000_create_users_table.php
2025_01_01_000001_create_cache_table.php
2025_01_01_000002_create_tariff_types_table.php
2025_01_01_000003_create_electricity_tariffs_table.php
2025_01_01_000004_create_substations_table.php
2025_01_01_000005_create_buildings_table.php
2025_01_01_000006_create_organization_units_table.php
2025_01_01_000007_create_electric_meters_table.php
2025_01_01_000008_create_meter_readings_table.php
2025_01_01_000009_create_bills_table.php
2025_01_01_000010_create_bill_details_table.php
2025_10_29_142808_add_tariff_type_id_to_electricity_tariffs_table.php
```

### Commands
```bash
# Fresh migrations
php artisan migrate:fresh

# With seed
php artisan migrate:fresh --seed

# Status
php artisan migrate:status

# Rollback
php artisan migrate:rollback
```

## Indexes & Performance

### Total Indexes: 35+

#### Primary Keys (10)
- Mỗi bảng có `id BIGINT UNSIGNED PRIMARY KEY`

#### Foreign Keys (20+)
Tất cả FK được indexed tự động

#### Search Indexes (15+)
```
tariff_types: code, status, sort_order
electricity_tariffs: tariff_type, effective_from
substations: code, status
buildings: code, status, substation_id
organization_units: code, parent_id, type, status, email
electric_meters: meter_number, meter_type, status
meter_readings: reading_date, electric_meter_id
bills: billing_date, status, organization_unit_id
```

#### Unique Constraints (10+)
```
users: email
tariff_types: code
substations: code
buildings: code
electric_meters: meter_number
organization_units: code
meter_readings: (electric_meter_id, reading_date)
bills: (organization_unit_id, billing_date)
bill_details: (bill_id, electric_meter_id)
```

### Performance Tips

1. **Eager Loading**
   ```php
   Bill::with('billDetails.electricMeter', 'organizationUnit')->get()
   ```

2. **Pagination**
   ```php
   Organization::paginate(20)
   ```

3. **Select Specific Columns**
   ```php
   ElectricMeter::select('id', 'meter_number', 'meter_type')->get()
   ```

4. **Monitor Query Logs**
   - Enable query logging to detect N+1 problems
   - Use Laravel Debugbar or Clockwork

## Data Integrity

### Foreign Key Constraints

| Relationship | Action |
|-----------|--------|
| Building → Substation | RESTRICT |
| ElectricMeter → Substation | RESTRICT |
| ElectricMeter → Organization | CASCADE |
| MeterReading → ElectricMeter | CASCADE |
| Bill → Organization | CASCADE |
| BillDetail → Bill | CASCADE |
| BillDetail → ElectricMeter | RESTRICT |

### Validation Rules

**OrganizationUnit**:
```
'code' => 'required|unique:organization_units|max:50'
'name' => 'required|max:255'
'type' => 'required|in:ORGANIZATION,UNIT,CONSUMER'
'status' => 'in:ACTIVE,INACTIVE'
```

**ElectricMeter**:
```
'meter_number' => 'required|unique:electric_meters|max:100'
'organization_unit_id' => 'required|exists:organization_units,id'
'meter_type' => 'required|in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL'
'hsn' => 'required|numeric|min:0.1|max:10'
```

**Bill**:
```
'organization_unit_id' => 'required|exists:organization_units,id'
'billing_date' => 'required|date'
'status' => 'in:PENDING,PAID,CANCELLED'
```

## Sample Data After Seeding

```
Users: 1 (admin@example.com)
TariffTypes: 3 (RESIDENTIAL, COMMERCIAL, INDUSTRIAL)
ElectricityTariffs: 3
Substations: 11
Buildings: 15
OrganizationUnits: 10
ElectricMeters: 9
MeterReadings: 18
Bills: 12 (6 PENDING, 6 PAID)
BillDetails: 40+ (3-4 per bill)
```

---

**Version**: 1.0.0 | **Updated**: October 2025
