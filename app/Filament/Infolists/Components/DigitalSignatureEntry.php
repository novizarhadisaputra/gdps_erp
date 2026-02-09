<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Support\Collection;

class DigitalSignatureEntry extends Entry
{
    protected string $view = 'filament.infolists.components.digital-signature-entry';

    public function getSignatures(): Collection
    {
        return $this->getRecord()->signatures()->get();
    }
}
