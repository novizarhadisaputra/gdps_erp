<?php

namespace Tests\Feature;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\App;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\BankAccountResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas\BankAccountForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\ContractTypeResource;
use Tests\TestCase;

class GlobalTranslationTest extends TestCase
{
    /**
     * Test basic translation function.
     */
    public function test_basic_translation_functions(): void
    {
        App::setLocale('id');

        $this->assertEquals('Rekening Bank', __('Bank Account'));
        $this->assertEquals('Rekening Bank', __('Bank Accounts'));
        $this->assertEquals('Jenis Kontrak', __('Contract Type'));
        $this->assertEquals('Keuangan & Akuntansi', __('Finance & Accounting'));

        App::setLocale('en');

        $this->assertEquals('Bank Account', __('Bank Account'));
        $this->assertEquals('Bank Accounts', __('Bank Accounts'));
        $this->assertEquals('Contract Type', __('Contract Type'));
        $this->assertEquals('Finance & Accounting', __('Finance & Accounting'));
    }

    /**
     * Test resource label translation via MasterDataBaseResource.
     */
    public function test_resource_labels_translate_correctly(): void
    {
        // 1. Indonesian Locale
        App::setLocale('id');

        $this->assertEquals('Rekening Bank', BankAccountResource::getModelLabel());
        $this->assertEquals('Rekening Bank', BankAccountResource::getPluralModelLabel());
        $this->assertEquals('Rekening Bank', BankAccountResource::getNavigationLabel());
        $this->assertEquals('Keuangan & Akuntansi', BankAccountResource::getNavigationGroup());

        $this->assertEquals('Jenis Kontrak', ContractTypeResource::getModelLabel());
        $this->assertEquals('Jenis Kontrak', ContractTypeResource::getPluralModelLabel());
        $this->assertEquals('Jenis Kontrak', ContractTypeResource::getNavigationLabel());
        $this->assertEquals('SDM & Organisasi', ContractTypeResource::getNavigationGroup());

        // 2. English Locale
        App::setLocale('en');

        $this->assertEquals('Bank Account', BankAccountResource::getModelLabel());
        $this->assertEquals('Bank Accounts', BankAccountResource::getPluralModelLabel());
        $this->assertEquals('Bank Accounts', BankAccountResource::getNavigationLabel());
        $this->assertEquals('Finance & Accounting', BankAccountResource::getNavigationGroup());

        $this->assertEquals('Contract Type', ContractTypeResource::getModelLabel());
        $this->assertEquals('Contract Types', ContractTypeResource::getPluralModelLabel());
        $this->assertEquals('Contract Types', ContractTypeResource::getNavigationLabel());
        $this->assertEquals('HR & Organization', ContractTypeResource::getNavigationGroup());
    }

    /**
     * Test form schemas have explicit translations.
     */
    public function test_form_schemas_have_explicit_translations(): void
    {
        // 1. Indonesian Locale
        App::setLocale('id');

        $schema = BankAccountForm::configure(new Schema);
        $components = $schema->getComponents();

        $this->assertNotEmpty($components);

        // Heading validation
        $section = $components[0];
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals('Detail Umum', $section->getHeading());
        $this->assertEquals('Berikan informasi perbankan yang diperlukan untuk transaksi keuangan.', $section->getDescription());

        // Standalone Field translations check
        $bankNameField = \Filament\Forms\Components\TextInput::make('bank_name')
            ->label(__('Bank Name'));

        $this->assertEquals('Nama Bank', $bankNameField->getLabel());

        // 2. English Locale
        App::setLocale('en');

        $schema = BankAccountForm::configure(new Schema);
        $components = $schema->getComponents();
        $section = $components[0];

        $this->assertEquals('General Details', $section->getHeading());
        $this->assertEquals('Provide the banking information required for financial transactions.', $section->getDescription());

        $bankNameField = \Filament\Forms\Components\TextInput::make('bank_name')
            ->label(__('Bank Name'));
        $this->assertEquals('Bank Name', $bankNameField->getLabel());
    }
}
