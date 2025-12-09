<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrganizationHelper
{
    /**
     * Get IDs của tất cả organizations mà user được assign
     */
    public static function getUserOrganizationIds(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return [];
        }
        
        // Super admin thấy tất cả
        if ($user->role === 'super_admin') {
            return \App\Models\OrganizationUnit::pluck('id')->toArray();
        }
        
        // Admin thường chỉ thấy orgs được assign
        return $user->organizationUnits()->pluck('organization_units.id')->toArray();
    }
    
    /**
     * Check xem user có quyền truy cập organization không
     */
    public static function canAccessOrganization(int $organizationId, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Super admin có quyền với tất cả
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Check xem org có trong danh sách được assign không
        return in_array($organizationId, static::getUserOrganizationIds($user));
    }
    
    /**
     * Scope query để chỉ lấy dữ liệu của organizations được assign
     * Áp dụng cho các model có relation với OrganizationUnit
     */
    public static function scopeToUserOrganizations(Builder $query, ?User $user = null, string $column = 'organization_unit_id'): Builder
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return empty
        }
        
        // Super admin thấy tất cả
        if ($user->role === 'super_admin') {
            return $query;
        }
        
        $orgIds = static::getUserOrganizationIds($user);
        
        if (empty($orgIds)) {
            return $query->whereRaw('1 = 0'); // Return empty
        }
        
        return $query->whereIn($column, $orgIds);
    }
    
    /**
     * Scope đặc biệt cho OrganizationUnit - filter theo chính ID của nó
     */
    public static function scopeOrganizationUnitsToUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super admin thấy tất cả
        if ($user->role === 'super_admin') {
            return $query;
        }
        
        $orgIds = static::getUserOrganizationIds($user);
        
        if (empty($orgIds)) {
            return $query->whereRaw('1 = 0');
        }
        
        // Filter theo chính ID của organization
        return $query->whereIn('id', $orgIds);
    }
    
    /**
     * Scope cho ElectricMeter (có thể thuộc substation hoặc organization)
     */
    public static function scopeElectricMetersToUserOrganizations(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super admin thấy tất cả
        if ($user->role === 'super_admin') {
            return $query;
        }
        
        $orgIds = static::getUserOrganizationIds($user);
        
        if (empty($orgIds)) {
            return $query->whereRaw('1 = 0');
        }
        
        // ElectricMeter có thể có organization_unit_id hoặc thuộc substation
        // Cần check cả 2 trường hợp
        return $query->where(function($q) use ($orgIds) {
            $q->whereIn('organization_unit_id', $orgIds)
              ->orWhereHas('substation', function($subQuery) use ($orgIds) {
                  $subQuery->whereIn('organization_unit_id', $orgIds);
              });
        });
    }
}
