<?php

namespace App\Observers;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrganizationUnitObserver
{
    /**
     * Handle the OrganizationUnit "created" event.
     * Tự động assign quyền cho user đã tạo/import
     */
    public function created(OrganizationUnit $organizationUnit): void
    {
        $user = Auth::user();
        
        if ($user && $user instanceof User) {
            // Ghi log activity với thông tin user
            activity()
                ->performedOn($organizationUnit)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'created',
                    'type' => $organizationUnit->type,
                    'code' => $organizationUnit->code,
                    'name' => $organizationUnit->name,
                ])
                ->log('Tạo mới ' . ($organizationUnit->type === 'UNIT' ? 'đơn vị' : 'hộ tiêu thụ') . ': ' . $organizationUnit->name);
            
            // Tự động assign quyền cho user tạo/import
            // Thêm vào bảng pivot user_organization_units nếu chưa có
            if (!$user->organizationUnits()->where('organization_unit_id', $organizationUnit->id)->exists()) {
                $user->organizationUnits()->attach($organizationUnit->id);
                
                activity()
                    ->performedOn($organizationUnit)
                    ->causedBy($user)
                    ->withProperties([
                        'action' => 'auto_assign',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                    ])
                    ->log('Tự động gán quyền cho ' . $user->name);
            }
        }
    }

    /**
     * Handle the OrganizationUnit "updated" event.
     */
    public function updated(OrganizationUnit $organizationUnit): void
    {
        $user = Auth::user();
        
        if ($user) {
            $changes = $organizationUnit->getChanges();
            
            activity()
                ->performedOn($organizationUnit)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'updated',
                    'old' => $organizationUnit->getOriginal(),
                    'new' => $changes,
                ])
                ->log('Cập nhật thông tin: ' . $organizationUnit->name);
        }
    }

    /**
     * Handle the OrganizationUnit "deleted" event.
     */
    public function deleted(OrganizationUnit $organizationUnit): void
    {
        $user = Auth::user();
        
        if ($user) {
            activity()
                ->performedOn($organizationUnit)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'deleted',
                    'code' => $organizationUnit->code,
                    'name' => $organizationUnit->name,
                ])
                ->log('Xóa ' . ($organizationUnit->type === 'UNIT' ? 'đơn vị' : 'hộ tiêu thụ') . ': ' . $organizationUnit->name);
        }
    }
}
