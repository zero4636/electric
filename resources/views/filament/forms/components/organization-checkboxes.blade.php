<div>
    <style>
        .org-checkbox-tree {
            border: 1px solid rgb(var(--gray-200));
            border-radius: 0.75rem;
            background: white;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }
        
        .dark .org-checkbox-tree {
            background: rgb(var(--gray-950));
            border-color: rgb(var(--gray-800));
        }
        
        .org-checkbox-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: linear-gradient(to bottom, rgb(var(--gray-50)), rgb(var(--gray-100)));
            border-bottom: 1px solid rgb(var(--gray-200));
        }
        
        .dark .org-checkbox-header {
            background: linear-gradient(to bottom, rgb(var(--gray-900)), rgb(var(--gray-800)));
            border-color: rgb(var(--gray-700));
        }
        
        .org-checkbox-count {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(var(--gray-700));
        }
        
        .dark .org-checkbox-count {
            color: rgb(var(--gray-300));
        }
        
        .org-checkbox-count strong {
            color: rgb(var(--primary-600));
            font-weight: 700;
            font-size: 1.125rem;
        }
        
        .dark .org-checkbox-count strong {
            color: rgb(var(--primary-400));
        }
        
        .org-checkbox-controls {
            display: flex;
            gap: 0.5rem;
        }
        
        .org-checkbox-btn {
            font-size: 0.75rem;
            font-weight: 500;
            color: white;
            background: rgb(var(--primary-600));
            border: none;
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }
        
        .org-checkbox-btn:hover {
            background: rgb(var(--primary-700));
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            transform: translateY(-1px);
        }
        
        .dark .org-checkbox-btn {
            background: rgb(var(--primary-500));
        }
        
        .dark .org-checkbox-btn:hover {
            background: rgb(var(--primary-400));
        }
        
        .org-checkbox-parent {
            border-bottom: 1px solid rgb(var(--gray-100));
            transition: background 0.2s;
        }
        
        .dark .org-checkbox-parent {
            border-color: rgb(var(--gray-800));
        }
        
        .org-checkbox-parent:last-child {
            border-bottom: none;
        }
        
        .org-checkbox-parent:hover {
            background: rgb(var(--gray-50) / 0.5);
        }
        
        .dark .org-checkbox-parent:hover {
            background: rgb(var(--gray-900) / 0.5);
        }
        
        .org-checkbox-parent-header {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            gap: 0.75rem;
            background: white;
            transition: background 0.2s;
            user-select: none;
            cursor: pointer;
        }
        
        .dark .org-checkbox-parent-header {
            background: rgb(var(--gray-950));
        }
        
        .org-checkbox-parent-header:hover {
            background: rgb(var(--gray-50));
        }
        
        .dark .org-checkbox-parent-header:hover {
            background: rgb(var(--gray-900));
        }
        
        .org-checkbox-parent-header.expanded {
            background: rgb(var(--primary-50) / 0.3);
            border-bottom: 1px solid rgb(var(--primary-100));
        }
        
        .dark .org-checkbox-parent-header.expanded {
            background: rgb(var(--primary-500) / 0.05);
            border-color: rgb(var(--primary-900));
        }
        
        .org-checkbox-parent-icon {
            width: 1.5rem;
            height: 1.5rem;
            flex-shrink: 0;
            color: rgb(var(--primary-600));
        }
        
        .dark .org-checkbox-parent-icon {
            color: rgb(var(--primary-400));
        }
        
        .org-checkbox-parent-cb,
        .org-checkbox-child-cb {
            cursor: pointer;
            accent-color: rgb(var(--primary-600));
            flex-shrink: 0;
            width: 1.125rem;
            height: 1.125rem;
            border-radius: 0.25rem;
        }
        
        .org-checkbox-child-cb {
            width: 1rem;
            height: 1rem;
        }
        
        .org-checkbox-parent-label {
            font-weight: 600;
            font-size: 0.9375rem;
            color: rgb(var(--gray-900));
            flex: 1;
        }
        
        .dark .org-checkbox-parent-label {
            color: rgb(var(--gray-100));
        }
        
        .org-checkbox-child-label {
            font-size: 0.875rem;
            color: rgb(var(--gray-700));
            user-select: none;
            flex: 1;
        }
        
        .dark .org-checkbox-child-label {
            color: rgb(var(--gray-300));
        }
        
        .org-checkbox-expand-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-left: auto;
            transition: transform 0.2s ease;
            flex-shrink: 0;
            color: rgb(var(--gray-400));
            padding: 0.125rem;
            border-radius: 0.25rem;
        }
        
        .org-checkbox-parent-header:hover .org-checkbox-expand-icon {
            background: rgb(var(--gray-200));
            color: rgb(var(--gray-600));
        }
        
        .dark .org-checkbox-expand-icon {
            color: rgb(var(--gray-500));
        }
        
        .dark .org-checkbox-parent-header:hover .org-checkbox-expand-icon {
            background: rgb(var(--gray-800));
            color: rgb(var(--gray-300));
        }
        
        .org-checkbox-expand-icon.expanded {
            transform: rotate(90deg);
            color: rgb(var(--primary-600));
        }
        
        .dark .org-checkbox-expand-icon.expanded {
            color: rgb(var(--primary-400));
        }
        
        .org-checkbox-parent-header.expanded:hover .org-checkbox-expand-icon {
            background: rgb(var(--primary-100));
        }
        
        .dark .org-checkbox-parent-header.expanded:hover .org-checkbox-expand-icon {
            background: rgb(var(--primary-900));
        }
        
        .org-checkbox-count-badge {
            font-size: 0.75rem;
            font-weight: 600;
            color: rgb(var(--gray-500));
            background: rgb(var(--gray-100));
            padding: 0.25rem 0.625rem;
            border-radius: 1rem;
            border: 1px solid rgb(var(--gray-200));
        }
        
        .dark .org-checkbox-count-badge {
            color: rgb(var(--gray-400));
            background: rgb(var(--gray-800));
            border-color: rgb(var(--gray-700));
        }
        
        .org-checkbox-count-badge.has-selected {
            background: rgb(var(--primary-50));
            color: rgb(var(--primary-700));
            border-color: rgb(var(--primary-200));
        }
        
        .dark .org-checkbox-count-badge.has-selected {
            background: rgb(var(--primary-900));
            color: rgb(var(--primary-300));
            border-color: rgb(var(--primary-800));
        }
        
        .org-checkbox-children {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.2s ease-out;
            background: rgb(var(--gray-50) / 0.3);
            opacity: 0;
        }
        
        .dark .org-checkbox-children {
            background: rgb(var(--gray-900) / 0.3);
        }
        
        .org-checkbox-children.expanded {
            max-height: 3000px;
            opacity: 1;
            transition: max-height 0.5s ease-in, opacity 0.3s ease-in;
        }
        
        .org-checkbox-children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 0.375rem;
            padding: 1rem 1.25rem 1rem 3.5rem;
        }
        
        @media (max-width: 768px) {
            .org-checkbox-children-grid {
                grid-template-columns: 1fr;
                padding: 0.75rem 1rem;
            }
        }
        
        .org-checkbox-child-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.15s;
            background: white;
            border: 1px solid rgb(var(--gray-200));
        }
        
        .dark .org-checkbox-child-item {
            background: rgb(var(--gray-900));
            border-color: rgb(var(--gray-700));
        }
        
        .org-checkbox-child-item:hover {
            background: rgb(var(--primary-50));
            border-color: rgb(var(--primary-300));
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            transform: translateX(2px);
        }
        
        .dark .org-checkbox-child-item:hover {
            background: rgb(var(--primary-950));
            border-color: rgb(var(--primary-700));
        }
        
        .org-checkbox-child-icon {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            color: rgb(var(--gray-400));
        }
        
        .dark .org-checkbox-child-icon {
            color: rgb(var(--gray-500));
        }
        
        .org-checkbox-child-item:hover .org-checkbox-child-icon {
            color: rgb(var(--primary-500));
        }
        
        .dark .org-checkbox-child-item:hover .org-checkbox-child-icon {
            color: rgb(var(--primary-400));
        }
        
        .org-checkbox-standalone {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgb(var(--gray-100));
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: background 0.15s;
        }
        
        .dark .org-checkbox-standalone {
            border-color: rgb(var(--gray-800));
        }
        
        .org-checkbox-standalone:last-child {
            border-bottom: none;
        }
        
        .org-checkbox-standalone:hover {
            background: rgb(var(--gray-50));
        }
        
        .dark .org-checkbox-standalone:hover {
            background: rgb(var(--gray-900));
        }
    </style>

    <div class="org-checkbox-tree">
        <div class="org-checkbox-header">
            <div class="org-checkbox-count">
                <strong id="selected-total-count">0</strong> đơn vị được chọn
            </div>
            <div class="org-checkbox-controls">
                <button type="button" class="org-checkbox-btn" onclick="expandAllOrgs()">
                    ⊕ Mở tất cả
                </button>
                <button type="button" class="org-checkbox-btn" onclick="collapseAllOrgs()">
                    ⊖ Đóng tất cả
                </button>
            </div>
        </div>

        @foreach($parents as $parent)
            @php
                $children = \App\Models\OrganizationUnit::where('parent_id', $parent->id)->orderBy('name')->get();
            @endphp
            
            @if($children->count() > 0)
                <div class="org-checkbox-parent">
                    <div class="org-checkbox-parent-header" 
                         id="parent-header-{{ $parent->id }}"
                         onclick="toggleOrgParent({{ $parent->id }})">
                        <!-- Icon đơn vị -->
                        <svg class="org-checkbox-parent-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        
                        <!-- Checkbox -->
                        <input type="checkbox" 
                               class="org-checkbox-parent-cb" 
                               id="org-cb-parent-{{ $parent->id }}"
                               wire:model="data.org_{{ $parent->id }}"
                               onchange="handleOrgParentChange({{ $parent->id }})"
                               onclick="event.stopPropagation()">
                        
                        <!-- Label -->
                        <span class="org-checkbox-parent-label">
                            {{ $parent->name }}
                        </span>
                        
                        <!-- Count badge -->
                        <span class="org-checkbox-count-badge" id="parent-count-{{ $parent->id }}">
                            0/{{ $children->count() }}
                        </span>
                        
                        <!-- Expand icon (cuối cùng) -->
                        <svg class="org-checkbox-expand-icon" 
                             id="parent-icon-{{ $parent->id }}" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    
                    <div class="org-checkbox-children" id="parent-children-{{ $parent->id }}">
                        <div class="org-checkbox-children-grid">
                            @foreach($children as $child)
                                <div class="org-checkbox-child-item">
                                    <!-- Checkbox -->
                                    <input type="checkbox" 
                                           class="org-checkbox-child-cb child-of-parent-{{ $parent->id }}" 
                                           id="org-cb-child-{{ $child->id }}"
                                           wire:model="data.org_{{ $child->id }}"
                                           data-parent="{{ $parent->id }}"
                                           onchange="handleOrgChildChange({{ $parent->id }})"
                                           onclick="event.stopPropagation()">
                                    
                                    <!-- Icon hộ -->
                                    <svg class="org-checkbox-child-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    
                                    <!-- Label -->
                                    <span class="org-checkbox-child-label">
                                        {{ $child->name }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="org-checkbox-standalone">
                    <!-- Icon -->
                    <svg class="org-checkbox-parent-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    
                    <!-- Checkbox -->
                    <input type="checkbox" 
                           class="org-checkbox-parent-cb" 
                           id="org-cb-standalone-{{ $parent->id }}"
                           wire:model="data.org_{{ $parent->id }}"
                           onchange="updateTotalOrgCount()">
                    
                    <!-- Label -->
                    <span class="org-checkbox-parent-label">
                        {{ $parent->name }}
                    </span>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        // Khởi tạo state
        let isInitializing = true;
        let updateTimeouts = {};
        
        function toggleOrgParent(parentId) {
            try {
                const icon = document.getElementById('parent-icon-' + parentId);
                const header = document.getElementById('parent-header-' + parentId);
                const children = document.getElementById('parent-children-' + parentId);
                
                if (icon && header && children) {
                    const isExpanded = children.classList.contains('expanded');
                    
                    if (isExpanded) {
                        icon.classList.remove('expanded');
                        header.classList.remove('expanded');
                        children.classList.remove('expanded');
                    } else {
                        icon.classList.add('expanded');
                        header.classList.add('expanded');
                        children.classList.add('expanded');
                    }
                }
            } catch (e) {
                console.error('toggleOrgParent error:', e);
            }
        }
        
        function handleOrgParentChange(parentId) {
            if (isInitializing) return;
            
            try {
                const parentCb = document.getElementById('org-cb-parent-' + parentId);
                if (!parentCb) return;
                
                const parentContainer = document.getElementById('parent-children-' + parentId);
                if (!parentContainer) {
                    updateTotalOrgCount();
                    return;
                }
                
                const childCbs = parentContainer.querySelectorAll('input[type="checkbox"].org-checkbox-child-cb');
                
                if (childCbs.length > 0) {
                    const isChecked = parentCb.checked;
                    
                    // Temporarily block init to prevent loops
                    const wasInit = isInitializing;
                    isInitializing = true;
                    
                    // Click each checkbox to trigger proper Livewire binding
                    childCbs.forEach((child) => {
                        if (child.checked !== isChecked) {
                            child.click();
                        }
                    });
                    
                    // Restore init state
                    isInitializing = wasInit;
                    
                    parentCb.indeterminate = false;
                    
                    // Update UI counts after a brief delay
                    setTimeout(() => {
                        updateOrgChildCount(parentId);
                        updateTotalOrgCount();
                    }, 50);
                } else {
                    updateTotalOrgCount();
                }
            } catch (e) {
                console.error('handleOrgParentChange error:', e);
            }
        }
        
        function handleOrgChildChange(parentId) {
            if (isInitializing) return;
            
            try {
                const parentCb = document.getElementById('org-cb-parent-' + parentId);
                if (!parentCb) {
                    updateTotalOrgCount();
                    return;
                }
                
                const parentContainer = document.getElementById('parent-children-' + parentId);
                if (!parentContainer) return;
                
                const childCbs = parentContainer.querySelectorAll('input[type="checkbox"].org-checkbox-child-cb');
                
                if (childCbs.length > 0) {
                    const checkedCount = Array.from(childCbs).filter(cb => cb.checked).length;
                    const totalCount = childCbs.length;
                    
                    const prevChecked = parentCb.checked;
                    const prevIndeterminate = parentCb.indeterminate;
                    
                    if (checkedCount === 0) {
                        parentCb.checked = false;
                        parentCb.indeterminate = false;
                    } else if (checkedCount === totalCount) {
                        parentCb.checked = true;
                        parentCb.indeterminate = false;
                    } else {
                        parentCb.checked = false;
                        parentCb.indeterminate = true;
                    }
                    
                    // Sync parent state with Livewire if changed
                    if (prevChecked !== parentCb.checked || prevIndeterminate !== parentCb.indeterminate) {
                        setTimeout(() => {
                            try {
                                parentCb.dispatchEvent(new Event('input', { bubbles: true }));
                            } catch (e) {
                                console.error('Error dispatching parent input:', e);
                            }
                        }, 30);
                    }
                    
                    updateOrgChildCount(parentId);
                }
                
                updateTotalOrgCount();
            } catch (e) {
                console.error('handleOrgChildChange error:', e);
            }
        }
        
        function updateOrgChildCount(parentId) {
            try {
                const parentContainer = document.getElementById('parent-children-' + parentId);
                if (!parentContainer) return;
                
                const childCbs = parentContainer.querySelectorAll('input[type="checkbox"].org-checkbox-child-cb');
                const countBadge = document.getElementById('parent-count-' + parentId);
                
                if (childCbs && countBadge) {
                    const checkedCount = Array.from(childCbs).filter(cb => cb.checked).length;
                    const totalCount = childCbs.length;
                    
                    countBadge.textContent = checkedCount + '/' + totalCount;
                    
                    if (checkedCount > 0) {
                        countBadge.classList.add('has-selected');
                    } else {
                        countBadge.classList.remove('has-selected');
                    }
                }
            } catch (e) {
                console.error('updateOrgChildCount error:', e);
            }
        }
        
        function updateTotalOrgCount() {
            try {
                const treeContainer = document.querySelector('.org-checkbox-tree');
                if (!treeContainer) return;
                
                const allCbs = treeContainer.querySelectorAll('input[type="checkbox"]');
                const countEl = document.getElementById('selected-total-count');
                
                if (allCbs && countEl) {
                    const checkedCount = Array.from(allCbs).filter(cb => cb.checked && !cb.indeterminate).length;
                    countEl.textContent = checkedCount;
                }
            } catch (e) {
                console.error('updateTotalOrgCount error:', e);
            }
        }
        
        function expandAllOrgs() {
            try {
                const headers = document.querySelectorAll('.org-checkbox-parent-header');
                headers.forEach(header => {
                    const icon = header.querySelector('.org-checkbox-expand-icon');
                    const children = header.parentElement.querySelector('.org-checkbox-children');
                    
                    if (icon && children && !children.classList.contains('expanded')) {
                        icon.classList.add('expanded');
                        header.classList.add('expanded');
                        children.classList.add('expanded');
                    }
                });
            } catch (e) {
                console.error('expandAllOrgs error:', e);
            }
        }
        
        function collapseAllOrgs() {
            try {
                const headers = document.querySelectorAll('.org-checkbox-parent-header');
                headers.forEach(header => {
                    const icon = header.querySelector('.org-checkbox-expand-icon');
                    const children = header.parentElement.querySelector('.org-checkbox-children');
                    
                    if (icon && children && children.classList.contains('expanded')) {
                        icon.classList.remove('expanded');
                        header.classList.remove('expanded');
                        children.classList.remove('expanded');
                    }
                });
            } catch (e) {
                console.error('collapseAllOrgs error:', e);
            }
        }
        
        // Initialize khi page load
        function initOrgTree() {
            isInitializing = true;
            
            try {
                // Init tất cả parent-child relationships
                @foreach($parents as $parent)
                    @php
                        $children = \App\Models\OrganizationUnit::where('parent_id', $parent->id)->get();
                    @endphp
                    @if($children->count() > 0)
                        handleOrgChildChange({{ $parent->id }});
                        updateOrgChildCount({{ $parent->id }});
                    @endif
                @endforeach
                
                updateTotalOrgCount();
            } catch (e) {
                console.error('initOrgTree error:', e);
            }
            
            setTimeout(() => {
                isInitializing = false;
            }, 500);
        }
        
        // Init on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(initOrgTree, 800);
            });
        } else {
            setTimeout(initOrgTree, 800);
        }
        
        // Re-init on Livewire updates
        document.addEventListener('livewire:init', () => {
            setTimeout(() => {
                initOrgTree();
            }, 200);
            
            Livewire.hook('morph.updated', ({ component, cleanup }) => {
                setTimeout(initOrgTree, 150);
            });
        });
    </script>
</div>
