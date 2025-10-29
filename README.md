# 🔌 Hệ Thống Quản Lý Điện - Electricity Management System

Một ứng dụng web quản lý điện năng toàn diện được xây dựng bằng **Laravel 11** và **Filament Admin Panel**. Hệ thống cung cấp giải pháp quản lý công tơ điện, đơn vị tổ chức, hóa đơn, biểu giá, và báo cáo tiêu thụ điện.

## 🎯 Tính Năng Chính

### 📊 Quản Lý Dữ Liệu Cơ Bản
- **Danh mục**: Quản lý tòa nhà, đơn vị tổ chức, trạm biến áp, loại biểu giá
- **Vận hành**: Quản lý công tơ điện, chỉ số công tơ
- **Hóa đơn**: Tạo và quản lý hóa đơn, chi tiết hóa đơn
- **Biểu giá**: Quản lý biểu giá điện, loại khách hàng

### 🏢 Quản Lý Tòa Nhà (Buildings)
- Tạo, sửa, xóa thông tin tòa nhà
- Liên kết với trạm biến áp
- Quản lý số tầng, mã tòa nhà
- Xem danh sách công tơ điện trong tòa nhà

### 🏛️ Quản Lý Đơn Vị Tổ Chức (Organization Units)
- Hỗ trợ cấu trúc phân cấp (cha-con)
- Quản lý loại đơn vị: ORGANIZATION, UNIT, CONSUMER
- Liên kết công tơ điện và hóa đơn
- Xem danh sách hóa đơn của mỗi đơn vị

### ⚡ Quản Lý Công Tơ Điện (Electric Meters)
- Tạo và quản lý công tơ
- Liên kết với đơn vị tổ chức, tòa nhà, trạm biến áp
- Phân loại: RESIDENTIAL, COMMERCIAL, INDUSTRIAL
- Xem chi tiết và lịch sử chỉ số

### 📈 Quản Lý Chỉ Số Công Tơ (Meter Readings)
- Ghi chỉ số định kỳ (hàng tháng)
- Tính toán tiêu thụ điện
- Lịch sử đầy đủ của từng công tơ

### 💰 Quản Lý Hóa Đơn (Bills)
- Tạo hóa đơn tự động từ chỉ số công tơ
- Quản lý chi tiết hóa đơn (consumption, price, amount)
- Trạng thái: PENDING, PAID, CANCELLED
- Xem chi tiết từng dòng hóa đơn

### 📊 Quản Lý Biểu Giá (Electricity Tariffs)
- Quản lý giá điện theo loại khách hàng
- Ngày hiệu lực
- Lịch sử thay đổi giá

## 🛠️ Công Nghệ Sử Dụng

| Thành Phần | Công Nghệ |
|-----------|----------|
| Backend | Laravel 11.46.1 |
| Admin Panel | Filament PHP |
| Database | MariaDB 11.4.2 |
| Frontend Build | Vite |
| CSS Framework | Tailwind CSS 3.4 |
| Containerization | Docker |

## 📋 Yêu Cầu Hệ Thống

- PHP >= 8.2
- Composer
- Node.js >= 18
- Docker & Docker Compose (optional)
- MariaDB >= 10.6

## 🚀 Cài Đặt & Thiết Lập

### 1. Clone Repository
```bash
git clone <repository-url>
cd electric
```

### 2. Cài Đặt Dependencies
```bash
composer install
npm install
```

### 3. Tạo File Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Cấu Hình Database
Chỉnh sửa file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electric_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Chạy Migrations
```bash
php artisan migrate
```

### 6. Seed Dữ Liệu Mẫu (Một Lệnh Duy Nhất)
```bash
php artisan db:seed
```

**Lệnh này sẽ tạo tất cả dữ liệu mẫu:**
- ✅ 1 tài khoản Admin (admin@example.com / password)
- ✅ 3 loại biểu giá (Dân cư, Thương mại, Công nghiệp)
- ✅ 3 biểu giá điện
- ✅ 11 trạm biến áp
- ✅ 15 tòa nhà
- ✅ 10 đơn vị tổ chức
- ✅ 9 công tơ điện
- ✅ 18 chỉ số công tơ
- ✅ 12 hóa đơn với chi tiết

### 7. Xây Dựng Assets
```bash
npm run build
```

### 8. Chạy Development Server
```bash
php artisan serve
```

Truy cập: http://localhost:8000

