<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Models\OrganizationUnit;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use BackedEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Quản lý Admin';
    
    protected static ?string $modelLabel = 'Admin';
    
    protected static ?string $pluralModelLabel = 'Admins';
    
    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin Admin')
                    ->schema([
                        TextInput::make('name')
                            ->label('Họ và tên')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('role')
                            ->label('Vai trò')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin',
                            ])
                            ->default('admin')
                            ->required()
                            ->disabled(fn ($record) => $record && $record->isSuperAdmin() && auth()->id() !== $record->id)
                            ->helperText('Super Admin có toàn quyền. Admin quản lý organizations được gán.'),

                        TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText(fn (string $context): string => 
                                $context === 'edit' ? 'Để trống nếu không muốn đổi mật khẩu' : ''
                            ),

                        TextInput::make('password_confirmation')
                            ->label('Xác nhận mật khẩu')
                            ->password()
                            ->same('password')
                            ->dehydrated(false)
                            ->requiredWith('password'),
                    ])
                    ->columns(2),

                SchemaSection::make('Organizations quản lý')
                    ->schema(function () {
                        $parents = OrganizationUnit::whereNull('parent_id')
                            ->orWhere('type', 'UNIT')
                            ->orderBy('name')
                            ->get();
                        
                        $fields = [];
                        
                        // Thêm ViewField với HTML tùy chỉnh chứa tất cả checkboxes
                        $fields[] = ViewField::make('organization_tree')
                            ->view('filament.forms.components.organization-checkboxes', [
                                'parents' => $parents,
                            ])
                            ->dehydrated(false);
                        
                        // Thêm các hidden fields để track state (dùng Hidden thay vì Checkbox)
                        foreach ($parents as $parent) {
                            $fields[] = Hidden::make('org_' . $parent->id)
                                ->default(false);
                            
                            $children = OrganizationUnit::where('parent_id', $parent->id)->get();
                            foreach ($children as $child) {
                                $fields[] = Hidden::make('org_' . $child->id)
                                    ->default(false);
                            }
                        }
                        
                        return $fields;
                    })
                    ->description('Chọn các đơn vị hoặc hộ tiêu thụ mà admin này có thể quản lý.')
                    ->hidden(fn ($record) => $record && $record->isSuperAdmin())
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Vai trò')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'super_admin',
                        'primary' => 'admin',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('organizationUnits_count')
                    ->label('Organizations')
                    ->counts('organizationUnits')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Tạo bởi')
                    ->placeholder('—')
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (User $record) {
                        // Detach all organizations before delete
                        $record->organizationUnits()->detach();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Detach all organizations before delete
                            foreach ($records as $record) {
                                $record->organizationUnits()->detach();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Only Super Admin can access this resource
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Scope query - Super Admin sees all, Admin sees only themselves
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user()?->isSuperAdmin()) {
            return $query;
        }

        // Regular admin only sees themselves
        return $query->where('id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
