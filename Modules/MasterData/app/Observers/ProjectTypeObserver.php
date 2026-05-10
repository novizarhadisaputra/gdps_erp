<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\ProjectType;

class ProjectTypeObserver
{
    public function saving(ProjectType $projectType): void
    {
        if (empty($projectType->code)) {
            $projectType->code = $this->generateCode();
        }
    }

    protected function generateCode(): string
    {
        $count = ProjectType::count();
        $next = str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);

        while (ProjectType::where('code', $next)->exists()) {
            $count++;
            $next = str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);
        }

        return $next;
    }
}
