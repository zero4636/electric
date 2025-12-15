<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phiếu điện</title>
    <style>
        @page {
            margin: 10mm 15mm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
        }
        
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .header-left h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
        }
        
        .header-left h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
        }
        
        .header-right .title {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }
        
        .header-right .date {
            font-size: 11pt;
            margin: 3px 0 0 0;
        }
        
        .bill-number {
            text-align: right;
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .info {
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .info-row {
            margin: 2px 0;
        }
        
        .info-row-split {
            display: table;
            width: 100%;
        }
        
        .info-left {
            display: table-cell;
            width: 65%;
        }
        
        .info-right {
            display: table-cell;
            width: 35%;
            text-align: right;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th {
            padding: 3px 2px;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
        }
        
        td {
            padding: 2px 2px;
            font-size: 10pt;
        }
        
        .center { text-align: center; }
        .right { text-align: right; }
        
        .total-row {
            font-weight: bold;
        }
        
        .total-amount-row {
            margin: 5px 0;
        }
        
        .amount-left {
            display: inline-block;
            width: 65%;
        }
        
        .amount-right {
            display: inline-block;
            width: 34%;
            text-align: right;
            vertical-align: top;
        }
        
        .footer {
            margin-top: 12px;
        }
        
        .footer-row {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        
        .footer-left {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .footer-date {
            text-align: right;
            margin-bottom: 8px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
        }
        
        .signature-name {
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="header-left">
            <h1>ĐẠI HỌC BÁCH KHOA HÀ NỘI</h1>
            <h2>PHÒNG CƠ SỞ VẬT CHẤT</h2>
        </div>
        <div class="header-right">
            <div class="title">PHIẾU THÔNG BÁO ĐIỆN TIÊU THỤ</div>
            <div class="date">Tháng {{ $month }} năm {{ $year }}</div>
        </div>
    </div>
    
    <div class="bill-number">
        <strong>Số phiếu: {{ $billNumber }}</strong>
    </div>
    
    <div class="info">
        <div class="info-row">
            <strong>Kính gửi: {{ $organization->name }}</strong>
        </div>
        <div class="info-row-split">
            <div class="info-left">Địa chỉ hộ tiêu thụ: {{ $organization->building ?? $organization->address }}.</div>
            <div class="info-right">Điện thoại: {{ $organization->contact_phone ?? '' }}</div>
        </div>
        <div class="info-row-split">
            <div class="info-left">Đại diện: {{ $organization->contact_person ?? $organization->name }}.</div>
            <div class="info-right">Điện thoại: {{ $organization->contact_phone ?? '' }}</div>
        </div>
    </div>
    
    <table>
        <tbody>
            <tr>
                <th style="width: 4%;">STT</th>
                <th style="width: 9%;">Chỉ số mới</th>
                <th style="width: 9%;">Chỉ số cũ</th>
                <th style="width: 6%;">Hệ số</th>
                <th style="width: 10%;">Điện năng tiêu thụ (kWh)</th>
                <th style="width: 8%;">Bao cấp (kWh)</th>
                <th style="width: 9%;">Đơn giá (VNĐ)</th>
                <th style="width: 11%;">Thành tiền (VNĐ)</th>
                <th style="width: 17%;">Tên đơn vị</th>
                <th style="width: 17%;">Địa chỉ</th>
            </tr>
            @foreach($meters as $index => $meter)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ number_format($meter['current_reading'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($meter['previous_reading'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($meter['hsn'], 2, ',', '.') }}</td>
                <td class="center">{{ number_format($meter['consumption'], 0, ',', '.') }}</td>
                <td class="center">{{ $meter['subsidy'] ?? '' }}</td>
                <td class="center">{{ number_format($meter['price'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($meter['amount'], 0, ',', '.') }}</td>
                <td>{{ $meter['name'] ?? '' }}</td>
                <td>{{ $meter['location'] ?? '' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" class="right" style="padding-right: 8px;">Tổng cộng:</td>
                <td class="center">{{ number_format(array_sum(array_column($meters, 'amount')), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="8" style="padding: 5px;">
                    <strong>Bằng chữ:</strong> {{ $amountInWords }} đồng
                </td>
                <td colspan="2" style="padding: 5px; text-align: right; font-weight: bold; font-style: italic;">
                    Phòng Cơ sở vật chất<br>
                    ĐT: 024 3 868 1954
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <div class="footer-date">Hà Nội, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
        
        <div class="footer-row">
            <div class="footer-left">
                <div class="signature-title">PHÒNG CƠ SỞ VẬT CHẤT</div>
                <div class="signature-name">&nbsp;</div>
            </div>
            <div class="footer-right">
                <div class="signature-title">NGƯỜI LẬP PHIẾU</div>
                <div class="signature-name">{{ $signerName ?? 'Hồ Thành Long' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
