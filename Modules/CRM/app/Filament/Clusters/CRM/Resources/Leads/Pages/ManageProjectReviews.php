<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\ProjectReviewResource;

class ManageProjectReviews extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'projectReviews';

    protected static ?string $relatedResource = ProjectReviewResource::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $title = 'Project Reviews';

    protected static ?string $navigationLabel = 'Project Reviews';

    public function getSubheading(): ?string
    {
        return 'Overview and management of project reviews for this lead.';
    }

    public function form(Schema $schema): Schema
    {
        return ProjectReviewResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ProjectReviewResource::table($table);
    }
}
