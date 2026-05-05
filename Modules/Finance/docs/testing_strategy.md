# Finance Module: JSONB-Centric Architecture & Testing Strategy

This document outlines the architectural shift from Eloquent-based costing items to a JSONB-centric model within the Finance module (specifically `ProfitabilityAnalysis`), and defines the testing standards for this pattern.

## 1. Architectural Audit Results

### 1.1 Model Decoupling
The `ProfitabilityAnalysis` model has been successfully decoupled from legacy costing tables (`manpowerItems`, `operationalItems`).
- **Relationships Removed**: All `HasMany` relationships to separate costing tables have been eliminated.
- **Data Persistence**: All costing data is now stored within the `analysis_details` JSONB column.
- **Accessors & Methods**: 
    - `getDirectItems()` and `getIndirectItems()` now parse `analysis_details` to return collections.
    - `getManpowerRequirementsAttribute()` and `getOperationalRequirementsAttribute()` provide virtual parity with legacy structures for downstream consumers (like exports).

### 1.2 Form Schema Integrity
The `ProfitabilityAnalysisForm` has been updated to handle the JSONB structure directly.
- **Manual Costing**: Uses `Repeater::make('analysis_details.manual_costs')` with nested `sub_items`.
- **Form State Compatibility**: Maintained hidden repeaters (`manpowerItems`, `operationalItems`) to facilitate action-based data hydration from templates without requiring database-level relations.

### 1.3 Service Layer Purity
The `ManpowerCostingService` and `SignatureService` are fully compliant.
- They operate on primitive data types or polymorphic signatures, maintaining independence from the specific database schema of the signable/calculable entities.

## 2. Testing Strategy

All tests in the Finance module must now follow the **JSONB-First** pattern. Do not attempt to use `factory()->hasItems()` for costing data.

### 2.1 Pattern: Manual JSONB Construction
When testing calculations or resource flows, construct the `analysis_details` manually to mirror the Filament form state.

```php
$analysis = ProfitabilityAnalysis::factory()->create([
    'is_manual_cost' => true,
    'analysis_details' => [
        'manual_costs' => [
            [
                'direct_cost_category_id' => $category->id,
                'amount' => 1000000,
                'sub_items' => [
                    [
                        'name' => 'Test Item',
                        'quantity' => 1,
                        'unit_amount' => 1000000,
                        'amount' => 1000000,
                    ]
                ]
            ]
        ]
    ]
]);
```

### 2.2 Pattern: Testing Action Hydration
To test approval flows or status changes that rely on form state, use the `mountAction` or `callAction` helpers in Filament tests, ensuring the `data` array includes the `analysis_details` block.

### 2.3 Verification Points
- **100% Pass Rate**: Ensure `Modules/Finance/tests/Feature` returns no failures.
- **No Residual Queries**: Audit DB logs during tests to ensure no queries are made to `manpower_items` or `operational_items` tables.

## 3. Deployment & Migration
- Ensure the `analysis_details` column exists and has a proper default `[]`.
- Existing records should be migrated via a custom script if necessary, but for GDPS POS, the shift was applied during the development phase of the module.
