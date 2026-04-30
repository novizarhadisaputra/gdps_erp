<?php

namespace Modules\CRM\Livewire\CostingTemplate;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas\CostingTemplateItemForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Tables\CostingTemplateItemsTable;
use Modules\CRM\Models\CostingTemplate;

#[Lazy]
class ManageCostingItems extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    #[Locked]
    public CostingTemplate $record;

    public function mount(CostingTemplate $record): void
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return CostingTemplateItemsTable::configure(
            $table->query($this->record->costingTemplateItems()->getQuery())
        )
            ->headerActions([
                CreateAction::make()
                    ->schema(CostingTemplateItemForm::schema())
                    ->slideOver()
                    ->after(fn () => $this->dispatch('costing-items-updated')),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(CostingTemplateItemForm::schema())
                    ->slideOver()
                    ->after(fn () => $this->dispatch('costing-items-updated')),
                DeleteAction::make()
                    ->after(fn () => $this->dispatch('costing-items-updated')),
            ])
            ->deferLoading();
    }

    public function render()
    {
        return view('crm::livewire.costing-template.manage-costing-items');
    }
}
