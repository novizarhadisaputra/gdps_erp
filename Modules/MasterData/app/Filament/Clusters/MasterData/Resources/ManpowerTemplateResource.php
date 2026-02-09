<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplates\Pages;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ManpowerTemplate;
use UnitEnum;

class ManpowerTemplateResource extends Resource
{
    protected static ?string $model = ManpowerTemplate::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Costing & Referentials';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Template Details')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('project_area_id')
                            ->relationship('projectArea', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Determines UMK and Risk Level for costing.'),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ]),

                Section::make('Manpower Composition')
                    ->description('Define the job positions and quantities for this template.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Job Positions')
                            ->schema([
                                Select::make('job_position_id')
                                    ->label('Job Position')
                                    ->relationship('jobPosition', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->createOptionForm(JobPositionForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('notes')
                                    ->label('Notes (Optional)')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Job Position')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Trigger recalculation of simulation
                                $set('simulation_trigger', uniqid());
                            }),
                    ]),

                Section::make('Cost Simulation (Estimasi Biaya)')
                    ->description('Rincian perhitungan biaya berdasarkan Area dan Jabatan yang dipilih.')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('cost_simulation_table')
                            ->label('Rincian Biaya per Bulan (Estimasi)')
                            ->html()
                            ->state(function (Get $get) {
                                $items = $get('items') ?? [];
                                $areaId = $get('project_area_id');

                                if (empty($items) || ! $areaId) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Silakan pilih Project Area dan tambahkan Job Position untuk melihat simulasi biaya.</p>');
                                }

                                $service = app(ManpowerCostingService::class);
                                $totalTemplateCost = 0;
                                $rows = '';

                                foreach ($items as $item) {
                                    $jpId = $item['job_position_id'] ?? null;
                                    $qty = (int) ($item['quantity'] ?? 0);

                                    if (! $jpId || $qty <= 0) {
                                        continue;
                                    }

                                    $jp = JobPosition::with('remunerationComponents')->find($jpId);
                                    if (! $jp) {
                                        continue;
                                    }

                                    $allowances = [];
                                    foreach ($jp->remunerationComponents ?? [] as $component) {
                                        $allowances[] = [
                                            'name' => $component->name,
                                            'type' => 'nominal',
                                            'value' => $component->pivot->amount,
                                            'is_fixed' => $component->is_fixed,
                                        ];
                                    }

                                    // Calculate single person cost
                                    $res = $service->calculate(
                                        basicSalary: $jp->basic_salary,
                                        allowances: $allowances,
                                        projectAreaId: $areaId,
                                        year: date('Y'), // Simulation uses current year
                                        riskLevel: $jp->risk_level ?? 'very_low',
                                        isLaborIntensive: $jp->is_labor_intensive ?? false
                                    );

                                    $unitCost = $res['total_direct_cost'];
                                    $lineTotal = $unitCost * $qty;
                                    $totalTemplateCost += $lineTotal;

                                    // Format currency
                                    $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                    $rows .= "
                                        <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800'>
                                            <td class='px-4 py-2'>{$jp->name}</td>
                                            <td class='px-4 py-2 text-center'>{$qty}</td>
                                            <td class='px-4 py-2 text-right'>Rp {$fmt($jp->basic_salary)}</td>
                                            <td class='px-4 py-2 text-right'>Rp {$fmt($res['total_allowances'] ?? 0)}</td>
                                            <td class='px-4 py-2 text-right'>Rp {$fmt($res['bpjs_total'] ?? 0)}</td>
                                            <td class='px-4 py-2 text-right'>Rp {$fmt($res['thr_compensation'] ?? 0)}</td>
                                            <td class='px-4 py-2 text-right font-medium'>Rp {$fmt($unitCost)}</td>
                                            <td class='px-4 py-2 text-right font-bold'>Rp {$fmt($lineTotal)}</td>
                                        </tr>
                                    ";
                                }

                                return new HtmlString("
                                    <div class='overflow-x-auto'>
                                        <table class='w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400'>
                                            <thead class='text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400'>
                                                <tr>
                                                    <th scope='col' class='px-4 py-3'>Job Position</th>
                                                    <th scope='col' class='px-4 py-3 text-center'>Qty</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Basic Salary</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Tunjangan</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>BPJS & Tax</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Accruals (THR)</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Total / Pax</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                            <tfoot>
                                                <tr class='font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700'>
                                                    <td colspan='7' class='px-4 py-3 text-right'>TOTAL ESTIMATED COST / MONTH</td>
                                                    <td class='px-4 py-3 text-right'>Rp ".number_format($totalTemplateCost, 0, ',', '.').'</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                ');
                            }),
                        Hidden::make('simulation_trigger'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('projectArea.name')
                    ->label('Project Area')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Positions'),
                ToggleColumn::make('is_active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManpowerTemplates::route('/'),
            'create' => Pages\CreateManpowerTemplate::route('/create'),
            'view' => Pages\ViewManpowerTemplate::route('/{record}'),
            'edit' => Pages\EditManpowerTemplate::route('/{record}/edit'),
        ];
    }
}
