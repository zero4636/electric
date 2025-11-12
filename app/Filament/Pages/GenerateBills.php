<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Schemas\GenerateBillsForm;
use App\Models\Bill;
use App\Models\ElectricMeter;
use App\Services\BillingService;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GenerateBills extends Page implements HasForms, HasSchemas, HasTable
{
    use InteractsWithForms;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.generate-bills';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationLabel = 'Tạo hóa đơn';

    protected static ?string $title = 'Tạo hóa đơn tự động';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Hóa đơn';
    }

    protected function mount(): void
    {
        $this->schema->fill([
            'billing_month' => now()->startOfMonth()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
    }

    public function schema(Schema $schema): Schema
    {
        return GenerateBillsForm::configure($schema)
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bill::query()
                    ->with(['organizationUnit', 'billDetails.electricMeter'])
                    ->latest('billing_month')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Mã HĐ')
                    ->sortable(),

                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('billing_month')
                    ->label('Tháng')
                    ->date('m/Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                        'PAID' => 'success',
                        'OVERDUE' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNPAID' => 'Chưa thanh toán',
                        'PARTIAL' => 'Thanh toán một phần',
                        'PAID' => 'Đã thanh toán',
                        'OVERDUE' => 'Quá hạn',
                        default => $state,
                    }),

                TextColumn::make('due_date')
                    ->label('Hạn thanh toán')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('billing_month', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function generate(): void
    {
        $data = $this->schema->getState();

        try {
            DB::beginTransaction();

            $billingService = app(BillingService::class);
            $billingMonth = \Carbon\Carbon::parse($data['billing_month']);

            // Lấy danh sách công tơ cần tạo hóa đơn
            $query = ElectricMeter::where('status', 'ACTIVE');

            if (!empty($data['organization_unit_id'])) {
                $query->where('organization_unit_id', $data['organization_unit_id']);
            }

            if (!empty($data['electric_meter_ids'])) {
                $query->whereIn('id', $data['electric_meter_ids']);
            }

            $meters = $query->get();

            if ($meters->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('Không có công tơ')
                    ->body('Không tìm thấy công tơ phù hợp để tạo hóa đơn')
                    ->send();
                return;
            }

            $created = 0;
            $errors = [];

            foreach ($meters as $meter) {
                try {
                    $bill = $billingService->createBillForMeter(
                        $meter,
                        $billingMonth,
                        \Carbon\Carbon::parse($data['due_date'])
                    );
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Công tơ {$meter->meter_number}: {$e->getMessage()}";
                }
            }

            DB::commit();

            if ($created > 0) {
                Notification::make()
                    ->success()
                    ->title('Tạo hóa đơn thành công')
                    ->body("Đã tạo {$created} hóa đơn" . (count($errors) > 0 ? ", " . count($errors) . " lỗi" : ""))
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
                    'billing_month' => now()->startOfMonth()->format('Y-m-d'),
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'organization_unit_id' => null,
                    'electric_meter_ids' => null,
                ]);
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
