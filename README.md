# X Finder

Sistema de monitoreo y análisis de contenido de Twitter/X con inteligencia artificial.

## Descripción

X Finder es una plataforma desarrollada en Laravel que permite realizar búsquedas automatizadas en Twitter/X, almacenar tweets y sus métricas, rastrear cambios históricos y analizar el contenido mediante IA (ChatGPT/OpenAI). El sistema está diseñado para monitorear conversaciones, tendencias y menciones de forma continua.

## Características principales

- **Búsquedas automatizadas**: Configuración de queries de búsqueda que se ejecutan cada X minutos
- **Almacenamiento de tweets**: Guarda tweets con toda su información (texto, métricas, autor, etc.)
- **Tracking de métricas**: Histórico de cambios en likes, retweets, replies, views, etc.
- **Análisis con IA**: Integración con OpenAI para analizar y puntuar tweets (score 0-100)
- **Gestión de cuentas**: Almacena información de perfiles de Twitter (autores)
- **API RESTful**: Endpoints protegidos con autenticación JWT
- **Sistema de permisos**: Control de acceso basado en roles y permisos
- **Documentación API**: Swagger/OpenAPI integrado

## Tecnologías

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de datos**: SQLite (configurable a MySQL/PostgreSQL)
- **Autenticación**: JWT (tymon/jwt-auth)
- **IA**: OpenAI PHP Client
- **Documentación**: L5-Swagger
- **Procesamiento de imágenes**: Intervention Image
- **API Externa**: TwitterAPI.io

## Requisitos

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite/MySQL/PostgreSQL
- Cuenta en [TwitterAPI.io](https://twitterapi.io)
- API Key de OpenAI (para análisis con IA)

## Instalación

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd x_finder
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node:
```bash
npm install
```

4. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

5. Generar la clave de la aplicación:
```bash
php artisan key:generate
```

6. Configurar las variables de entorno en `.env`:
```env
# Base de datos
DB_CONNECTION=sqlite

# Twitter API (twitterapi.io)
TWITTER_API_BASE_URL=https://api.twitterapi.io
TWITTER_API_KEY=your_api_key_here

# OpenAI
OPENAI_API_KEY=your_openai_key_here

# JWT
JWT_SECRET=your_jwt_secret
```

7. Ejecutar las migraciones:
```bash
php artisan migrate
```

8. Generar la clave JWT:
```bash
php artisan jwt:secret
```

## Uso

### Iniciar el servidor de desarrollo

Modo completo (servidor + cola + logs + Vite):
```bash
composer run dev
```

O de forma individual:
```bash
php artisan serve
php artisan queue:work
npm run dev
```

### Credenciales por defecto

- **Email**: admin@xfinder.com
- **Password**: 123456

**IMPORTANTE**: Cambiar estas credenciales en producción.

## Estructura de datos

### Búsquedas (Searches)
Configuración de queries que se ejecutan periódicamente:
- Query de búsqueda (soporta operadores avanzados)
- Tipo de búsqueda (Latest/Top)
- País y idioma
- Frecuencia de ejecución (minutos)
- Filtros mínimos (likes, retweets)
- Cuentas específicas a monitorear

### Tweets
Almacena información completa:
- Contenido del tweet
- Métricas (likes, RTs, replies, views, bookmarks)
- Relación con cuenta autor
- Análisis de IA (score y razón)
- Entidades (hashtags, URLs, menciones)
- Información de respuestas/quotes/retweets

### Cuentas (Accounts)
Perfiles de Twitter:
- Datos de perfil (username, nombre, bio)
- Verificación y badges
- Estadísticas (followers, following, tweets)
- Imágenes de perfil y portada

### Historial (Tweet History)
Seguimiento de cambios:
- Snapshots de métricas
- Diferencias entre versiones
- Razón del cambio

## API Endpoints

### Autenticación
```
POST /api/v1/auth/login
POST /api/v1/auth/logout
POST /api/v1/auth/password_request
POST /api/v1/auth/change_password
```

### Usuarios (requiere autenticación)
```
GET  /api/v1/users/me
PUT  /api/v1/users/me
PUT  /api/v1/users/password
```

### Búsquedas y datos
```
GET  /api/v1/places
POST /api/v1/orders
GET  /api/v1/orders/{id}
```

Documentación completa disponible en `/api/documentation` (Swagger UI)

## Configuración de búsquedas

Las búsquedas se configuran en la tabla `searchs` con campos como:
- `query`: Query de búsqueda con operadores de Twitter
- `query_type`: 'Latest' o 'Top'
- `run_every_minutes`: Frecuencia de ejecución
- `min_like_count`: Filtro mínimo de likes
- `country`: País objetivo (ej: 'AR', 'ES', 'MX')
- `lang`: Idioma (ej: 'es', 'en')

## Testing

Ejecutar tests:
```bash
composer test
```

O directamente:
```bash
php artisan test
```

## Docker

El proyecto incluye configuración Docker:
```bash
docker-compose up -d
```

## Estructura del proyecto

```
x_finder/
├── app/
│   ├── Http/Controllers/Api/  # Controladores de API
│   ├── Models/                # Modelos Eloquent
│   └── Services/              # Servicios (Twitter, ChatGPT)
├── config/                    # Archivos de configuración
├── database/
│   ├── migrations/            # Migraciones de BD
│   └── seeders/               # Seeders
├── public/                    # Archivos públicos
├── resources/                 # Vistas y assets
├── routes/
│   └── api.php               # Rutas de API
└── tests/                    # Tests automatizados
```

## Seguridad

- Autenticación JWT para todos los endpoints protegidos
- Soft deletes en todas las tablas principales
- Validación de entrada en controllers
- Tokens de recuperación de contraseña
- No commitear archivo `.env` con credenciales reales

## Contribución

Este es un proyecto privado. Para contribuir:
1. Crear un branch desde `main`
2. Realizar cambios
3. Ejecutar tests
4. Crear Pull Request

## Licencia

MIT License

## Soporte

Para reportar problemas o sugerencias, contactar al equipo de desarrollo.

---

Desarrollado con Laravel 12
