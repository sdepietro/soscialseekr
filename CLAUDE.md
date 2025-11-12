# CLAUDE.md

Este archivo proporciona una guía para **Claude Code** (claude.ai/code) al trabajar con el código en este repositorio.

## Descripción general del proyecto

**X Finder** es una plataforma de monitoreo y análisis con IA para Twitter/X, construida con **Laravel 12**. Ejecuta búsquedas programadas en Twitter, almacena tweets con métricas completas, rastrea cambios históricos y utiliza **OpenAI** para evaluar la relevancia de los tweets en un contexto de negocio SaaS médico.

## Comandos de desarrollo

### Ejecución de la aplicación

```bash
# Iniciar todos los servicios al mismo tiempo (recomendado para desarrollo)
composer run dev
# Esto ejecuta: servidor + worker de cola + visor de logs + vite

# O ejecutar servicios individualmente:
php artisan serve              # Servidor web en puerto 8000
php artisan queue:work         # Worker de colas
php artisan queue:listen --tries=1  # Listener de colas (recarga automática)
php artisan pail --timeout=0   # Visor de logs en tiempo real
npm run dev                    # Servidor de desarrollo de Vite

# Entorno Docker
docker-compose up -d           # Iniciar contenedores
docker-compose down            # Detener contenedores
docker-compose logs -f app     # Ver logs de la aplicación
```

### Operaciones de base de datos

```bash
php artisan migrate            # Ejecutar migraciones
php artisan migrate:fresh      # Eliminar todas las tablas y migrar de nuevo
php artisan db:seed            # Ejecutar seeders

# Vía rutas web (solo desarrollo, eliminar en producción):
# GET /install       - Ejecutar migraciones
# GET /forceinstall  - Migración completa (destructiva)
# GET /clear-cache   - Limpiar cachés
```

### Comandos de Cron (Tareas Programadas)

```bash
# Ejecutar búsquedas de Twitter y guardar resultados
php artisan twitter:search

# Analizar tweets pendientes con IA
php artisan tweets:analyze-ai

# Ver tareas programadas en el scheduler
php artisan schedule:list

# Ejecutar el scheduler manualmente (una sola vez)
php artisan schedule:run

# Ejecutar el scheduler continuamente (para desarrollo)
php artisan schedule:work

# Configuración en producción:
# Agregar al crontab del servidor:
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# Ver documentación completa de configuración:
# NO_SUBIR/docs/CRON_SETUP.md
```

**Nota**: Los comandos `twitter:search` y `tweets:analyze-ai` están programados para ejecutarse automáticamente cada minuto mediante el Laravel Task Scheduler. En desarrollo, puedes usar `php artisan schedule:work` para ejecutar el scheduler localmente, o ejecutar los comandos manualmente para testing.

### Pruebas

por el momento no hay pruebas ni test funcionales/unitarios válidos.

### Calidad de código

```bash
composer run lint              # Ejecutar Laravel Pint
php artisan pint               # Formatear código con Pint
```

### Operaciones JWT

```bash
php artisan jwt:secret         # Generar clave secreta JWT
```

### Documentación de API

Acceder a Swagger UI en `/api/documentation` después de iniciar el servidor.

### Documentación del proyecto

Dentro de la carpeta NO_SUBIR/docs se encuentra la documentación técnica del proyecto en formato Markdown.

```bash
php artisan l5-swagger:generate  # Regenerar documentación API
```

## Arquitectura general

### Componentes principales

**Capa de servicios** (`app/Services/`):
- `TwitterService`: Interactúa con TwitterAPI.io para búsquedas y manejo de paginación.
- `ChatGptService`: Evalúa tweets (máx. 20 por lote) con OpenAI usando prompts médicos. Devuelve puntajes de 0 a 100.

**Controladores** (`app/Http/Controllers/`):
- `Api/UserController`: Autenticación JWT (login/logout/reset)
- `Api/PlacesController`, `Api/OrdersController`: Endpoints del dominio
- `Test/TwitterController`: Endpoints de prueba

**Comandos de Consola** (`app/Console/Commands/`):
- `RunTwitterSearches`: Ejecuta búsquedas programadas de Twitter cada minuto
- `AnalyzeTweetsWithAI`: Analiza tweets pendientes con IA cada minuto

**Modelos** (`app/Models/`):
- `User`: Autenticación JWT con roles
- `Account`: Cachea perfiles de Twitter
- `Search`: Configuraciones de búsqueda programada
- `Tweet`: Contenido y análisis IA
- `TweetHistory`: Historial de métricas

### Flujo de datos

```
Configuración → TwitterService → TwitterAPI.io
                     ↓
              Account + Tweet + TweetHistory
                     ↓
              ChatGptService → OpenAI
                     ↓
              Tweet (ia_analyzed, ia_score, ia_reason)
```

### Autenticación JWT con encabezado personalizado

- **Middleware**: `JwtMiddleware` valida tokens
- **Encabezado**: `Authorizationjwt` (no estándar)
- **Mapeo**: `authorizationjwt` → `Authorization`
- **Rutas protegidas**: Middleware `jwt.verify`

Para probar endpoints:  
`Authorizationjwt: Bearer <token>`

### Sistema de permisos

Definido en `config/constants.php`
- Roles: `admin`, `supervisor`, `user`
- El admin tiene `*` (acceso total)
- `User::checkAccess($permission)` realiza la validación
- No se almacenan en base de datos

### Esquema de base de datos

Relaciones:
- `Account` (1) → `Tweet` (N)
- `Tweet` (1) → `TweetHistory` (N)
- `User` (N) ↔ `Permission` (N)

Convenciones:
- Tabla `searchs` (plural no estándar)
- Soft deletes (`deleted_at`)
- Campos JSON (`entities`, `raw_payload`, etc.)

