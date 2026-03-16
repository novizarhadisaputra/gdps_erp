<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;

class ManageContracts extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage contracts associated with this lead.';
    }

    protected static string $relationship = 'contracts';

    protected static ?string $relatedResource = ContractResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $title = 'Contracts';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'negotiation',
            'contract',
            'won',
            'closed_lost',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ContractResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ContractResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => ContractResource::form($schema))
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        // Try to find the latest proposal linked to this lead
                        $latestProposal = $record->proposals()->latest('created_at')->first();

                        return [
                            'customer_id' => $record->customer_id,
                            'work_scheme_id' => $record->work_scheme_id,
                            'proposal_id' => $latestProposal?->id,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $record = $this->getOwnerRecord();
                        $data['customer_id'] = $record->customer_id;
                        $data['work_scheme_id'] = $record->work_scheme_id;
                        $data['lead_id'] = $record->id;

                        return $data;
                    }),
            ]);
    }
}
