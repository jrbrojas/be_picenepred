<?php

use Barryvdh\DomPDF\Facade\Pdf;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::get('/files/{file}', [\App\Http\Controllers\FileController::class, 'file'])->name('files.show');
//visualizar archivos adjuntos
Route::get('/files/{type}/{id}', [\App\Http\Controllers\FileController::class, 'show'])->name('anyfiles.show');
Route::get('/', function () {
    return view('welcome');
});
Route::get(
    '/pdf',
    function (Generator $generator) {
        $config = Scramble::getGeneratorConfig('default');
        $logo = public_path('img/rofailogo.png');
        $base64 = base64_encode(file_get_contents($logo));

        return Pdf::loadView('pdf.documentacion', ['apiSpec' => $generator($config), 'logoBase64' => $base64])
            ->setPaper('a4')
            ->stream("documentacion-api.pdf");
    }
);
