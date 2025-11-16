# 🔋 HỆ THỐNG HÓA ĐƠN ĐIỆN - TOÀN CẢNH

## 📐 KIẾN TRÚC DATABASE

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FLOW DỮ LIỆU                                    │
└─────────────────────────────────────────────────────────────────────────┘

                    ┌──────────────────┐
                    │  Substations     │ (Trạm biến áp)
                    │  - code          │
                    │  - name          │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ OrganizationUnits│ (Đơn vị/Hộ tiêu thụ)
                    │  - name          │
                    │  - type          │ CONSUMER/DEPARTMENT
                    │  - parent_id     │ (cây phân cấp)
                    └────────┬─────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼                             ▼
     ┌─────────────────┐          ┌──────────────────┐
     │ TariffTypes     │          │ ElectricMeters   │
     │  - code         │◄─────────│  - meter_number  │
     │  - name         │          │  - hsn           │ (Hệ số nhân)
     │  - description  │          │  - subsidized_kwh│ (Bao cấp)
     └────────┬────────┘          │  - status        │
              │                   └─────────┬────────┘
              │                             │
              │                             ▼
              │                   ┌──────────────────┐
              │                   │ MeterReadings    │ (Ghi chỉ số)
              │                   │  - reading_date  │
              │                   │  - reading_value │
              │                   │  - reader_name   │
              │                   └──────────────────┘
              │
              ▼
     ┌─────────────────┐
     │ElectricityTariffs│ (Biểu giá)
     │  - price_per_kwh │
     │  - effective_date│
     └──────────────────┘
```

---

## 💰 CẤU TRÚC HÓA ĐƠN

### **1. BILL (Hóa đơn tổng)**

```php
bills
├── id
├── organization_unit_id  ───┐ FK → Hộ tiêu thụ nào
├── billing_month            │ Tháng lập HĐ (YYYY-MM-01)
├── due_date                 │ Hạn thanh toán
├── total_amount             │ Tổng tiền (tổng của tất cả bill_details)
├── payment_status           │ UNPAID | PARTIAL | PAID | OVERDUE
└── timestamps               │
                             │
        ┌────────────────────┘
        │ 1 Bill → nhiều BillDetails
        ▼
```

### **2. BILL_DETAIL (Chi tiết từng công tơ)**

```php
bill_details
├── id
├── bill_id               ───┐ FK → Thuộc hóa đơn nào
├── electric_meter_id    ────┼─> FK → Công tơ nào
│
├── consumption              │ Tiêu thụ thực tế (kWh)
├── subsidized_applied       │ Bao cấp đã áp dụng (kWh)
├── chargeable_kwh           │ = consumption - subsidized_applied
│
├── price_per_kwh            │ Đơn giá (VNĐ/kWh) từ tariff
├── hsn                      │ Hệ số nhân (copy từ meter)
├── amount                   │ = chargeable_kwh × price_per_kwh
└── timestamps
```

---

## ⚙️ QUY TRÌNH TẠO HÓA ĐƠN

### **Bước 1: Thu thập dữ liệu**
```
MeterReading (2 lần ghi gần nhất)
  ├─> reading_date_1: 2025-10-01, value: 1000 kWh
  └─> reading_date_2: 2025-10-31, value: 1150 kWh
```

### **Bước 2: Tính tiêu thụ**
```php
raw_consumption = (1150 - 1000) × meter.hsn
                = 150 × 1.0 = 150 kWh
```

### **Bước 3: Áp dụng bao cấp**
```php
meter.subsidized_kwh = 50 kWh (mỗi tháng)

subsidized_applied = min(150, 50) = 50 kWh
chargeable_kwh = 150 - 50 = 100 kWh  // Chỉ tính tiền 100 kWh
```

### **Bước 4: Lấy biểu giá**
```php
tariff = ElectricityTariff::getActiveTariff(
    meter.tariff_type_id,  // VD: RESIDENTIAL
    billing_month           // 2025-10
)
→ price_per_kwh = 2,500 VNĐ/kWh
```

### **Bước 5: Tính tiền**
```php
amount = chargeable_kwh × price_per_kwh
       = 100 × 2,500
       = 250,000 VNĐ
```

### **Bước 6: Tạo BillDetail**
```php
BillDetail::create([
    'bill_id' => $bill->id,
    'electric_meter_id' => $meter->id,
    'consumption' => 150,          // Tiêu thụ gốc
    'subsidized_applied' => 50,    // Bao cấp
    'chargeable_kwh' => 100,       // Tính tiền
    'price_per_kwh' => 2500,
    'hsn' => 1.0,
    'amount' => 250000
]);
```

### **Bước 7: Cập nhật Bill tổng**
```php
// Nếu 1 hộ có 3 công tơ:
bill.total_amount = bill_detail_1.amount 
                  + bill_detail_2.amount 
                  + bill_detail_3.amount
```

---

## 🏗️ THÀNH PHẦN CẤU TẠO NÊN BILL

### **Dữ liệu Master (Setup 1 lần)**
1. ✅ **Substations** - Trạm biến áp
2. ✅ **OrganizationUnits** - Đơn vị/Hộ tiêu thụ
3. ✅ **TariffTypes** - Loại biểu giá (RESIDENTIAL, COMMERCIAL)
4. ✅ **ElectricityTariffs** - Biểu giá chi tiết

### **Dữ liệu Công tơ (Import từ CSV)**
5. ✅ **ElectricMeters**
   - `meter_number`: Mã công tơ
   - `hsn`: Hệ số nhân
   - `subsidized_kwh`: Bao cấp hàng tháng
   - `tariff_type_id`: Loại biểu giá

### **Dữ liệu Chỉ số (Ghi định kỳ hàng tháng)**
6. ✅ **MeterReadings**
   - Ghi chỉ số đầu tháng
   - Ghi chỉ số cuối tháng

### **Dữ liệu Hóa đơn (Tạo sau khi có đủ chỉ số)**
7. ✅ **Bills** - Header hóa đơn (1 org unit/tháng)
8. ✅ **BillDetails** - Chi tiết từng công tơ (nhiều/bill)

---

## 🔄 FLOW HIỆN TẠI (SAU KHI DỌN DẸP)

```
1. CSV Import (DatabaseSeeder)
   └─> Tạo: Substations, OrganizationUnits, ElectricMeters, MeterReadings

