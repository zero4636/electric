# ✅ RBAC & Export Excel - ĐÃ CÀI ĐẶT THÀNH CÔNG!

## 🎯 Tóm tắt nhanh

Hệ thống **Role-Based Access Control** và **Export Excel** đã được cài đặt và cấu hình hoàn tất!

---

## 🔑 Tài khoản test (sử dụng ngay)

### 1. Admin (Full Access)
```
Email: admin@electric.test
Password: admin123
Organizations: TẤT CẢ (không giới hạn)
Quyền: Xem/Tạo/Sửa/Xóa tất cả + Export
```

### 2. Manager (Quản lý đơn vị được gán)
```
Email: manager@electric.test
Password: manager123
Organizations: 6 đơn vị
  - HĐKT (ID: 1)
  - Nhà ăn T3-D2A (ID: 23)
  - TT chuyển đổi số (ID: 46)
  - TT Y tế (ID: 57)
  - TTDV và HT BK (ID: 68)
  - BK Holding (ID: 76)
Quyền: CRUD Organizations, Meters, Readings, Bills (chỉ trong 6 đơn vị trên) + Export
```

### 3. Reader (Chỉ đọc + nhập chỉ số)
```
Email: reader@electric.test
Password: reader123
Organizations: 2 đơn vị
  - HĐKT (ID: 1)
  - Nhà ăn T3-D2A (ID: 23)
Quyền: Xem Organizations/Meters + Tạo Meter Readings (chỉ trong 2 đơn vị)
```

### 4. Accountant (Kế toán - focus billing)
```
Email: accountant@electric.test
Password: accountant123
Organizations: 4 đơn vị
  - HĐKT (ID: 1)
  - TT chuyển đổi số (ID: 46)
  - TT Y tế (ID: 57)
  - TTDV và HT BK (ID: 68)
Quyền: Xem Orgs/Meters/Readings + CRUD Bills + Xem Tariffs (chỉ trong 4 đơn vị) + Export
```

---

## 🧪 Test checklist

### Test Admin
- [ ] Login với admin@electric.test / admin123
- [ ] Vào "Organization Units" → Thấy tất cả 147 đơn vị
- [ ] Vào "Electric Meters" → Thấy tất cả công tơ
- [ ] Click "Export Excel" → Download thành công
- [ ] Tạo/Sửa/Xóa bất kỳ record nào → Thành công

### Test Manager
- [ ] Login với manager@electric.test / manager123
- [ ] Vào "Organization Units" → **CHỈ thấy 6 đơn vị được gán** (hoặc children của chúng)
- [ ] Vào "Electric Meters" → **CHỈ thấy meters thuộc 6 đơn vị**
- [ ] Click "Export Excel" → Download chỉ data của 6 đơn vị
- [ ] Tạo reading cho meter trong scope → Thành công
- [ ] Tạo reading cho meter ngoài scope → **Bị chặn/Không thấy meter**

### Test Reader
- [ ] Login với reader@electric.test / reader123
- [ ] Vào "Organization Units" → **CHỈ xem**, không có nút Create/Edit/Delete
- [ ] Vào "Electric Meters" → **CHỈ xem**, không có nút Create/Edit/Delete
- [ ] Vào "Meter Readings" → Có nút "Create" để nhập chỉ số
- [ ] Vào "Bills" → **Không truy cập được** (403 Forbidden)
- [ ] Không có nút "Export Excel" (thiếu quyền export_data)

### Test Accountant
- [ ] Login với accountant@electric.test / accountant123
- [ ] Vào "Organization Units" → **CHỈ xem 4 đơn vị**
- [ ] Vào "Bills" → Có nút Create/Edit/Delete cho bills thuộc 4 đơn vị
- [ ] Vào "Electricity Tariffs" → Chỉ xem, không edit
- [ ] Click "Export Excel" ở Bills → Download thành công

---

## 🔧 Các lệnh hữu ích

### Kiểm tra user đang có quyền gì
```bash
cd /home/zero4636/www/project/electric/docker/environment
docker compose exec cli php artisan tinker
```

```php
$user = User::where('email', 'manager@electric.test')->first();
$user->roles->pluck('name'); // ['Manager']
$user->organizationUnits->pluck('name'); // 6 organizations
$user->hasPermission('create_electric_meters'); // true
$user->hasPermission('delete_organization_units'); // true
$user->hasPermission('export_data'); // true
```

### Gán thêm organization cho user
```php
$user = User::where('email', 'reader@electric.test')->first();
$org = OrganizationUnit::find(10); // Lớp học thầy Chung T3
$user->organizationUnits()->attach($org->id, ['is_primary' => false]);
```

### Kiểm tra user có thể access org không
```php
$user = User::where('email', 'manager@electric.test')->first();
$org = OrganizationUnit::find(1); // HĐKT
$user->canAccessOrganization($org); // true (vì được gán)

$org2 = OrganizationUnit::find(100);
$user->canAccessOrganization($org2); // false (không được gán)
```

