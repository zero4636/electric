<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class OrganizationTree extends Field
{
    protected string $view = 'filament.forms.components.organization-tree';

    protected array | \Closure $options = [];

    public function options(array | callable $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        $options = $this->evaluate($this->options);

        if (is_array($options)) {
            return $options;
        }

        return [];
    }
}
