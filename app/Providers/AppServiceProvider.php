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
                $openApi->info->title = 'Documento de API Integración
                    APIs de Integración Sistema de Registro Plataforma Integradora
                    Plataforma Integradora CENEPRED';
                $openApi->info->version = 'v1';
                $openApi->info->description = <<<EOF
                        El presente Documento de API de Integración tiene como finalidad definir, estructurar y describir de manera detallada los servicios de integración que permiten la interoperabilidad entre el Sistema de Registro Plataforma Integradora y la Plataforma Integradora del CENEPRED. Este documento consolida los lineamientos técnicos, especificaciones funcionales y requerimientos de comunicación necesarios para garantizar un intercambio de información seguro, eficiente y trazable entre los sistemas involucrados.

                        En este documento se describen las APIs de Integración, sus endpoints, métodos permitidos, parámetros requeridos y estructuras de datos utilizadas. Cada servicio incluye la definición completa de su request, detallando los headers obligatorios y opcionales, el body correspondiente según el tipo de operación, así como los responses que puede retornar el servicio —incluyendo códigos de estado HTTP, mensajes de validación y modelos de datos de salida—. Esto permite a los equipos de desarrollo implementar correctamente la comunicación entre sistemas según las reglas establecidas.

                        Además, se especifican las consideraciones de seguridad, autenticación, manejo de errores, políticas de versionamiento y mecanismos de auditoría que rigen el uso de los servicios. De igual manera, se documentan las validaciones aplicadas, escenarios de uso y ejemplos prácticos de consumo para facilitar la implementación.

                        El propósito de esta documentación es asegurar que los equipos técnicos responsables del desarrollo, mantenimiento y supervisión de ambos sistemas cuenten con una referencia clara, actualizada y estandarizada para implementar adecuadamente los procesos de intercambio de información, reduciendo riesgos de incompatibilidad, errores de integración y brechas de seguridad.

                        Este documento forma parte de la arquitectura técnica de la Plataforma Integradora CENEPRED y contribuye al cumplimiento de los lineamientos establecidos en las normativas institucionales, garantizando la calidad, consistencia y disponibilidad de los datos gestionados en el marco de la Gestión del Riesgo de Desastres.
                    EOF;
                //$openApi->info->description = nl2br($openApi->info->description);
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
