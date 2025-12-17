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
            'email_ho',
            'ma_don_vi',
            'ten_don_vi',
            'loai_don_vi',
            'dia_chi_don_vi',
            'sdt_don_vi',
            'dai_dien_don_vi',
            'email_don_vi',
            'so_cong_to*',
            'loai_cong_to',
            'vi_tri_dat',
            'tram_bien_ap',
            'he_so_nhan',
            'bao_cap',
            'chi_so_moi',
            'thang_ghi',
            'nguoi_thuc_hien',
            'email_nguoi_tao',
            'ghi_chu',
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
            $isConsumer ? ($orgUnit?->email ?? '') : '',
            // Đơn vị tiêu thụ: nếu là hộ thì lấy parent, nếu là đơn vị thì lấy chính nó
            $isConsumer ? ($parentOrg?->code ?? '') : ($isOrg ? ($orgUnit?->code ?? '') : ''),
            $isConsumer ? ($parentOrg?->name ?? '') : ($isOrg ? ($orgUnit?->name ?? '') : ''),
            $isConsumer ? 'Đơn vị tiêu thụ' : ($isOrg ? 'Đơn vị tiêu thụ' : ''),
            $isConsumer ? ($parentOrg?->address ?? '') : ($isOrg ? ($orgUnit?->address ?? '') : ''),
            $isConsumer ? ($parentOrg?->contact_phone ?? '') : ($isOrg ? ($orgUnit?->contact_phone ?? '') : ''),
            $isConsumer ? ($parentOrg?->contact_name ?? '') : ($isOrg ? ($orgUnit?->contact_name ?? '') : ''),
            $isConsumer ? ($parentOrg?->email ?? '') : ($isOrg ? ($orgUnit?->email ?? '') : ''),
            $meter?->meter_number ?? '',
            $meter?->phase_type === '3_PHASE' ? '3 pha' : '1 pha',
            $meter?->installation_location ?? '',
            $substation?->code ?? '',
            $meter?->hsn ?? 1,
            $meter?->subsidized_kwh ?? 0,
            $reading->reading_value ?? '',
            $thangGhi,
            $reading->reader_name ?? '',
            '', // email_nguoi_tao - để trống trong export
            $reading->notes ?? '',
        ];
    }
}
