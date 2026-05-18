<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\MasterData\Models\BpjsJkkConfig;

class ManpowerTemplateItemObserver
{
    /**
     * Handle the ManpowerTemplateItem "saving" event.
     */
    public function saving(ManpowerTemplateItem $item): void
    {
        // Auto-populate legacy columns for backward compatibility and database coherence
        if ($item->bpjs_jkk_config_id) {
            $jkkConfig = BpjsJkkConfig::find($item->bpjs_jkk_config_id);
            if ($jkkConfig) {
                $item->risk_level = $jkkConfig->risk_level ?? 'very_low';
                if ($jkkConfig->employee_type) {
                    $item->employee_type = $jkkConfig->employee_type;
                }
            }
        } else {
            $item->risk_level = $item->risk_level ?? 'very_low';
        }

        if ($item->jkn_category) {
            $item->employee_type = strtolower($item->jkn_category);
        } else {
            $item->employee_type = $item->employee_type ?? 'ppu';
        }
    }
}
