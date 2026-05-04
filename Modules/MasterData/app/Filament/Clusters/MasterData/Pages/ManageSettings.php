<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Pages;

use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\MasterData\Models\AppSetting;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = null;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected string $view = 'masterdata::filament.pages.manage-settings';

    protected static ?string $title = 'App Settings';

    protected static ?string $navigationLabel = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = AppSetting::all();

        $formattedData = [];
        foreach ($settings as $setting) {
            $formattedData[$setting->group][] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'payload' => $setting->payload,
                'is_active' => $setting->is_active,
            ];
        }

        $this->form->fill(['settings' => $formattedData]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Global Configuration')
                    ->description('Manage dynamic application settings using JSON-based payloads.')
                    ->schema([
                        Tabs::make('Groups')
                            ->tabs([
                                $this->getGroupTab('general', 'General'),
                                $this->getGroupTab('integration', 'Integrations'),
                                $this->getGroupTab('custom', 'Custom'),
                            ])
                            ->persistTabInQueryString(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getGroupTab(string $group, string $label): Tabs\Tab
    {
        return Tabs\Tab::make($label)
            ->schema([
                Repeater::make("settings.{$group}")
                    ->label(false)
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->distinct()
                            ->disableLabel()
                            ->placeholder('Setting Key (e.g. google_analytics_id)')
                            ->columnSpan(2),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(1),
                        KeyValue::make('payload')
                            ->label('JSON Payload')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add New Setting')
                    ->collapsible()
                    ->collapsed(fn ($state) => ! empty($state['key']))
                    ->itemLabel(fn (array $state): ?string => $state['key'] ?? null),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState()['settings'];

        $existingIds = [];

        foreach ($data as $group => $settings) {
            foreach ($settings as $settingData) {
                $setting = AppSetting::updateOrCreate(
                    ['key' => $settingData['key']],
                    [
                        'group' => $group,
                        'payload' => $settingData['payload'] ?? [],
                        'is_active' => $settingData['is_active'] ?? true,
                    ]
                );
                $existingIds[] = $setting->id;
            }
        }

        // Clean up removed settings
        AppSetting::whereNotIn('id', $existingIds)->delete();

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