2. Ghi chỉ số định kỳ
   └─> MeterReadingResource → Create MeterReading

3. Tạo hóa đơn THỦ CÔNG
   ├─> BillResource → Create Bill (chọn org unit, tháng, hạn TT)
   └─> BillDetailResource → Create BillDetail cho từng công tơ
       └─> Nhập: consumption, subsidized_applied, price, hsn, amount

4. Quản lý thanh toán
   └─> BillResource → Edit → Cập nhật payment_status
```

---

## ⚠️ VẤN ĐỀ HIỆN TẠI

**Không còn logic tự động tạo hóa đơn!**

Đã xóa:
- ❌ `GenerateBills` page (UI tạo hóa đơn tự động)
- ❌ `BillingService` (logic tính toán tự động)

**Giờ phải làm thủ công:**
1. Tạo Bill → Nhập org_unit, billing_month, due_date
2. Tạo từng BillDetail → Tự tính consumption, price, amount

---

## 💡 KHUYẾN NGHỊ

✅ **ĐÃ TRIỂN KHAI: Option A - Logic tự động**

### **Đã tạo:**
1. ✅ `app/Services/BillingService.php`
   - `createBillForMeter()` - Tạo hóa đơn cho 1 công tơ
   - `createBillForOrganizationUnit()` - Tạo hóa đơn cho 1 đơn vị
   - `createBillsForMeters()` - Tạo hóa đơn cho nhiều công tơ

2. ✅ Header action trong `/admin/bills`
   - Nút "Tạo hóa đơn tự động" 
   - Form chọn: Tháng, Hạn TT, Đơn vị hoặc Công tơ
   - Tự động tính toán và tạo Bill + BillDetails

### **Cách sử dụng:**

#### **Option 1: Tạo cho công tơ cụ thể**
```
1. Vào /admin/bills
2. Click "Tạo hóa đơn tự động"
3. Chọn:
   - Tháng lập hóa đơn: 11/2025
   - Hạn thanh toán: 15/12/2025
   - Công tơ cụ thể: Chọn 1 hoặc nhiều
4. Click "Generate"
```

#### **Option 2: Tạo cho toàn bộ đơn vị**
```
1. Vào /admin/bills
2. Click "Tạo hóa đơn tự động"
3. Chọn:
   - Tháng lập hóa đơn: 11/2025
   - Hạn thanh toán: 15/12/2025
   - Đơn vị: Chọn 1 đơn vị
4. Click "Generate"
→ Tạo hóa đơn cho tất cả công tơ của đơn vị đó
```

#### **Option 3: Tạo cho tất cả**
```
1. Vào /admin/bills
2. Click "Tạo hóa đơn tự động"
3. Chọn:
   - Tháng lập hóa đơn: 11/2025
   - Hạn thanh toán: 15/12/2025
   - Bỏ trống cả Đơn vị và Công tơ
4. Click "Generate"
→ Tạo hóa đơn cho TẤT CẢ đơn vị CONSUMER có công tơ ACTIVE
```

### **Xử lý lỗi tự động:**
- Không đủ chỉ số → Bỏ qua, báo lỗi
- Tiêu thụ âm/0 → Bỏ qua, báo lỗi
- Không có tariff → Bỏ qua, báo lỗi
- Các công tơ khác vẫn tạo bình thường

---

## 📊 QUAN HỆ GIỮA CÁC BẢNG

```
substations (1) ──── (n) organization_units
                            │
                            ├──── (1) parent_id (self-reference)
                            │
                            └──── (n) electric_meters
                                       │
                                       ├──── (n) meter_readings
                                       │
                                       └──── (n) bill_details
                                                  │
                                                  └──── (1) bills

tariff_types (1) ──── (n) electric_meters
                 │
                 └──── (n) electricity_tariffs
```

---

## 🎯 ĐIỂM QUAN TRỌNG

1. **1 Bill = 1 Org Unit + 1 Tháng**
   - Không tạo trùng bill cho cùng org unit trong cùng tháng

2. **BillDetail = Bill + ElectricMeter**
   - 1 hộ có 3 công tơ → 1 Bill có 3 BillDetails

3. **Bao cấp (subsidized_kwh)**
   - Chỉ trừ vào consumption khi tính tiền
   - Lưu lại trong `subsidized_applied` để theo dõi

4. **HSN (Hệ số nhân)**
   - Nhân vào consumption ngay từ đầu
   - Copy từ meter sang bill_detail để lưu vết

5. **TariffType vs ElectricityTariff**
   - TariffType: Loại (RESIDENTIAL, COMMERCIAL)
   - ElectricityTariff: Giá cụ thể theo thời gian
   - 1 TariffType có nhiều ElectricityTariff (theo ngày hiệu lực)

---

**Ngày tạo:** 2025-11-13  
**Trạng thái:** Đã dọn dẹp code, chờ quyết định tạo lại logic tự động
