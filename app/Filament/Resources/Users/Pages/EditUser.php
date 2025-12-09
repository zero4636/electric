<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\OrganizationUnit;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    protected static ?string $title = 'Chỉnh sửa Admin';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Lưu')
                ->action(function () {
                    $this->save();
                })
                ->keyBindings(['mod+s'])
                ->color('primary')
                ->icon('heroicon-o-check'),
            \Filament\Actions\Action::make('cancel')
                ->label('Hủy')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-x-mark'),
            DeleteAction::make()
                ->disabled(fn ($record) => $record->isSuperAdmin() || $record->id === auth()->id())
                ->before(function ($record) {
                    // Detach all organizations before delete
                    $record->organizationUnits()->detach();
                }),
        ];
    }
    
    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load selected organization IDs and set org_* checkboxes
        $orgIds = $this->record->organizationUnits()->pluck('organization_units.id')->toArray();
        
        foreach ($orgIds as $orgId) {
            $data['org_' . $orgId] = true;
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract organization IDs from org_* checkboxes
        $orgIds = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'org_') && $value === true) {
                $orgIds[] = (int) str_replace('org_', '', $key);
            }
        }
        
        \Log::info('EditUser - Form data before save:', [
            'all_keys' => array_keys($data),
            'org_keys' => array_filter(array_keys($data), fn($k) => str_starts_with($k, 'org_')),
            'extracted_org_ids' => $orgIds
        ]);
        
        // Store for afterSave
        $this->selectedOrgIds = $orgIds;
        
        return $data;
    }

    protected function afterSave(): void
    {
        \Log::info('EditUser - After save:', [
            'selected_org_ids' => $this->selectedOrgIds,
            'user_id' => $this->record->id
        ]);
        
        // Sync organizations
        if (isset($this->selectedOrgIds)) {
            $this->record->organizationUnits()->sync($this->selectedOrgIds);
            
            \Log::info('EditUser - Synced organizations:', [
                'synced_ids' => $this->selectedOrgIds,
                'current_orgs' => $this->record->organizationUnits()->pluck('organization_units.id')->toArray()
            ]);
        }
    }
    
    protected array $selectedOrgIds = [];

    protected function getRedirectUrl(): ?string
    {
        return null; // Stay on current page after save
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Đã lưu thành công!';
    }
}
