<?php<?php



namespace App\Filament\Pages;namespace App\Filament\Pages;



use App\Filament\Pages\Schemas\BulkMeterReadingForm;use App\Filament\Pages\Schemas\BulkMeterReadingForm;

use App\Models\MeterReading;use App\Models\MeterReading;

use BackedEnum;use BackedEnum;

use Filament\Forms\Concerns\InteractsWithForms;use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Forms\Contracts\HasForms;use Filament\Forms\Contracts\HasForms;

use Filament\Notifications\Notification;use Filament\Notifications\Notification;

use Filament\Pages\Page;use Filament\Pages\Page;

use Filament\Schemas\Concerns\InteractsWithSchemas;use Filament\Schemas\Concerns\InteractsWithSchemas;

use Filament\Schemas\Contracts\HasSchemas;use Filament\Schemas\Contracts\HasSchemas;

use Filament\Schemas\Schema;use Filament\Schemas\Schema;

use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\DB;



class BulkMeterReading extends Page implements HasForms, HasSchemasclass BulkMeterReading extends Page implements HasForms, HasSchemas

{{

    use InteractsWithForms;    use InteractsWithForms;

    use InteractsWithSchemas;    use InteractsWithSchemas;



    protected string $view = 'filament.pages.bulk-meter-reading';    protected string $view = 'filament.pages.bulk-meter-reading';



    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';



    protected static ?string $navigationLabel = 'Ghi chỉ số hàng loạt';    protected static ?string $navigationLabel = 'Ghi chỉ số hàng loạt';



    protected static ?string $title = 'Ghi chỉ số hàng loạt';    protected static ?string $title = 'Ghi chỉ số công tơ hàng loạt';



    protected static ?int $navigationSort = 2;    protected static ?int $navigationSort = 1;



    public ?array $data = [];    public ?array $data = [];



    public static function getNavigationGroup(): ?string    public static function getNavigationGroup(): ?string

    {    {

        return 'Ghi chỉ số';        return 'Ghi chỉ số';

    }    }



    protected function mount(): void    protected function mount(): void

    {    {

        $this->schema->fill([        $this->schema->fill([

            'reading_date' => now()->format('Y-m-d'),            'reading_date' => now()->format('Y-m-d'),

            'reader_name' => auth()->user()?->name,            'reader_name' => auth()->user()?->name,

        ]);        ]);

    }    }



    public function schema(Schema $schema): Schema    public function schema(Schema $schema): Schema

    {    {

        return BulkMeterReadingForm::configure($schema)        return BulkMeterReadingForm::configure($schema);

            ->statePath('data');    }

    }    {

        return $form

    public function save(): void            ->schema([

    {                Wizard::make([

        $data = $this->schema->getState();                    Wizard\Step::make('Chọn trạm/khu vực')

                        ->schema([

        try {                            Select::make('substation_id')

            DB::beginTransaction();                                ->label('Trạm biến áp / Khu vực')

                                ->options(Substation::where('status', 'ACTIVE')->pluck('name', 'id'))

            $saved = 0;                                ->required()

            $errors = [];                                ->live()

                                ->searchable()

            foreach ($data['readings'] as $reading) {                                ->afterStateUpdated(function ($state, $set) {

                if (!empty($reading['reading_value'])) {                                    if ($state) {

                    try {                                        $meters = ElectricMeter::where('substation_id', $state)

                        MeterReading::create([                                            ->where('status', 'ACTIVE')

                            'electric_meter_id' => $reading['electric_meter_id'],                                            ->with(['organizationUnit', 'meterReadings' => function ($query) {

                            'reading_date' => $data['reading_date'],                                                $query->latest('reading_date')->limit(1);

                            'reading_value' => $reading['reading_value'],                                            }])

                            'reader_name' => $data['reader_name'],                                            ->get();

                            'notes' => $reading['notes'] ?? null,

                        ]);                                        $readings = [];

                        $saved++;                                        foreach ($meters as $meter) {

                    } catch (\Exception $e) {                                            $latestReading = $meter->meterReadings->first();

                        $errors[] = "Công tơ {$reading['meter_number']}: {$e->getMessage()}";                                            $readings[] = [

                    }                                                'electric_meter_id' => $meter->id,

                }                                                'meter_number' => $meter->meter_number,

            }                                                'organization' => $meter->organizationUnit?->name,

                                                'location' => $meter->installation_location,

            DB::commit();                                                'latest_reading' => $latestReading?->reading_value,

                                                'latest_date' => $latestReading?->reading_date?->format('d/m/Y'),

            if ($saved > 0) {                                                'reading_value' => null,

                Notification::make()                                                'notes' => null,

                    ->success()                                            ];

                    ->title('Ghi chỉ số thành công')                                        }

                    ->body("Đã ghi {$saved} công tơ" . (count($errors) > 0 ? ", " . count($errors) . " lỗi" : ""))                                        $set('readings', $readings);

                    ->send();                                    }

                                })

                if (count($errors) > 0) {                                ->helperText('Chọn trạm để hiển thị danh sách công tơ'),

                    foreach ($errors as $error) {

                        Notification::make()                            DatePicker::make('reading_date')

                            ->warning()                                ->label('Ngày ghi chỉ số')

                            ->title('Lỗi')                                ->required()

                            ->body($error)                                ->default(now())

                            ->send();                                ->maxDate(now())

                    }                                ->native(false)

                }                                ->displayFormat('d/m/Y'),



                // Reset form                            TextInput::make('reader_name')

                $this->schema->fill([                                ->label('Người ghi')

                    'reading_date' => now()->format('Y-m-d'),                                ->required()

                    'reader_name' => auth()->user()?->name,                                ->default(auth()->user()?->name)

                    'substation_id' => null,                                ->maxLength(255),

                    'readings' => [],                        ]),

                ]);

            } else {                    Wizard\Step::make('Nhập chỉ số')

                Notification::make()                        ->schema([

                    ->warning()                            Repeater::make('readings')

                    ->title('Không có dữ liệu')                                ->label('Danh sách công tơ')

                    ->body('Vui lòng nhập chỉ số cho ít nhất 1 công tơ')                                ->schema([

                    ->send();                                    TextInput::make('meter_number')

            }                                        ->label('Số công tơ')

        } catch (\Exception $e) {                                        ->disabled()

            DB::rollBack();                                        ->dehydrated(false),



            Notification::make()                                    TextInput::make('organization')

                ->danger()                                        ->label('Đơn vị')

                ->title('Lỗi')                                        ->disabled()

                ->body($e->getMessage())                                        ->dehydrated(false),

                ->send();

        }                                    TextInput::make('location')

    }                                        ->label('Vị trí')

}                                        ->disabled()

                                        ->dehydrated(false),

                                    TextInput::make('latest_reading')
                                        ->label('Chỉ số cũ')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->suffix('kWh'),

                                    TextInput::make('reading_value')
                                        ->label('Chỉ số mới')
                                        ->numeric()
                                        ->required()
                                        ->suffix('kWh')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, $set, Get $get) {
                                            $latest = $get('latest_reading');
                                            if ($state && $latest) {
                                                $set('consumption', $state - $latest);
                                            }
                                        })
                                        ->rules([
                                            function (Get $get) {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $latest = $get('latest_reading');
                                                    if ($latest && $value <= $latest) {
                                                        $fail("Phải lớn hơn {$latest}");
                                                    }
                                                };
                                            },
                                        ]),

                                    TextInput::make('consumption')
                                        ->label('Tiêu thụ')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->suffix('kWh')
                                        ->extraAttributes(['class' => 'font-bold']),

                                    TextInput::make('notes')
                                        ->label('Ghi chú')
                                        ->maxLength(500)
                                        ->placeholder('Ghi chú (nếu có)'),

                                    TextInput::make('electric_meter_id')->hidden(),
                                ])
                                ->columns(7)
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->defaultItems(0),
                        ]),
                ])
                    ->submitAction(view('filament.pages.bulk-meter-reading-submit'))
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $saved = 0;
            $errors = [];

            foreach ($data['readings'] as $reading) {
                if (!empty($reading['reading_value'])) {
                    try {
                        MeterReading::create([
                            'electric_meter_id' => $reading['electric_meter_id'],
                            'reading_date' => $data['reading_date'],
                            'reading_value' => $reading['reading_value'],
                            'reader_name' => $data['reader_name'],
                            'notes' => $reading['notes'],
                        ]);
                        $saved++;
                    } catch (\Exception $e) {
                        $errors[] = "Công tơ {$reading['meter_number']}: {$e->getMessage()}";
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
                $this->form->fill([
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