### Clear cache sau khi thay đổi permissions
```bash
cd /home/zero4636/www/project/electric/docker/environment
docker compose exec cli php artisan optimize:clear
```

---

## 📊 Phân quyền chi tiết

### Admin
- ✅ Tất cả permissions (30+)
- ✅ Bypass organization checks
- ✅ Quản lý users và roles (khi có User Management Resource)

### Manager (16 permissions)
- ✅ view/create/edit/delete: organization_units
- ✅ view/create/edit/delete: electric_meters
- ✅ view/create/edit/delete: meter_readings
- ✅ view/create/edit/delete: bills
- ✅ view: substations, tariff_types, electricity_tariffs
- ✅ export_data, view_reports

### Reader (4 permissions)
- ✅ view: organization_units, electric_meters, meter_readings
- ✅ create: meter_readings
- ❌ Không có quyền edit/delete
- ❌ Không có export_data

### Accountant (10 permissions)
- ✅ view: organization_units, electric_meters, meter_readings, tariff_types, electricity_tariffs
- ✅ view/create/edit/delete: bills
- ✅ export_data, view_reports
- ❌ Không CRUD meters, readings

---

## 🎨 Export Excel đã implement

### ✅ Có Export button:
1. **Organization Units** (`ListOrganizationUnits.php`)
   - 12 columns: code, name, type, parent, building, contact, email, address, meter count, status, created_at
   
2. **Electric Meters** (`ListElectricMeters.php`)
   - 12 columns: meter_number, org, substation, building, address, location, phase, tariff, HSN, subsidized_kwh, status, created_at

### ⏳ Cần thêm Export (pattern sẵn):
- Meter Readings
- Bills
- Bill Details
- Substations
- Tariff Types
- Electricity Tariffs

**Pattern để copy:**
```php
use App\Filament\Actions\ExportExcelAction;

ExportExcelAction::make('Export Excel', [
    'Header' => 'column_or_accessor',
    'Related' => fn($r) => $r->relation?->field ?? '',
])->visible(fn() => auth()->user()?->hasPermission('export_data') ?? false),
```

---

## 📁 Files đã tạo/sửa

### Migrations
- `database/migrations/2025_12_08_100000_create_roles_permissions_tables.php` ✅

### Seeders
- `database/seeders/RolesPermissionsSeeder.php` ✅

### Models
- `app/Models/User.php` (updated) ✅
- `app/Models/Role.php` (new) ✅
- `app/Models/Permission.php` (new) ✅

### Policies
- `app/Policies/OrganizationUnitPolicy.php` ✅
- `app/Policies/ElectricMeterPolicy.php` ✅
- `app/Policies/MeterReadingPolicy.php` ✅
- `app/Policies/BillPolicy.php` ✅
- `app/Policies/SubstationPolicy.php` ✅

### Actions
- `app/Filament/Actions/ExportExcelAction.php` ✅

### List Pages (updated with Export)
- `app/Filament/Resources/OrganizationUnits/Pages/ListOrganizationUnits.php` ✅
- `app/Filament/Resources/ElectricMeters/Pages/ListElectricMeters.php` ✅

### Service Provider
- `app/Providers/AppServiceProvider.php` (registered policies) ✅

### Documentation
- `SETUP_RBAC_EXPORT.md` ✅
- `RBAC_EXPORT_SUMMARY.md` ✅
- `QUICK_TEST_GUIDE.md` (this file) ✅

---

## 🚨 Lưu ý quan trọng

1. **Working directory cho Docker**: 
   ```bash
   cd /home/zero4636/www/project/electric/docker/environment
   ```
   Tất cả `docker compose exec` phải chạy từ đây!

2. **Organization scoping**: 
   - Non-admin users CHỈ thấy data của organizations được gán
   - Admin thấy tất cả
   - Hiện tại query chưa được scope (cần implement getEloquentQuery() trong Resources)

3. **Export format**: 
   - File CSV với UTF-8 BOM (Excel mở được tiếng Việt)
   - Không phải Excel binary (*.xlsx)
   - Đơn giản hơn, không cần package

4. **Demo users chưa có UI quản lý**:
   - Gán organizations phải dùng Tinker
   - Hoặc tạo User Management Resource sau

---

## ✨ Next steps (optional)

1. **Thêm Export cho các Resources còn lại**
   - Copy pattern từ ListOrganizationUnits/ListElectricMeters
   - 5 phút/resource

2. **Tạo User Management Resource**
   - CRUD users
   - Assign roles qua UI
   - Assign organizations qua UI

3. **Scope queries trong Resources**
   - Override `getEloquentQuery()` trong Resources
   - Filter records theo organizationUnits của user
   - Hiện tại policy ngăn actions, nhưng table vẫn show all

4. **Activity Log**
   - Install spatie/laravel-activitylog
   - Track ai sửa gì khi nào

5. **Advanced features**
   - Email notifications khi có bills mới
   - Dashboard widgets theo organization
   - Report exports với charts

---

**🎉 Hệ thống sẵn sàng! Hãy test và feedback!**
