<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function denyIfViewer(): void
    {
        abort_if(auth()->user()?->isViewer(), 403, 'Viewers cannot modify data.');
    }
}
