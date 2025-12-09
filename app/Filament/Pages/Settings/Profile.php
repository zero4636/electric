<?php

namespace App\Filament\Pages\Settings;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use BackedEnum;
use UnitEnum;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'Thông tin cá nhân';
    
    protected static ?string $title = 'Thông tin cá nhân';
    
    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';
    
    protected static ?int $navigationSort = 200;

    // View property is not static in Filament v4
    public string $view = 'filament.pages.settings.profile';
    
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Thông tin tài khoản')
                    ->description('Cập nhật thông tin cá nhân của bạn')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Họ và tên')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('users', 'email', fn () => auth()->user())
                                    ->prefixIcon('heroicon-o-envelope'),
                            ]),
                        
                        Placeholder::make('role')
                            ->label('Vai trò')
                            ->content(fn () => auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin'),
                        
                        Placeholder::make('created_at')
                            ->label('Ngày tạo tài khoản')
                            ->content(fn () => auth()->user()->created_at->format('d/m/Y H:i')),
                    ])
                    ->columns(1),

                Section::make('Đổi mật khẩu')
                    ->description('Cập nhật mật khẩu để bảo mật tài khoản')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Mật khẩu hiện tại')
                            ->password()
                            ->dehydrated(false)
                            ->requiredWith('password')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->revealable(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Mật khẩu mới')
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-key')
                                    ->revealable()
                                    ->helperText('Tối thiểu 8 ký tự'),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Xác nhận mật khẩu mới')
                                    ->password()
                                    ->same('password')
                                    ->dehydrated(false)
                                    ->requiredWith('password')
                                    ->prefixIcon('heroicon-o-key')
                                    ->revealable(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(true)
                    ->extraAttributes(['class' => 'mb-6']),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Check current password if changing password
        if (!empty($data['current_password'])) {
            if (!Hash::check($data['current_password'], auth()->user()->password)) {
                Notification::make()
                    ->title('Mật khẩu hiện tại không đúng')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Update user
        $user = auth()->user();
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        Notification::make()
            ->title('Cập nhật thông tin thành công')
            ->success()
            ->send();
    }
}
