<?php

namespace App\Support\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class UuidFileNamer extends DefaultFileNamer
{
    public function originalFileName(string $fileName): string
    {
        return (string) Str::uuid();
    }
}