Campos IA:
- `ia_analyzed` (bool)
- `ia_score` (int)
- `ia_reason` (string)

### Archivos de configuración

`config/constants.php`:
- Roles y permisos
- Credenciales API:
    - `TWITTERAPIIIO_API_KEY`
    - `OPENAI_API_KEY`
    - `OPENAI_VERSION`
    - `OPENAI_MAX_TOKENS`

### Sistema de colas

- Driver: base de datos (`jobs`, `failed_jobs`)
- Worker: `composer run dev`
- El cron usa rutas web
- Recomendado: usar jobs en lugar de controladores directos

### Sistema de búsqueda

Operadores avanzados:
```
min_replies:1 (receta OR medico OR clinica)
geocode:-34.619340,-58.494032,50km
since:2025-10-28
-filter:replies
```

Scopes:
- `Search::active()`
- `Search::country('AR')`

Flujo:
1. Carga búsquedas activas
2. Llama a `TwitterService::search()`
3. Upsert de `Account` y `Tweet`
4. Crea `TweetHistory`
5. Detecta picos y llama `notificate()`

Limitación: `array_slice(..., 0, 2)` devuelve solo 2 tweets.

### Integración con ChatGPT

**Criterios**:
1. Intención médica (0–40)
2. Accionabilidad (0–25)
3. Contexto argentino (0–15)
4. Seguridad (0–10)
5. Recencia/interacción (0–10)

**Configuración**:
- Modelo: `gpt-4o-mini`
- Temperatura: 0.2
- Tokens máx.: 500
- Lote: 20 tweets

Entrada: `tweet_id|fecha|likes|texto`  
Salida: `[{id, score, reason}]`

## Entorno Docker

**Contenedores**: `ypf_bdots_*`
- `ypf_bdots_php`: app PHP/Laravel (8088)
- `ypf_bdots_db`: MySQL 5.7 (3307)
- `ypf_bdots_phpmyadmin`: phpMyAdmin (8080)

**Base de datos**:
- Host: `db` (interno) / `localhost:3307` (externo)
- DB: `ypf_bdots`
- Usuario: `root`
- Password: `root`

Cambiar `.env` a:
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
```

## Manejo de errores en servicios

```php
if ($response->successful()) {
    return $response->json();
}
Log::error("API Error", ['response' => $response->body()]);
return null;
```

## Transacciones

```php
try {
    // DB::beginTransaction();
    // ...
    // DB::commit();
} catch (\Exception $e) {
    // DB::rollBack();
    Log::error($e->getMessage());
}
```

## Relaciones de modelos

```php
$tweet->account;
$tweet->histories;
$account->tweets;
```

## Detección de picos

- Analiza `TweetHistory` de la última hora
- Calcula delta de métricas
- Umbrales: +20 likes o +10 replies
- Llama `notificate($tweet)`

## Pruebas

Base: SQLite en memoria  
Tipos:
1. Unitarias (`TwitterService`, `ChatGptService`)
2. Funcionales (autenticación JWT, CRUD de tweets)
3. Integración (flujo completo cron → IA)

## Seguridad

**Credenciales por defecto**:
- Email: `admin@xfinder.com`
- Pass: `123456`

**Variables a proteger**:
- `TWITTERAPIIIO_API_KEY`
- `OPENAI_API_KEY`
- `JWT_SECRET`
- `DB_PASSWORD`

**Rutas a eliminar en producción**:
- `/install`, `/forceinstall`, `/clear-cache`, `/test/*`

**Rate limiting**: aún no implementado

## Tareas comunes

**Agregar nueva búsqueda**:
1. Insertar en `searchs`
2. `active = true`
3. Configurar `run_every_minutes`, `query`, `country`, `lang`

**Probar búsqueda**:
```bash
# Ejecutar comando de búsqueda manualmente
php artisan twitter:search

# O usar endpoint de testing
POST /test/buscar_tweets
{
  "query": "min_replies:1 medico",
  "queryType": "Latest"
}
```

**Evaluar IA**:
```bash
# Ejecutar análisis IA manualmente
php artisan tweets:analyze-ai

# O usar endpoint de testing
POST /test/evaluateTweets
```

**Colas**:
```bash
php artisan queue:failed
php artisan queue:retry <job_id>
php artisan queue:flush
```

**Logs**:
```bash
php artisan pail --timeout=0
```

## Pruebas de API

```bash
curl -X POST http://localhost:8000/api/v1/auth/login   -H "Content-Type: application/json"   -d '{"email":"admin@xfinder.com","password":"123456"}'

curl -X GET http://localhost:8000/api/v1/users/me   -H "Authorizationjwt: Bearer <token>"
```

Swagger UI: `/api/documentation`

## Problemas conocidos

1. Límite de 2 tweets en `TwitterService`
2. `notificate()` vacío en `RunTwitterSearches` (método para detectar spikes)
3. Transacciones comentadas en algunos lugares
4. Contenedores `ypf_bdots_*` (nombres legacy)
5. Cobertura de tests mínima

## Convenciones

- PSR-12
- Laravel Pint
- Soft deletes
- Campos JSON
- Capa de servicios para APIs externas
- Logs en errores de API

## Rendimiento

- Usar `next_cursor` y procesamiento por lotes
- Indexar `tweets.created_at_twitter`
- Eager loading (`Tweet::with('account','histories')`)
- Cachear configuraciones y datos frecuentes

## APIs externas

**TwitterAPI.io**:
- `https://api.twitterapi.io/twitter/tweet/advanced_search`
- Devuelve metadatos completos y cursores

**OpenAI API**:
- Modelo: `gpt-4o-mini` o `gpt-4`
- Temperatura: 0.2
- Tokens máx.: 500
- Revisar límites de tasa y costos
