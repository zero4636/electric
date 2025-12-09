<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\OrganizationUnit;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected static ?string $title = 'Tạo Admin mới';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Tạo')
                ->action(function () {
                    $this->create();
                })
                ->keyBindings(['mod+s'])
                ->color('primary')
                ->icon('heroicon-o-plus'),
            \Filament\Actions\Action::make('cancel')
                ->label('Hủy')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-x-mark'),
        ];
    }
    
    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by to current user
        $data['created_by'] = auth()->id();
        
        // Extract organization IDs from org_* checkboxes
        $orgIds = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'org_') && $value === true) {
                $orgIds[] = (int) str_replace('org_', '', $key);
            }
        }
        
        // Store for afterCreate
        $this->selectedOrgIds = $orgIds;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync organizations
        if (!empty($this->selectedOrgIds)) {
            $this->record->organizationUnits()->sync($this->selectedOrgIds);
        }
    }

    protected array $selectedOrgIds = [];
}