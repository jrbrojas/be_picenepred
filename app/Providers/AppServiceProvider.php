<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->info->title = 'API de Plataforma integradora';
                $openApi->info->version = 'v1';
                $openApi->info->description = '';
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            })
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {
                $middleware = $routeInfo->route->gatherMiddleware();

                if (collect($middleware)->contains(fn($m) => Str::startsWith($m, 'auth:api'))) {
                    $security = new SecurityRequirement(["http" => []]);
                    $operation->security = [$security];
                } else {
                    $operation->security = [];
                }
            });
    }
}