## 🐳 Chạy Bằng Docker

### Sử Dụng Docker Compose
```bash
cd docker/environment
docker-compose up -d
```

### Chạy Lệnh Artisan
```bash
docker-compose exec cli php artisan migrate
docker-compose exec cli php artisan db:seed
```

## 📂 Cấu Trúc Dự Án

```
electric/
├── app/
│   ├── Filament/         # Admin Panel Resources (9 resources)
│   │   └── Resources/
│   │       ├── Buildings/
│   │       ├── Bills/
│   │       ├── OrganizationUnits/
│   │       ├── ElectricMeters/
│   │       ├── MeterReadings/
│   │       ├── ElectricityTariffs/
│   │       ├── TariffTypes/
│   │       ├── Substations/
│   │       └── Users/
│   ├── Models/          # Eloquent Models (9 models)
│   ├── Services/        # Business Logic
│   ├── Helpers/         # Helper Functions
│   └── Providers/       # Service Providers
├── database/
│   ├── migrations/      # Database Migrations (35+ indexes)
│   ├── seeders/         # DatabaseSeeder (tất cả trong 1 file)
│   └── factories/       # Model Factories
├── resources/
│   ├── css/            # Tailwind CSS (Professional design)
│   └── js/             # Frontend JS
├── docker/             # Docker Configuration
├── config/             # Configuration Files
└── routes/             # Route Definitions
```

## 🗄️ Cấu Trúc Database

### Bảng Chính

| Bảng | Mô Tả | Relationships |
|------|-------|---------------|
| `users` | Tài khoản người dùng | - |
| `tariff_types` | Loại biểu giá | Has many ElectricityTariffs |
| `electricity_tariffs` | Biểu giá điện | Belongs to TariffType |
| `substations` | Trạm biến áp | Has many Buildings, ElectricMeters |
| `buildings` | Tòa nhà | Belongs to Substation, Has many ElectricMeters |
| `organization_units` | Đơn vị tổ chức | Hierarchical, Has many ElectricMeters, Bills |
| `electric_meters` | Công tơ điện | Belongs to OrganizationUnit/Building/Substation, Has many Readings/BillDetails |
| `meter_readings` | Chỉ số công tơ | Belongs to ElectricMeter |
| `bills` | Hóa đơn | Belongs to OrganizationUnit, Has many BillDetails |
| `bill_details` | Chi tiết hóa đơn | Belongs to Bill, ElectricMeter |

### Indexes (35+)
- Primary keys trên tất cả bảng
- Foreign keys tối ưu hóa
- Indexes trên các trường tìm kiếm thường xuyên
- Composite indexes cho queries phức tạp

Chi tiết đầy đủ xem trong `DATABASE_DESIGN.md`

## 👤 Đăng Nhập Admin Panel

**URL**: http://localhost:8000/admin

**Tài khoản mặc định**:
- Email: `admin@example.com`
- Password: `password`

## 🎨 Thiết Kế UI/UX

- **Color Scheme**: Blue primary, Slate background (light & dark mode)
- **Border Radius**: rounded-lg (8px) - Professional yet modern
- **Components**: Border-based styling, minimal shadows
- **Responsiveness**: Fully responsive, mobile-friendly
- **Typography**: Inter font family
- **Max Width**: 8xl (90rem)

## 📝 Navigation Groups

| Nhóm | Mục | Tổng |
|-----|------|------|
| **Danh mục** | Đơn vị tổ chức, Tòa nhà, Trạm điện, Loại biểu giá | 4 items |
| **Vận hành** | Công tơ điện, Chỉ số công tơ | 2 items |
| **Hóa đơn** | Hóa đơn, Chi tiết hóa đơn | 2 items |
| **Biểu giá** | Biểu giá điện, Loại biểu giá | 2 items |

## 🔒 Bảo Mật

- CSRF protection
- SQL Injection prevention (Eloquent ORM)
- XSS protection (Blade template escaping)
- Password hashing (Bcrypt)
- Input validation & sanitization
- Authorization checks

## 📚 Tài Liệu

- **DATABASE_DESIGN.md** - Schema, relationships, migrations chi tiết
- **README.md** - Tài liệu này
- Inline code documentation & comments

## 🤝 Contributing

Contributions welcome! Vui lòng:
1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Mở Pull Request

## 📄 License

MIT License - xem file LICENSE

---

**Phiên bản**: 1.0.0 | **Cập nhật**: October 2025

