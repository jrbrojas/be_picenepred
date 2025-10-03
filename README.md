## Forma de iniciar el proyecto backend - Plataforma Integradora (CENEPRED)

### 1. Instalar dependencias de composer

```bash
$ composer install
```

### 2. Copiar archivo de entorno

Ambiente de desarrollo

```bash
$ cp .env.dev .env
```
Ambiente de calidad

```bash
$ cp .env.qa .env
```

Ambiente de produccion

```bash
$ cp .env.prod .env
```

### 3. Copiar archivo de entorno

```bash
$ php artisan key:generate
```

### 4. Ejecutar migraciones
El proyecto se puede construir con las migraciones de Laravel, por lo que es
independiente del motor de base de datos que se esté utilzando. Como primer
comando se debe ejecutar:

Si lo levantas por primera vez:

```bash
$ php artisan migrate --seed
```

Si lo levantas posterior:

```bash
$ php artisan migrate:fresh --seed
```

### 5. Levantar servidor de desarrollo
Para probar el servidor, debe ejecutar:

```bash
$ php artisan serve
```
si es con un puerto como el 8003, ejecutas el siguiente comando

```bash
php artisan serve --port=8003
```
