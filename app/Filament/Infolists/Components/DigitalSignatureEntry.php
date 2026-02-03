<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class DigitalSignatureEntry extends Entry
{
    protected string $view = 'filament.infolists.components.digital-signature-entry';

    public function getSignatures(): \Illuminate\Support\Collection
    {
        return $this->getRecord()->signatures()->get();
    }
}
