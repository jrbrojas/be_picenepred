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
    </style>
</head>
<body>

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

    if (!function_exists('printSchemaProperties')) {
        function printSchemaProperties(array $schema, array $apiSpec, int $level = 0, array $inheritedRequired = [])
        {
            $schema = resolveSchema($schema, $apiSpec);

            if (($schema['type'] ?? null) === 'array' && isset($schema['items'])) {
                printSchemaProperties($schema['items'], $apiSpec, $level, $schema['required'] ?? $inheritedRequired);
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
                echo '<td>' . e($desc) . '</td>';
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
        <h1>{{ $apiSpec['info']['title'] }}</h1>
        <p><strong>Versión:</strong> {{ $apiSpec['info']['version'] }}</p>
        <p><strong>Descripción:</strong> {{ $apiSpec['info']['description'] ?? '-' }}</p>
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
            <h3>{{ $path }}</h3>

            @foreach($methods as $httpMethod => $operation)
                <div class="sub-section">
                    <h4>{{ strtoupper($httpMethod) }}</h4>

                    @if(isset($operation['summary']))
                        <p><strong>Resumen:</strong> {{ $operation['summary'] }}</p>
                    @endif
                    @if(isset($operation['description']))
                        <p><strong>Descripción:</strong> {!! nl2br(e($operation['description'])) !!}</p>
                    @endif

                    <p><strong>URL:</strong> <code>{{ $path }}</code></p>

                    {{-- HEADERS ESPECÍFICOS DEL ENDPOINT --}}
                    @php
                        $endpointHeaders = getOperationHeaders($operation, $apiSpec);
                    @endphp

                    @if(count($endpointHeaders))
                        <h5>Headers</h5>
                        <table>
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Tipo</th>
                                    <th>Req.</th>
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
                                        <th>Req.</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php printSchemaProperties($bodySchema, $apiSpec); @endphp
                                </tbody>
                            </table>
                        @endif
                    @endif

                    {{-- RESPONSES EN FORMATO ÁRBOL --}}
                    @if(isset($operation['responses']))
                        <h5>Responses</h5>

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
                                                    <th>Req.</th>
                                                    <th>Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    // internamente printSchemaProperties también resuelve $ref de schemas
                                                    printSchemaProperties($respSchema, $apiSpec);
                                                @endphp
                                            </tbody>
                                        </table>
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

