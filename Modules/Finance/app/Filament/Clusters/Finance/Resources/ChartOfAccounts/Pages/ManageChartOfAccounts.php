<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;

class ManageChartOfAccounts extends TreePage
{
    protected static string $resource = ChartOfAccountResource::class;

    protected static ?string $title = 'Chart of Accounts';

    public static function tree(Tree $tree): Tree
    {
        return $tree
            ->actions([
                ActionGroup::make([
                    Action::make('createChild')
                        ->label('Add Child')
                        ->icon(Heroicon::Plus)
                        ->color('success')
                        ->url(fn (Model $record) => static::getResource()::getUrl('create', ['parent_id' => $record->id])),
                    EditAction::make()
                        ->url(fn (Model $record) => static::getResource()::getUrl('edit', ['record' => $record])),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        return "{$record->code} - {$record->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(static::getResource()::getUrl('create')),
        ];
    }
}
