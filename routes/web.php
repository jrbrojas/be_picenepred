<?php

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/pdf',
    function (Generator $generator) {
        $config = Scramble::getGeneratorConfig('default');

        return Pdf::loadView('pdf.documentacion', ['apiSpec' => $generator($config)])
            ->setPaper('a4')
            ->stream("documentacion-api.pdf");
    }
);
