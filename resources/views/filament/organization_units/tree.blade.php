<div style="font-family: Arial, sans-serif;">
    <h2>{{ \Filament\Facades\Filament::translate('Cây Đơn vị tổ chức') ?? 'Cây Đơn vị tổ chức' }}</h2>

    @php
        // build tree from flat list
        $items = \App\Models\OrganizationUnit::all();
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->parent_id ?? 0][] = $item;
        }

        $render = function($parentId) use (&$render, $grouped) {
            if (!isset($grouped[$parentId])) {
                return;
            }
            echo '<ul style="list-style: none; margin-left: 1rem;">';
            foreach ($grouped[$parentId] as $node) {
                echo '<li style="margin: 0.25rem 0;">';
                echo '<strong>' . str_repeat('&nbsp;&nbsp;&nbsp;', $node->depth) . '</strong>';
                echo '<span>' . e($node->name) . '</span>';
                // small meta
                echo ' <small style="color:#666">(' . e($node->code) . ')</small>';
                // recurse
                $render($node->id);
                echo '</li>';
            }
            echo '</ul>';
        };
    @endphp

    <div>
        @php $render(0); @endphp
    </div>
</div>