<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages\CreateProjectReview;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages\EditProjectReview;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages\ListProjectReviews;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages\ViewProjectReview;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Schemas\ProjectReviewForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Schemas\ProjectReviewInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Tables\ProjectReviewTable;
use Modules\CRM\Models\ProjectReview;

class ProjectReviewResource extends Resource
{
    protected static ?string $model = ProjectReview::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Project Reviews';

    protected static ?string $title = 'Project Review';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'project-reviews';

    public static function form(Schema $schema): Schema
    {
        return ProjectReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectReviewTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectReviewInfolist::configure($schema);
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
            'index' => ListProjectReviews::route('/'),
            'create' => CreateProjectReview::route('/create'),
            'view' => ViewProjectReview::route('/{record}'),
            'edit' => EditProjectReview::route('/{record}/edit'),
        ];
    }
}
