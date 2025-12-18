# ⚡ Electric Management System

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net)
[![Filament](https://img.shields.io/badge/Filament-4.x-orange.svg)](https://filamentphp.com)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11-green.svg)](https://mariadb.org)

> Hệ thống quản lý điện năng và thu tiền điện toàn diện với Laravel & Filament

## 📋 Mô tả dự án

Hệ thống quản lý điện năng tích hợp đầy đủ các tính năng:
- **Quản lý tổ chức**: Đơn vị, hộ tiêu thụ điện theo cấu trúc cây phân cấp
- **Quản lý công tơ**: Theo dõi thiết bị đo điện, trạm biến áp
- **Đọc chỉ số**: Ghi nhận chỉ số tiêu thụ định kỳ
- **Biểu giá điện**: Quản lý giá điện bậc thang theo quy định EVN
- **Tạo hóa đơn tự động**: Logic tính toán chi tiết, hỗ trợ nhiều loại biểu giá
- **Dashboard**: Thống kê, báo cáo trực quan với 10+ widgets

---

## 🚀 Cách chạy dự án

```bash
# Clone và khởi động
git clone <repository-url>
cd electric/docker/environment
docker compose up -d

# Vào container và setup
docker compose exec cli bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
exit
```

### 3. Truy cập hệ thống
- **URL**: `http://electric.test`
- **Admin**: `admin@electric.test` / `admin123`

---

## 📊 Import dữ liệu

### Bước 1: Tự động qua Seeder (Đã có trong bước setup)
```bash
php artisan migrate:fresh --seed
```

### Bước 2: Qua giao diện web
1. Đăng nhập → Dashboard
2. Widget **"Quick Actions"** → **"Import Tổng hợp"**
3. Upload file: `/database/import-thang-12-2025-merged.csv`

---

## 🛠️ Cấu trúc dự án

```
app/
├── Filament/          # Admin UI với Filament
├── Models/            # Eloquent Models  
├── Services/          # Business Logic
└── Imports/           # Excel Import Classes

database/
├── migrations/        # Database Schema
├── seeders/          # Sample Data
└── import-thang-12-2025-merged.csv  # Dữ liệu mẫu
```

---

## 🔧 Troubleshooting

```bash
# Kiểm tra containers
docker compose ps

# Reset database
docker compose exec cli php artisan migrate:fresh --seed

# Xóa cache
docker compose exec cli php artisan cache:clear
```
