<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;
use Illuminate\Support\Str;

class UuidFileNamer extends DefaultFileNamer
{
    public function originalFileName(string $fileName): string
    {
        return (string) Str::uuid();
    }
}
