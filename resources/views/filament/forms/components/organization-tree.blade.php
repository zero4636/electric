<div class="organization-tree-wrapper" id="org-tree-{{ $getId() }}"><div class="organization-tree-wrapper" id="org-tree-{{ $getId() }}"><div 

    <style>

        #org-tree-{{ $getId() }} .org-tree {    <style>    x-data="orgTree(@js($getState() ?? []), @js($getOptions()))" 

            border: 1px solid #e5e7eb;

            border-radius: 8px;        .org-tree {    x-init="init()"

            background: #ffffff;

            overflow: hidden;            border: 1px solid #e5e7eb;    x-cloak

        }

                    border-radius: 8px;    class="fi-fo-field-wrp">

        #org-tree-{{ $getId() }} .org-parent {

            border-bottom: 1px solid #f3f4f6;            background: #ffffff;    

        }

                    overflow: hidden;    <!-- Header -->

        #org-tree-{{ $getId() }} .org-parent:last-child {

            border-bottom: none;        }    <div class="flex items-center justify-between mb-3 px-1">

        }

                        <div class="text-sm text-gray-600 dark:text-gray-400">

        #org-tree-{{ $getId() }} .org-parent-header {

            display: flex;        .org-parent {            <span x-text="getSelectedCount()"></span> đơn vị được chọn

            align-items: center;

            padding: 12px 16px;            border-bottom: 1px solid #f3f4f6;        </div>

            background: #f9fafb;

            cursor: pointer;        }        <div class="flex gap-3 text-xs">

            transition: background 0.2s;

            user-select: none;                    <button 

        }

                .org-parent:last-child {                type="button"

        #org-tree-{{ $getId() }} .org-parent-header:hover {

            background: #f3f4f6;            border-bottom: none;                @click="expandAll()"

        }

                }                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium transition"

        #org-tree-{{ $getId() }} .org-parent-header.expanded {

            background: #eff6ff;                    >

        }

                .org-parent-header {                Mở tất cả

        #org-tree-{{ $getId() }} .org-expand-icon {

            width: 20px;            display: flex;            </button>

            height: 20px;

            margin-right: 8px;            align-items: center;            <button 

            transition: transform 0.2s;

            flex-shrink: 0;            padding: 12px 16px;                type="button"

            color: #6b7280;

        }            background: #f9fafb;                @click="collapseAll()"

        

        #org-tree-{{ $getId() }} .org-expand-icon.expanded {            cursor: pointer;                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium transition"

            transform: rotate(90deg);

            color: #3b82f6;            transition: background 0.2s;            >

        }

                    user-select: none;                Đóng tất cả

        #org-tree-{{ $getId() }} .org-parent-checkbox {

            margin-right: 12px;        }            </button>

            width: 18px;

            height: 18px;                </div>

            cursor: pointer;

            accent-color: #3b82f6;        .org-parent-header:hover {    </div>

        }

                    background: #f3f4f6;    

        #org-tree-{{ $getId() }} .org-parent-label {

            font-weight: 600;        }    <!-- Tree -->

            color: #1f2937;

            flex: 1;            <div class="rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden">

        }

                .org-parent-header.expanded {        <div class="max-h-[500px] overflow-y-auto p-3 space-y-1">

        #org-tree-{{ $getId() }} .org-count {

            font-size: 12px;            background: #eff6ff;            

            color: #6b7280;

            background: #e5e7eb;        }            <template x-for="org in orgs" :key="org.id">

            padding: 2px 8px;

            border-radius: 12px;                        <div>

            margin-left: 8px;

        }        .org-expand-icon {                    <!-- Parent -->

        

        #org-tree-{{ $getId() }} .org-count.has-selected {            width: 20px;                    <div 

            background: #dbeafe;

            color: #1e40af;            height: 20px;                        class="flex items-center gap-2 py-2 px-3 rounded-lg"

        }

                    margin-right: 8px;                        :class="isSelected(org.id) ? 'bg-primary-50 dark:bg-primary-500/10' : 'hover:bg-gray-50 dark:hover:bg-white/5'"

        #org-tree-{{ $getId() }} .org-children {

            max-height: 0;            transition: transform 0.2s;                    >

            overflow: hidden;

            transition: max-height 0.3s ease-out;            flex-shrink: 0;                        <button 

            background: #ffffff;

        }            color: #6b7280;                            type="button"

        

        #org-tree-{{ $getId() }} .org-children.expanded {        }                            @click="org.expanded = !org.expanded"

            max-height: 2000px;

            transition: max-height 0.5s ease-in;                                    class="w-5 h-5 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-white/10"

        }

                .org-expand-icon.expanded {                            :class="org.children.length === 0 ? 'invisible' : ''"

        #org-tree-{{ $getId() }} .org-children-grid {

            display: grid;            transform: rotate(90deg);                        >

            grid-template-columns: repeat(2, 1fr);

            gap: 8px;            color: #3b82f6;                            <svg 

            padding: 12px 16px 12px 44px;

        }        }                                class="w-4 h-4 text-gray-500 transition-transform"

        

        #org-tree-{{ $getId() }} .org-child-item {                                        :class="{ 'rotate-90': org.expanded }"

            display: flex;

            align-items: center;        .org-parent-checkbox {                                fill="none" stroke="currentColor" viewBox="0 0 24 24"

            padding: 8px;

            border-radius: 6px;            margin-right: 12px;                            >

            transition: background 0.15s;

        }            width: 18px;                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>

        

        #org-tree-{{ $getId() }} .org-child-item:hover {            height: 18px;                            </svg>

            background: #f9fafb;

        }            cursor: pointer;                        </button>

        

        #org-tree-{{ $getId() }} .org-child-checkbox {            accent-color: #3b82f6;                        

            margin-right: 8px;

            width: 16px;        }                        <div class="relative">

            height: 16px;

            cursor: pointer;                                    <input 

            accent-color: #3b82f6;

        }        .org-parent-label {                                type="checkbox"

        

        #org-tree-{{ $getId() }} .org-child-label {            font-weight: 600;                                :checked="isSelected(org.id)"

            font-size: 14px;

            color: #374151;            color: #1f2937;                                @change="toggleParent(org, $event.target.checked)"

            cursor: pointer;

            user-select: none;            flex: 1;                                class="w-5 h-5 rounded border-gray-300 dark:border-white/20 text-primary-600 focus:ring-primary-500 cursor-pointer"

        }

                }                            >

        #org-tree-{{ $getId() }} .org-standalone {

            padding: 12px 16px;                                    <template x-if="isIndeterminate(org)">

            border-bottom: 1px solid #f3f4f6;

            display: flex;        .org-count {                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">

            align-items: center;

        }            font-size: 12px;                                    <div class="w-3 h-0.5 bg-primary-600 rounded"></div>

        

        #org-tree-{{ $getId() }} .org-standalone:last-child {            color: #6b7280;                                </div>

            border-bottom: none;

        }            background: #e5e7eb;                            </template>

        

        #org-tree-{{ $getId() }} .org-standalone:hover {            padding: 2px 8px;                        </div>

            background: #f9fafb;

        }            border-radius: 12px;                        

        

        #org-tree-{{ $getId() }} .org-header-controls {            margin-left: 8px;                        <div class="flex items-center gap-2 flex-1">

            display: flex;

            justify-content: space-between;        }                            <svg class="w-5 h-5" :class="org.expanded ? 'text-primary-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">

            align-items: center;

            padding: 12px 16px;                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>

            background: #f9fafb;

            border-bottom: 2px solid #e5e7eb;        .org-count.has-selected {                            </svg>

        }

                    background: #dbeafe;                            <span class="font-semibold text-gray-900 dark:text-white" x-text="org.name"></span>

        #org-tree-{{ $getId() }} .org-selected-count {

            font-size: 14px;            color: #1e40af;                            <template x-if="org.children.length > 0">

            color: #6b7280;

        }        }                                <span 

        

        #org-tree-{{ $getId() }} .org-selected-count strong {                                            class="text-xs px-2 py-0.5 rounded-full"

            color: #3b82f6;

            font-weight: 600;        .org-children {                                    :class="isSelected(org.id) ? 'bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400'"

        }

                    max-height: 0;                                    x-text="org.children.length"

        #org-tree-{{ $getId() }} .org-controls {

            display: flex;            overflow: hidden;                                ></span>

            gap: 12px;

        }            transition: max-height 0.3s ease-out;                            </template>

        

        #org-tree-{{ $getId() }} .org-btn {            background: #ffffff;                        </div>

            font-size: 12px;

            color: #3b82f6;        }                    </div>

            background: none;

            border: none;                            

            cursor: pointer;

            padding: 4px 8px;        .org-children.expanded {                    <!-- Children -->

            border-radius: 4px;

            transition: all 0.15s;            max-height: 2000px;                    <template x-if="org.expanded">

        }

                    transition: max-height 0.5s ease-in;                        <div class="ml-7 border-l-2 border-gray-200 dark:border-white/10 pl-3 mt-1 space-y-0.5">

        #org-tree-{{ $getId() }} .org-btn:hover {

            background: #dbeafe;        }                            <template x-for="child in org.children" :key="child.id">

            color: #1e40af;

        }                                        <div 

    </style>

        .org-children-grid {                                    class="flex items-center gap-2 py-2 px-3 rounded-lg"

    <div class="org-tree">

        <div class="org-header-controls">            display: grid;                                    :class="isSelected(child.id) ? 'bg-primary-50 dark:bg-primary-500/10' : 'hover:bg-gray-50 dark:hover:bg-white/5'"

            <div class="org-selected-count">

                <strong id="selected-count-{{ $getId() }}">0</strong> đơn vị được chọn            grid-template-columns: repeat(2, 1fr);                                >

            </div>

            <div class="org-controls">            gap: 8px;                                    <input 

                <button type="button" class="org-btn" onclick="orgTreeExpandAll('{{ $getId() }}')">

                    ⊕ Mở tất cả            padding: 12px 16px 12px 44px;                                        type="checkbox"

                </button>

                <button type="button" class="org-btn" onclick="orgTreeCollapseAll('{{ $getId() }}')">        }                                        :checked="isSelected(child.id)"

                    ⊖ Đóng tất cả

                </button>                                                @change="toggleChild(org, child.id, $event.target.checked)"

            </div>

        </div>        .org-child-item {                                        class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-primary-600 focus:ring-primary-500 cursor-pointer"



        @foreach($getOptions() as $parent)            display: flex;                                    >

            @if(count($parent['children']) > 0)

                <div class="org-parent">            align-items: center;                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">

                    <div class="org-parent-header" 

                         onclick="orgTreeToggleParent('{{ $getId() }}', {{ $parent['id'] }})"            padding: 8px;                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>

                         id="header-{{ $getId() }}-{{ $parent['id'] }}">

                        <svg class="org-expand-icon" id="icon-{{ $getId() }}-{{ $parent['id'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">            border-radius: 6px;                                    </svg>

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>

                        </svg>            transition: background 0.15s;                                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="child.name"></span>

                        

                        <input type="checkbox"         }                                </div>

                               class="org-parent-checkbox" 

                               id="parent-{{ $getId() }}-{{ $parent['id'] }}"                                    </template>

                               wire:model="data.org_{{ $parent['id'] }}"

                               onchange="orgTreeHandleParentChange('{{ $getId() }}', {{ $parent['id'] }})"        .org-child-item:hover {                        </div>

                               onclick="event.stopPropagation()">

                                    background: #f9fafb;                    </template>

                        <label for="parent-{{ $getId() }}-{{ $parent['id'] }}" class="org-parent-label" onclick="event.stopPropagation()">

                            {{ $parent['name'] }}        }                </div>

                        </label>

                                            </template>

                        <span class="org-count" id="count-{{ $getId() }}-{{ $parent['id'] }}">

                            0/{{ count($parent['children']) }}        .org-child-checkbox {            

                        </span>

                    </div>            margin-right: 8px;            <template x-if="orgs.length === 0">

                    

                    <div class="org-children" id="children-{{ $getId() }}-{{ $parent['id'] }}">            width: 16px;                <div class="text-center py-12">

                        <div class="org-children-grid">

                            @foreach($parent['children'] as $child)            height: 16px;                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <div class="org-child-item">

                                    <input type="checkbox"             cursor: pointer;                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>

                                           class="org-child-checkbox child-of-{{ $getId() }}-{{ $parent['id'] }}" 

                                           id="child-{{ $getId() }}-{{ $child['id'] }}"            accent-color: #3b82f6;                    </svg>

                                           wire:model="data.org_{{ $child['id'] }}"

                                           data-parent="{{ $parent['id'] }}"        }                    <p class="text-sm text-gray-500">Không có đơn vị nào</p>

                                           onchange="orgTreeHandleChildChange('{{ $getId() }}', {{ $parent['id'] }})">

                                                            </div>

                                    <label for="child-{{ $getId() }}-{{ $child['id'] }}" class="org-child-label">

                                        {{ $child['name'] }}        .org-child-label {            </template>

                                    </label>

                                </div>            font-size: 14px;            

                            @endforeach

                        </div>            color: #374151;        </div>

                    </div>

                </div>            cursor: pointer;    </div>

            @else

                <div class="org-standalone">            user-select: none;    

                    <input type="checkbox" 

                           class="org-parent-checkbox"         }    <input type="hidden" wire:model="{{ $getStatePath() }}" :value="JSON.stringify(Array.from(selected))">

                           id="standalone-{{ $getId() }}-{{ $parent['id'] }}"

                           wire:model="data.org_{{ $parent['id'] }}"        </div>

                           onchange="orgTreeUpdateCount('{{ $getId() }}')">

                            .org-standalone {

                    <label for="standalone-{{ $getId() }}-{{ $parent['id'] }}" class="org-parent-label">

                        {{ $parent['name'] }}            padding: 12px 16px;<script>

                    </label>

                </div>            border-bottom: 1px solid #f3f4f6;function orgTree(initialSelected, allOrgs) {

            @endif

        @endforeach            display: flex;    return {

    </div>

            align-items: center;        orgs: [],

    <script>

        function orgTreeToggleParent(treeId, parentId) {        }        selected: new Set(initialSelected || []),

            const icon = document.getElementById('icon-' + treeId + '-' + parentId);

            const header = document.getElementById('header-' + treeId + '-' + parentId);                

            const children = document.getElementById('children-' + treeId + '-' + parentId);

                    .org-standalone:last-child {        init() {

            if (icon && header && children) {

                icon.classList.toggle('expanded');            border-bottom: none;            const map = new Map();

                header.classList.toggle('expanded');

                children.classList.toggle('expanded');        }            const roots = [];

            }

        }                    

        

        function orgTreeHandleParentChange(treeId, parentId) {        .org-standalone:hover {            allOrgs.forEach(o => map.set(o.id, { ...o, children: [], expanded: false }));

            const parentCheckbox = document.getElementById('parent-' + treeId + '-' + parentId);

            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);            background: #f9fafb;            

            

            if (parentCheckbox && childCheckboxes) {        }            allOrgs.forEach(o => {

                childCheckboxes.forEach(child => {

                    child.checked = parentCheckbox.checked;                        const node = map.get(o.id);

                    child.dispatchEvent(new Event('change'));

                });        .org-header-controls {                if (o.parent_id && map.has(o.parent_id)) {

                

                parentCheckbox.indeterminate = false;            display: flex;                    map.get(o.parent_id).children.push(node);

                orgTreeUpdateChildCount(treeId, parentId);

                orgTreeUpdateCount(treeId);            justify-content: space-between;                } else {

            }

        }            align-items: center;                    roots.push(node);

        

        function orgTreeHandleChildChange(treeId, parentId) {            padding: 12px 16px;                }

            const parentCheckbox = document.getElementById('parent-' + treeId + '-' + parentId);

            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);            background: #f9fafb;            });

            

            if (parentCheckbox && childCheckboxes) {            border-bottom: 2px solid #e5e7eb;            

                const checkedCount = Array.from(childCheckboxes).filter(cb => cb.checked).length;

                const totalCount = childCheckboxes.length;        }            this.orgs = roots.sort((a, b) => a.name.localeCompare(b.name));

                

                if (checkedCount === 0) {                    this.orgs.forEach(o => o.children.sort((a, b) => a.name.localeCompare(b.name)));

                    parentCheckbox.checked = false;

                    parentCheckbox.indeterminate = false;        .org-selected-count {            

                } else if (checkedCount === totalCount) {

                    parentCheckbox.checked = true;            font-size: 14px;            this.orgs.forEach(o => {

                    parentCheckbox.indeterminate = false;

                } else {            color: #6b7280;                if (o.children.some(c => this.selected.has(c.id)) || this.selected.has(o.id)) {

                    parentCheckbox.checked = false;

                    parentCheckbox.indeterminate = true;        }                    o.expanded = true;

                }

                                        }

                parentCheckbox.dispatchEvent(new Event('change'));

                orgTreeUpdateChildCount(treeId, parentId);        .org-selected-count strong {            });

                orgTreeUpdateCount(treeId);

            }            color: #3b82f6;        },

        }

                    font-weight: 600;        

        function orgTreeUpdateChildCount(treeId, parentId) {

            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);        }        expandAll() {

            const countLabel = document.getElementById('count-' + treeId + '-' + parentId);

                                this.orgs.forEach(o => o.expanded = true);

            if (childCheckboxes && countLabel) {

                const checkedCount = Array.from(childCheckboxes).filter(cb => cb.checked).length;        .org-controls {        },

                const totalCount = childCheckboxes.length;

                            display: flex;        

                countLabel.textContent = checkedCount + '/' + totalCount;

                if (checkedCount > 0) {            gap: 12px;        collapseAll() {

                    countLabel.classList.add('has-selected');

                } else {        }            this.orgs.forEach(o => o.expanded = false);

                    countLabel.classList.remove('has-selected');

                }                },

            }

        }        .org-btn {        

        

        function orgTreeUpdateCount(treeId) {            font-size: 12px;        toggleParent(org, checked) {

            const allCheckboxes = document.querySelectorAll('#org-tree-' + treeId + ' input[type="checkbox"]');

            const countElement = document.getElementById('selected-count-' + treeId);            color: #3b82f6;            if (checked) {

            

            if (allCheckboxes && countElement) {            background: none;                this.selected.add(org.id);

                const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;

                countElement.textContent = checkedCount;            border: none;                org.children.forEach(c => this.selected.add(c.id));

            }

        }            cursor: pointer;            } else {

        

        function orgTreeExpandAll(treeId) {            padding: 4px 8px;                this.selected.delete(org.id);

            const headers = document.querySelectorAll('#org-tree-' + treeId + ' .org-parent-header');

            headers.forEach(header => {            border-radius: 4px;                org.children.forEach(c => this.selected.delete(c.id));

                const icon = header.querySelector('.org-expand-icon');

                const children = header.parentElement.querySelector('.org-children');            transition: all 0.15s;            }

                

                if (icon && children && !children.classList.contains('expanded')) {        }            this.sync();

                    icon.classList.add('expanded');

                    header.classList.add('expanded');                },

                    children.classList.add('expanded');

                }        .org-btn:hover {        

            });

        }            background: #dbeafe;        toggleChild(parent, childId, checked) {

        

        function orgTreeCollapseAll(treeId) {            color: #1e40af;            if (checked) {

            const headers = document.querySelectorAll('#org-tree-' + treeId + ' .org-parent-header');

            headers.forEach(header => {        }                this.selected.add(childId);

                const icon = header.querySelector('.org-expand-icon');

                const children = header.parentElement.querySelector('.org-children');    </style>            } else {

                

                if (icon && children && children.classList.contains('expanded')) {                this.selected.delete(childId);

                    icon.classList.remove('expanded');

                    header.classList.remove('expanded');    <div class="org-tree">            }

                    children.classList.remove('expanded');

                }        <div class="org-header-controls">            

            });

        }            <div class="org-selected-count">            const count = parent.children.filter(c => this.selected.has(c.id)).length;

        

        // Initialize on page load                <strong id="selected-count-{{ $getId() }}">0</strong> đơn vị được chọn            if (count === parent.children.length) {

        document.addEventListener('DOMContentLoaded', function() {

            const treeId = '{{ $getId() }}';            </div>                this.selected.add(parent.id);

            

            @foreach($getOptions() as $parent)            <div class="org-controls">            } else {

                @if(count($parent['children']) > 0)

                    setTimeout(function() {                <button type="button" class="org-btn" onclick="orgTreeExpandAll('{{ $getId() }}')">                this.selected.delete(parent.id);

                        orgTreeHandleChildChange(treeId, {{ $parent['id'] }});

                    }, 100);                    ⊕ Mở tất cả            }

                @endif

            @endforeach                </button>            this.sync();

            

            setTimeout(function() {                <button type="button" class="org-btn" onclick="orgTreeCollapseAll('{{ $getId() }}')">        },

                orgTreeUpdateCount(treeId);

            }, 150);                    ⊖ Đóng tất cả        

        });

    </script>                </button>        isSelected(id) {

</div>

            </div>            return this.selected.has(id);

        </div>        },

        

        @foreach($getOptions() as $parent)        isIndeterminate(org) {

            @if(count($parent['children']) > 0)            if (!org.children.length) return false;

                <div class="org-parent">            const count = org.children.filter(c => this.selected.has(c.id)).length;

                    <div class="org-parent-header"             return count > 0 && count < org.children.length;

                         onclick="orgTreeToggleParent('{{ $getId() }}', {{ $parent['id'] }})"        },

                         id="header-{{ $getId() }}-{{ $parent['id'] }}">        

                        <svg class="org-expand-icon" id="icon-{{ $getId() }}-{{ $parent['id'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">        getSelectedCount() {

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>            return this.selected.size;

                        </svg>        },

                                

                        <input type="checkbox"         sync() {

                               class="org-parent-checkbox"             this.$nextTick(() => {

                               id="parent-{{ $getId() }}-{{ $parent['id'] }}"                this.$root.querySelector('input[type="hidden"]').dispatchEvent(new Event('input', { bubbles: true }));

                               wire:model="data.org_{{ $parent['id'] }}"            });

                               onchange="orgTreeHandleParentChange('{{ $getId() }}', {{ $parent['id'] }})"        }

                               onclick="event.stopPropagation()">    }

                        }

                        <label for="parent-{{ $getId() }}-{{ $parent['id'] }}" class="org-parent-label" onclick="event.stopPropagation()"></script>

                            {{ $parent['name'] }}
                        </label>
                        
                        <span class="org-count" id="count-{{ $getId() }}-{{ $parent['id'] }}">
                            0/{{ count($parent['children']) }}
                        </span>
                    </div>
                    
                    <div class="org-children" id="children-{{ $getId() }}-{{ $parent['id'] }}">
                        <div class="org-children-grid">
                            @foreach($parent['children'] as $child)
                                <div class="org-child-item">
                                    <input type="checkbox" 
                                           class="org-child-checkbox child-of-{{ $getId() }}-{{ $parent['id'] }}" 
                                           id="child-{{ $getId() }}-{{ $child['id'] }}"
                                           wire:model="data.org_{{ $child['id'] }}"
                                           data-parent="{{ $parent['id'] }}"
                                           onchange="orgTreeHandleChildChange('{{ $getId() }}', {{ $parent['id'] }})">
                                    
                                    <label for="child-{{ $getId() }}-{{ $child['id'] }}" class="org-child-label">
                                        {{ $child['name'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="org-standalone">
                    <input type="checkbox" 
                           class="org-parent-checkbox" 
                           id="standalone-{{ $getId() }}-{{ $parent['id'] }}"
                           wire:model="data.org_{{ $parent['id'] }}"
                           onchange="orgTreeUpdateCount('{{ $getId() }}')">
                    
                    <label for="standalone-{{ $getId() }}-{{ $parent['id'] }}" class="org-parent-label">
                        {{ $parent['name'] }}
                    </label>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        function orgTreeToggleParent(treeId, parentId) {
            const icon = document.getElementById('icon-' + treeId + '-' + parentId);
            const header = document.getElementById('header-' + treeId + '-' + parentId);
            const children = document.getElementById('children-' + treeId + '-' + parentId);
            
            icon.classList.toggle('expanded');
            header.classList.toggle('expanded');
            children.classList.toggle('expanded');
        }
        
        function orgTreeHandleParentChange(treeId, parentId) {
            const parentCheckbox = document.getElementById('parent-' + treeId + '-' + parentId);
            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);
            
            // Tick/untick tất cả con theo parent
            childCheckboxes.forEach(child => {
                child.checked = parentCheckbox.checked;
                // Trigger Livewire update
                child.dispatchEvent(new Event('change'));
            });
            
            // Xóa indeterminate state
            parentCheckbox.indeterminate = false;
            
            orgTreeUpdateChildCount(treeId, parentId);
            orgTreeUpdateCount(treeId);
        }
        
        function orgTreeHandleChildChange(treeId, parentId) {
            const parentCheckbox = document.getElementById('parent-' + treeId + '-' + parentId);
            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);
            
            const checkedCount = Array.from(childCheckboxes).filter(cb => cb.checked).length;
            const totalCount = childCheckboxes.length;
            
            if (checkedCount === 0) {
                // Không có con nào được chọn
                parentCheckbox.checked = false;
                parentCheckbox.indeterminate = false;
            } else if (checkedCount === totalCount) {
                // Tất cả con được chọn
                parentCheckbox.checked = true;
                parentCheckbox.indeterminate = false;
            } else {
                // Một số con được chọn (indeterminate state)
                parentCheckbox.checked = false;
                parentCheckbox.indeterminate = true;
            }
            
            // Trigger Livewire update for parent
            parentCheckbox.dispatchEvent(new Event('change'));
            
            orgTreeUpdateChildCount(treeId, parentId);
            orgTreeUpdateCount(treeId);
        }
        
        function orgTreeUpdateChildCount(treeId, parentId) {
            const childCheckboxes = document.querySelectorAll('.child-of-' + treeId + '-' + parentId);
            const checkedCount = Array.from(childCheckboxes).filter(cb => cb.checked).length;
            const totalCount = childCheckboxes.length;
            const countLabel = document.getElementById('count-' + treeId + '-' + parentId);
            
            if (countLabel) {
                countLabel.textContent = checkedCount + '/' + totalCount;
                if (checkedCount > 0) {
                    countLabel.classList.add('has-selected');
                } else {
                    countLabel.classList.remove('has-selected');
                }
            }
        }
        
        function orgTreeUpdateCount(treeId) {
            const allCheckboxes = document.querySelectorAll('#org-tree-' + treeId + ' input[type="checkbox"]');
            const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
            const countElement = document.getElementById('selected-count-' + treeId);
            
            if (countElement) {
                countElement.textContent = checkedCount;
            }
        }
        
        function orgTreeExpandAll(treeId) {
            const headers = document.querySelectorAll('#org-tree-' + treeId + ' .org-parent-header');
            headers.forEach(header => {
                const icon = header.querySelector('.org-expand-icon');
                const children = header.parentElement.querySelector('.org-children');
                
                if (icon && children && !children.classList.contains('expanded')) {
                    icon.classList.add('expanded');
                    header.classList.add('expanded');
                    children.classList.add('expanded');
                }
            });
        }
        
        function orgTreeCollapseAll(treeId) {
            const headers = document.querySelectorAll('#org-tree-' + treeId + ' .org-parent-header');
            headers.forEach(header => {
                const icon = header.querySelector('.org-expand-icon');
                const children = header.parentElement.querySelector('.org-children');
                
                if (icon && children && children.classList.contains('expanded')) {
                    icon.classList.remove('expanded');
                    header.classList.remove('expanded');
                    children.classList.remove('expanded');
                }
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const treeId = '{{ $getId() }}';
            
            // Initialize all child counts and indeterminate states
            @foreach($getOptions() as $parent)
                @if(count($parent['children']) > 0)
                    orgTreeHandleChildChange(treeId, {{ $parent['id'] }});
                @endif
            @endforeach
            
            // Update total count
            orgTreeUpdateCount(treeId);
        });
    </script>
</div>
