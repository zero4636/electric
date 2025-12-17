<?php

namespace App\Exports;

use App\Models\MeterReading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComprehensiveBillingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $query = MeterReading::with([
            'electricMeter.organizationUnit',
            'electricMeter.substation'
        ])->orderBy('reading_date', 'desc');

        // Filter by month/year if provided
        if ($this->month && $this->year) {
            $query->whereMonth('reading_date', $this->month)
                  ->whereYear('reading_date', $this->year);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'stt',
            'ma_ho_tieu_thu',
            'ten_ho_tieu_thu',
            'dia_chi_ho',
            'sdt_ho',
            'dai_dien_ho',
            'ma_don_vi',
            'ten_don_vi',
            'loai_don_vi',
            'dia_chi_don_vi',
            'sdt_don_vi',
            'so_cong_to',
            'loai_cong_to',
            'vi_tri_cong_to',
            'tram_bien_ap',
            'he_so_nhan',
            'chi_so_moi',
            'thang_ghi',
            'nguoi_thuc_hien',
            'ghi_chu_chi_so',
        ];
    }

    public function map($reading): array
    {
        static $stt = 0;
        $stt++;

        $meter = $reading->electricMeter;
        $orgUnit = $meter?->organizationUnit;
        $substation = $meter?->substation;

        // Format tháng ghi: "tháng 10 năm 2025"
        $month = $reading->reading_date?->format('n') ?? ($this->month ?? '');
        $year = $reading->reading_date?->format('Y') ?? ($this->year ?? '');
        $thangGhi = $month && $year ? ("tháng $month năm $year") : '';

        // Nếu có organizationUnit là hộ tiêu thụ (type=CONSUMER) thì lấy thông tin hộ, còn lại là đơn vị
        $isConsumer = $orgUnit?->type === 'CONSUMER';
        $parentOrg = $isConsumer ? $orgUnit?->parent : null;
        $isOrg = $orgUnit && !$isConsumer;

        return [
            $stt,
            $isConsumer ? ($orgUnit?->code ?? '') : '',
            $isConsumer ? ($orgUnit?->name ?? '') : '',
            $isConsumer ? ($orgUnit?->address ?? '') : '',
            $isConsumer ? ($orgUnit?->contact_phone ?? '') : '',
            $isConsumer ? ($orgUnit?->contact_name ?? '') : '',
            // Đơn vị tiêu thụ: nếu là hộ thì lấy parent, nếu là đơn vị thì lấy chính nó
            $isConsumer ? ($parentOrg?->code ?? '') : ($isOrg ? ($orgUnit?->code ?? '') : ''),
            $isConsumer ? ($parentOrg?->name ?? '') : ($isOrg ? ($orgUnit?->name ?? '') : ''),
            $isConsumer ? ($parentOrg?->type ?? '') : ($isOrg ? ($orgUnit?->type ?? '') : ''),
            $isConsumer ? ($parentOrg?->address ?? '') : ($isOrg ? ($orgUnit?->address ?? '') : ''),
            $isConsumer ? ($parentOrg?->contact_phone ?? '') : ($isOrg ? ($orgUnit?->contact_phone ?? '') : ''),
            $meter?->meter_number ?? '',
            $meter?->meter_type ?? '',
            $meter?->installation_location ?? '',
            $substation?->name ?? '',
            $meter?->hsn ?? '',
            $reading->reading_value ?? '',
            $thangGhi,
            $reading->reader_name ?? '',
            $reading->notes ?? '',
        ];
    }
}
