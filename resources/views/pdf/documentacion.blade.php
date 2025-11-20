@php
function j(mixed $data, string $separator = ', '): string
{
    return is_array($data) ? join($separator, $data) : $data;
}
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $apiSpec['info']['title'] ?? 'Documentación de API' }}</title>

    <style>
        @page {
            margin: 100px 40px 70px 40px; /* top, right, bottom, left */
        }
            header {
                position: fixed;
                top: -60px;
                left: 0px;
                right: 0px;
                height: 60px;

                /** Extra personal styles **/
                color: black;
                text-align: center;
                line-height: 35px;

                border-bottom: 1px solid #aaa;
            }

            footer {
                position: fixed; 
                bottom: -60px; 
                left: 0px; 
                right: 0px;
                height: 50px; 

                /** Extra personal styles **/
                color: black;
                text-align: center;
                line-height: 35px;

                border-top: 1px solid #aaa;
            }
        .json-example {
    background: #1e1e1e;
    color: #dcdcdc;
    border: 1px solid #333;
    padding: 14px 18px;
    border-radius: 4px;
    font-family: Consolas, "Courier New", monospace;
    font-size: 13px;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin-top: 10px;
}

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        h1, h2, h3, h4, h5 {
            margin: 20px 0 10px;
        }
        h1 { font-size: 24px; }
        h2 { font-size: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px;}
        h3 { font-size: 18px; }
        h4 { font-size: 16px; }
        h5 { font-size: 14px; }

        .section { margin-bottom: 30px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        table th {
            background: #eee;
        }

        .header-info { margin-bottom: 15px; }
        .page-break { page-break-after: always; }

        .media-type {
            float: right;
            font-size: 11px;
            color: #666;
        }

        .schema-table th:nth-child(1) { width: 40%; }
        .schema-table th:nth-child(2) { width: 15%; }
        .schema-table th:nth-child(3) { width: 10%; }
        .schema-table th:nth-child(4) { width: 35%; }

        .prop-name {
            white-space: nowrap;
        }
        .level-0 { padding-left: 0; }
        .level-1 { padding-left: 15px; }
        .level-2 { padding-left: 30px; }
        .level-3 { padding-left: 45px; }
        .level-4 { padding-left: 60px; }
.header-table {
    width: 100%;
    border-collapse: collapse;
    border: none;
}

.header-table td {
    border: none !important;
    padding: 0;
}

.header-logo {
    width: 120px; /* Ajusta según tu logo */
    vertical-align: bottom;
}

.logo-img {
    width: 100px; /* Ajusta tamaño del logo */
    height: auto;
    display: block;
}

.header-text {
    text-align: right;
    font-size: 12px;
    padding-left: 10px;
}
    </style>
</head>
<body>

        <header>
            <table class="header-table">
                <tr>
                    <td class="header-logo"><img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo-img"/></td>
                    <td class="header-text">ROFAI BUSINESS S.A.C. | Plataforma Tecnológica Integrada – CENEPRED</td>
                </tr>
            </table>
        </header>

        <footer>
            <div>ROFAI BUSINESS S.A.C. – PTI CENEPRED</div>
        </footer>
@php
    if (!function_exists('resolveSchema')) {
        function resolveSchema(array $schema, array $apiSpec)
        {
            if (isset($schema['$ref'])) {
                $ref = $schema['$ref']; // "#/components/schemas/User"
                $path = explode('/', trim($ref, '#/'));
                $resolved = $apiSpec;
                foreach ($path as $segment) {
                    if (is_array($resolved) && array_key_exists($segment, $resolved)) {
                        $resolved = $resolved[$segment];
                    } else {
                        return $schema; // fallback
                    }
                }
                if (is_array($resolved)) {
                    $schema = array_merge($resolved, [
                        '_refName' => end($path),
                    ]);
                }
            }
            return $schema;
        }
    }

    if (!function_exists('schemaTypeLabel')) {
        function schemaTypeLabel(array $schema, ?string $refName = null)
        {
            if ($refName) {
                return $refName;
            }

            $type = $schema['type'] ?? 'object';

            if ($type === 'array') {
                $items = $schema['items'] ?? [];
                $itemType = $items['type'] ?? null;
                $ref = $items['$ref'] ?? null;

                if ($ref) {
                    $parts = explode('/', trim($ref, '#/'));
                    $name = end($parts);
                    return 'array<' . $name . '>';
                }

                return 'array<' . ($itemType ?? 'mixed') . '>';
            }

            if (isset($schema['format'])) {
                return j($type) . '<' . j($schema['format']) . '>';
            }

            return $type;
        }
    }
if (!function_exists('generateExampleFromSchema')) {
    /**
     * Genera un ejemplo (PHP array) recursivamente a partir de un schema OpenAPI.
     *
     * @param array $schema
     * @param array $apiSpec
     * @param int $depth Profundidad de recursión (para evitar loops)
     * @param array $inheritedRequired campos requeridos heredados (si aplica)
     * @return mixed
     */
    function generateExampleFromSchema(array $schema, array $apiSpec, int $depth = 0, array $inheritedRequired = [])
    {
        // Evitar recursión infinita
        $maxDepth = 6;
        if ($depth > $maxDepth) {
            return null;
        }

        $schema = resolveSchema($schema, $apiSpec);

        // Arrays
        if (($schema['type'] ?? null) === 'array' && isset($schema['items'])) {
            $itemExample = generateExampleFromSchema($schema['items'], $apiSpec, $depth + 1, $schema['required'] ?? []);
            return [$itemExample];
        }

        // Si es un object con properties -> generar objeto completo
        if (($schema['type'] ?? null) === 'object' && isset($schema['properties']) && is_array($schema['properties'])) {
            $result = [];
            $required = $schema['required'] ?? $inheritedRequired;

            foreach ($schema['properties'] as $propName => $propSchema) {
                $resolved = resolveSchema($propSchema, $apiSpec);
                // si el campo tiene ejemplo explícito, usarlo; si no, recurrir a generatePropertyExample que delega a generateExampleFromSchema cuando sea necesario
                $result[$propName] = generatePropertyExample($resolved, $apiSpec, $depth + 1);
            }

            return $result;
        }

        // Si no tiene properties (primitivo, enum, etc.), delegar
        return generatePropertyExample($schema, $apiSpec, $depth);
    }
}

if (!function_exists('generatePropertyExample')) {
    /**
     * Genera ejemplo para una sola propiedad (puede delegar a generateExampleFromSchema si es objeto/array).
     *
     * @param array $schema
     * @param array $apiSpec
     * @param int $depth
     * @return mixed
     */
    function generatePropertyExample(array $schema, array $apiSpec = [], int $depth = 0)
    {
        // Resuelve referencias por si acaso
        $schema = resolveSchema($schema, $apiSpec);

        // 1) Si tiene example → usar directamente
        if (isset($schema['example'])) {
            return $schema['example'];
        }

        // 2) Si es enum → tomar el primer valor
        if (isset($schema['enum']) && is_array($schema['enum']) && count($schema['enum']) > 0) {
            return $schema['enum'][0];
        }

        // 3) Si viene con ejemplo por ejemplo de x-example u otros campos (opcional)
        if (isset($schema['x-example'])) {
            return $schema['x-example'];
        }

        $type = $schema['type'] ?? null;
        $format = $schema['format'] ?? null;

        // Si no hay tipo pero tiene properties -> tratar como object
        if ($type === null && isset($schema['properties'])) {
            $type = 'object';
        }
        switch ($type) {
            case 'string':
                // Diferenciar formatos comunes
                $format = $schema['format'] ?? null;
                if ($format === 'date-time' || $format === 'date') {
                    // usar formato ISO
                    return date($format === 'date-time' ? 'c' : 'Y-m-d');
                }
                if ($format === 'uuid') {
                    return '00000000-0000-0000-0000-000000000000';
                }
                return $schema['default'] ?? 'string';

            case 'integer':
                return $schema['default'] ?? 0;

            case 'number':
                return $schema['default'] ?? 0.0;

            case 'boolean':
                return $schema['default'] ?? true;

            case 'array':
                if (isset($schema['items'])) {
                    // si items es objeto -> delegar para generar contenido del array
                    $itemExample = generatePropertyExample($schema['items'], $apiSpec, $depth + 1);
                    return [$itemExample];
                }
                return [];

            case 'object':
                // Si tiene properties -> generar recursivamente
                if (isset($schema['properties']) && is_array($schema['properties'])) {
                    $res = [];
                    foreach ($schema['properties'] as $pname => $pschema) {
                        $res[$pname] = generatePropertyExample(resolveSchema($pschema, $apiSpec), $apiSpec, $depth + 1);
                    }
                    return $res;
                }
                return (object)[]; // o [] según prefieras

            default:
                // fallback: intentar inferir a partir de "properties" o "items"
                if (isset($schema['properties'])) {
                    return generateExampleFromSchema($schema, $apiSpec, $depth + 1);
                }
                if (isset($schema['items'])) {
                    return [ generatePropertyExample($schema['items'], $apiSpec, $depth + 1) ];
                }

                return $schema['default'] ?? 'example';
        }
    }
}
    if (!function_exists('printSchemaProperties')) {
        function printSchemaProperties(array $schema, array $apiSpec, int $level = 0, array $inheritedRequired = [])
        {
            $schema = resolveSchema($schema, $apiSpec);

            if (($schema['type'] ?? null) === 'array' && isset($schema['items'])) {
                $newSchema = $schema['items'];
                if (!is_array($newSchema)) {
                    $newSchema = (array) $newSchema;;
                }
                printSchemaProperties($newSchema, $apiSpec, $level, $schema['required'] ?? $inheritedRequired);
                return;
            }

            $requiredList = $schema['required'] ?? $inheritedRequired;

            if (!isset($schema['properties']) || !is_array($schema['properties'])) {
                return;
            }

            foreach ($schema['properties'] as $propName => $propSchema) {
                $original = $propSchema;
                $refName = null;

                if (isset($original['$ref'])) {
                    $parts = explode('/', trim($original['$ref'], '#/'));
                    $refName = end($parts);
                }

                $propSchema = resolveSchema($propSchema, $apiSpec);
                $label = schemaTypeLabel($propSchema, $propSchema['_refName'] ?? $refName);
                $desc  = $propSchema['description'] ?? '';
                $isReq = in_array($propName, $requiredList ?? [], true) ? 'Sí' : 'No';

                echo '<tr>';
                echo '<td class="prop-name level-' . $level . '">' .
                        str_repeat('&nbsp;&nbsp;&nbsp;', $level) .
                        e($propName) .
                     '</td>';
                echo '<td>' . e(j($label)) . '</td>';
                echo '<td>' . $isReq . '</td>';
                //echo '<td>' . e($desc) . '</td>';
                echo '</tr>';

                if (
                    isset($propSchema['properties']) ||
                    (isset($propSchema['type']) && $propSchema['type'] === 'array' && isset($propSchema['items']))
                ) {
                    printSchemaProperties($propSchema, $apiSpec, $level + 1, $propSchema['required'] ?? []);
                }
            }
        }
    }

    if (!function_exists('getBodySchema')) {
        function getBodySchema(array $requestBody)
        {
            if (!isset($requestBody['content']) || !is_array($requestBody['content'])) {
                return null;
            }

            if (isset($requestBody['content']['application/json']['schema'])) {
                return $requestBody['content']['application/json']['schema'];
            }

            $first = reset($requestBody['content']);
            return $first['schema'] ?? null;
        }
    }

    // NUEVO: headers por operación (parámetros + security)
    if (!function_exists('getOperationHeaders')) {
        function getOperationHeaders(array $operation, array $apiSpec): array
        {
            $headers = [];

            // 1) Parámetros "in: header"
            if (isset($operation['parameters']) && is_array($operation['parameters'])) {
                foreach ($operation['parameters'] as $param) {
                    if (($param['in'] ?? null) === 'header') {
                        $name = $param['name'] ?? '';
                        if (!$name) continue;

                        $headers[$name] = [
                            'name' => $name,
                            'type' => $param['schema']['type'] ?? 'string',
                            'required' => !empty($param['required']) ? 'Sí' : 'No',
                            'description' => $param['description'] ?? '',
                        ];
                    }
                }
            }

            // 2) Security (global o por operación)
            $globalSecurity = $apiSpec['security'] ?? [];
            $opSecurity = $operation['security'] ?? $globalSecurity;

            if (!empty($opSecurity) && isset($apiSpec['components']['securitySchemes'])) {
                $schemes = $apiSpec['components']['securitySchemes'];

                foreach ($opSecurity as $secObj) {
                    if (!is_array($secObj)) continue;

                    foreach ($secObj as $secName => $scopes) {
                        $scheme = $schemes[$secName] ?? null;
                        if (!$scheme) continue;

                        // Ejemplo típico: http bearer JWT
                        if (($scheme['type'] ?? null) === 'http' && ($scheme['scheme'] ?? null) === 'bearer') {
                            $name = 'Authorization';
                            if (!isset($headers[$name])) {
                                $desc = 'Bearer token para autenticar la petición';
                                if (!empty($scheme['description'])) {
                                    $desc = $scheme['description'];
                                } elseif (!empty($scheme['bearerFormat'])) {
                                    $desc .= ' (' . $scheme['bearerFormat'] . ')';
                                }

                                $headers[$name] = [
                                    'name' => $name,
                                    'type' => 'string',
                                    'required' => 'Sí',
                                    'description' => $desc,
                                ];
                            }
                        }

                        // si tuvieras otros tipos (apiKey en header, etc.) puedes extender aquí
                    }
                }
            }

            return array_values($headers);
        }
    }
@endphp

    <!-- Portada -->
    <div class="section">
        <h1>{!! $apiSpec['info']['title'] !!}</h1>
        <p><strong>Versión:</strong> {{ $apiSpec['info']['version'] }}</p>
        <p style="text-align: justify;"><strong>Descripción:</strong> {!! $apiSpec['info']['description'] ?? '-' !!}</p>
    </div>

    <div class="page-break"></div>

    <!-- Seguridad / Autenticación -->
    <div class="section">
        <h2>Autenticación / Seguridad</h2>
        @if(isset($apiSpec['components']['securitySchemes']))
            @foreach($apiSpec['components']['securitySchemes'] as $schemeName => $schemeDef)
                <h3>{{ $schemeName }}</h3>
                <p><strong>Tipo:</strong> {{ $schemeDef['type'] }}</p>
                @if(isset($schemeDef['scheme'])) <p><strong>Esquema:</strong> {{ $schemeDef['scheme'] }}</p>@endif
                @if(isset($schemeDef['bearerFormat'])) <p><strong>Formato Bearer:</strong> {{ $schemeDef['bearerFormat'] }}</p>@endif
                @if(isset($schemeDef['in'])) <p><strong>Dónde:</strong> {{ $schemeDef['in'] }}</p>@endif
            @endforeach
        @else
            <p>No se ha definido ningún esquema de seguridad.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Headers Globales -->
    <div class="section">
        <h2>Headers globales de la API</h2>
        @if(isset($apiSpec['servers']))
            <p><strong>Servidores:</strong></p>
            <ul>
                @foreach($apiSpec['servers'] as $srv)
                    <li>{{ $srv['url'] }} {{ isset($srv['description']) ? '- ' . $srv['description'] : '' }}</li>
                @endforeach
            </ul>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Header</th>
                    <th>Obligatorio</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Authorization</td>
                    <td>Sí</td>
                    <td>Bearer token JWT para autenticar la petición</td>
                </tr>
                <tr>
                    <td>Content-Type</td>
                    <td>Sí</td>
                    <td>application/json (o según el endpoint)</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- Endpoints -->
    <div class="section">
        <h2>Endpoints</h2>

        @foreach($apiSpec['paths'] as $path => $methods)
            @foreach($methods as $httpMethod => $operation)
                <div class="sub-section">
                    @if(isset($operation['summary']))
                        <h1>{{ $operation['summary'] }}</h1>
                    @endif
                    <p><strong>URL:</strong> <code>/api{{ $path }}</code></p>
                    <p><strong>Método HTTP:</strong> <code>{{ strtoupper($httpMethod) }}</code></p>
                    @if(isset($operation['description']))
                        <p><strong>Descripción:</strong> {!! nl2br(e($operation['description'])) !!}</p>
                    @endif


                    {{-- HEADERS ESPECÍFICOS DEL ENDPOINT --}}
                    @php
                        $endpointHeaders = getOperationHeaders($operation, $apiSpec);
                    @endphp

                    @if(count($endpointHeaders))
                        <h4>Headers</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Tipo</th>
                                    <th>Requerido</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($endpointHeaders as $h)
                                    <tr>
                                        <td>{{ $h['name'] }}</td>
                                        <td>{{ $h['type'] }}</td>
                                        <td>{{ $h['required'] }}</td>
                                        <td>{{ $h['description'] ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- BODY (REQUEST) EN FORMATO ÁRBOL --}}
                    @if(isset($operation['requestBody']))
                        @php
                            $bodySchema = getBodySchema($operation['requestBody']);
                            $contentTypes = isset($operation['requestBody']['content'])
                                ? implode(', ', array_keys($operation['requestBody']['content']))
                                : '';
                        @endphp

                        @if($bodySchema)
                            <h4>Request</h4>
                            <h5>
                                Body
                                @if($contentTypes)
                                    <span class="media-type">{{ $contentTypes }}</span>
                                @endif
                            </h5>

                            <table class="schema-table">
                                <thead>
                                    <tr>
                                        <th>Propiedad</th>
                                        <th>Tipo</th>
                                        <th>Requerido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php printSchemaProperties($bodySchema, $apiSpec); @endphp
                                </tbody>
                            </table>
                            <h4>Ejemplo</h4>
                            <pre class="json-example">{{ json_encode(generateExampleFromSchema($bodySchema, $apiSpec), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    @endif

                    {{-- RESPONSES EN FORMATO ÁRBOL --}}
                    @if(isset($operation['responses']))
                        <h4>Responses</h4>

                        @foreach($operation['responses'] as $code => $resp)
                            @php
                                // IMPORTANTE: resolver $ref en components.responses
                                $resp = resolveSchema($resp, $apiSpec);
                            @endphp

                            <p><strong>Código {{ $code }}:</strong> {{ $resp['description'] ?? '-' }}</p>

                            @if(isset($resp['content']) && is_array($resp['content']))
                                @foreach($resp['content'] as $media => $mediaObj)
                                    @php
                                        $respSchema = $mediaObj['schema'] ?? null;
                                    @endphp

                                    @if($respSchema)
                                        <h5>
                                            Body
                                            <span class="media-type">{{ $media }}</span>
                                        </h5>

                                        <table class="schema-table">
                                            <thead>
                                                <tr>
                                                    <th>Propiedad</th>
                                                    <th>Tipo</th>
                                                    <th>Requerido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    // internamente printSchemaProperties también resuelve $ref de schemas
                                                    printSchemaProperties($respSchema, $apiSpec);
                                                @endphp
                                            </tbody>
                                        </table>
                                        <h4>Ejemplo</h4>
                                        <pre class="json-example">{{ json_encode(
                                            generateExampleFromSchema($respSchema, $apiSpec),
                                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                                        ) }}</pre>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @endif

                </div>

                <div class="page-break"></div>
            @endforeach
        @endforeach
    </div>
</body>
</html>

