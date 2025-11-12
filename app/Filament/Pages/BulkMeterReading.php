<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Schemas\BulkMeterReadingForm;
use App\Models\MeterReading;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class BulkMeterReading extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.bulk-meter-reading';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Ghi chỉ số hàng loạt';

    protected static ?string $title = 'Ghi chỉ số hàng loạt';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Ghi chỉ số';
    }

    protected function mount(): void
    {
        $this->schema->fill([
            'reading_date' => now()->format('Y-m-d'),
            'reader_name' => auth()->user()?->name,
        ]);
    }

    public function schema(Schema $schema): Schema
    {
        return BulkMeterReadingForm::configure($schema)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->schema->getState();

        try {
            DB::beginTransaction();

            $saved = 0;
            $errors = [];

            foreach (($data['readings'] ?? []) as $reading) {
                if (!empty($reading['reading_value'])) {
                    try {
                        MeterReading::create([
                            'electric_meter_id' => $reading['electric_meter_id'],
                            'reading_date' => $data['reading_date'],
                            'reading_value' => $reading['reading_value'],
                            'reader_name' => $data['reader_name'] ?? null,
                            'notes' => $reading['notes'] ?? null,
                        ]);
                        $saved++;
                    } catch (\Exception $e) {
                        $meterNo = $reading['meter_number'] ?? 'N/A';
                        $errors[] = "Công tơ {$meterNo}: {$e->getMessage()}";
                    }
                }
            }

            DB::commit();

            if ($saved > 0) {
                Notification::make()
                    ->success()
                    ->title('Ghi chỉ số thành công')
                    ->body("Đã ghi {$saved} công tơ" . (count($errors) > 0 ? ", " . count($errors) . " lỗi" : ""))
                    ->send();

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        Notification::make()
                            ->warning()
                            ->title('Lỗi')
                            ->body($error)
                            ->send();
                    }
                }

                // Reset form
                $this->schema->fill([
                    'reading_date' => now()->format('Y-m-d'),
                    'reader_name' => auth()->user()?->name,
                    'substation_id' => null,
                    'readings' => [],
                ]);
            } else {
                Notification::make()
                    ->warning()
                    ->title('Không có dữ liệu')
                    ->body('Vui lòng nhập chỉ số cho ít nhất 1 công tơ')
                    ->send();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Lỗi')
                ->body($e->getMessage())
                ->send();
        }
    }
}

