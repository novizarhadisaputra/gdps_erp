<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\CRM\Models\GeneralInformationPic;

class EditGeneralInformation extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = GeneralInformationResource::class;

    public function getSubheading(): ?string
    {
        return 'Modify project general information (only for records in Draft or Rejected status).';
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->getRecord()->isLocked()) {
            \Filament\Notifications\Notification::make()
                ->title(__('Access Locked'))
                ->body('General Information that has been submitted or approved cannot be modified.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['pics'] = $this->getRecord()->pics()->get()->map(fn ($pic) => [
            'id' => $pic->id,
            'name' => $pic->name,
            'phone' => $pic->phone,
            'email' => $pic->email,
            'job_position_id' => $pic->job_position_id,
            'contact_role_id' => $pic->contact_role_id,
        ])->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $formPics = collect($this->data['pics'] ?? []);

        $submittedIds = $formPics->pluck('id')->filter()->values()->toArray();

        // Delete pics that were removed from the form
        $record->pics()->whereNotIn('id', $submittedIds)->delete();

        // Upsert remaining pics
        foreach ($formPics as $picData) {
            if (! empty($picData['id'])) {
                GeneralInformationPic::where('id', $picData['id'])->update([
                    'name' => $picData['name'],
                    'phone' => $picData['phone'] ?? null,
                    'email' => $picData['email'] ?? null,
                    'job_position_id' => $picData['job_position_id'] ?? null,
                    'contact_role_id' => $picData['contact_role_id'] ?? null,
                ]);
            } else {
                $record->pics()->create([
                    'name' => $picData['name'],
                    'phone' => $picData['phone'] ?? null,
                    'email' => $picData['email'] ?? null,
                    'job_position_id' => $picData['job_position_id'] ?? null,
                    'contact_role_id' => $picData['contact_role_id'] ?? null,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord(), 'lead' => $this->parentRecord]);
    }
}
