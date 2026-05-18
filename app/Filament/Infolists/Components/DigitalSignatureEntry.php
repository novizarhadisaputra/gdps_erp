<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Support\Collection;

class DigitalSignatureEntry extends Entry
{
    protected string $view = 'filament.infolists.components.digital-signature-entry';

    public function getSignatures(): Collection
    {
        $record = $this->getRecord();

        if (! $record) {
            return collect();
        }

        return $record->signatures()->get();
    }
}
