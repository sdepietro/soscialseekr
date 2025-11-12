# Configuración de Cron para X Finder

Esta guía proporciona instrucciones detalladas para configurar el sistema de cron de Laravel para ejecutar las búsquedas automatizadas de Twitter y el análisis IA.

## Descripción General

X Finder utiliza el **Laravel Task Scheduler** para ejecutar tareas programadas:

- **`twitter:search`**: Ejecuta búsquedas de Twitter cada minuto, guarda tweets y detecta spikes
- **`tweets:analyze-ai`**: Analiza tweets pendientes con IA cada minuto y envía notificaciones

## Requisitos Previos

- Acceso SSH al servidor
- Usuario con permisos para editar crontab
- PHP CLI disponible
- Proyecto Laravel completamente configurado

## Verificar que los Comandos Funcionan

Antes de configurar el cron, verifica que los comandos funcionen correctamente:

```bash
# Navegar al directorio del proyecto
cd /home/sergio/Develop/workspace/www/x_finder

# Listar todos los comandos disponibles
php artisan list

# Verificar que los nuevos comandos estén registrados
php artisan list | grep -E "(twitter:search|tweets:analyze-ai)"

# Ejecutar manualmente el comando de búsqueda
php artisan twitter:search

# Ejecutar manualmente el comando de análisis IA
php artisan tweets:analyze-ai

# Verificar el scheduler (muestra tareas programadas)
php artisan schedule:list
```

**Salida esperada de `schedule:list`**:
```
0 * * * * php artisan twitter:search .......... Next Due: 1 minute from now
0 * * * * php artisan tweets:analyze-ai ....... Next Due: 1 minute from now
```

## Configuración en Servidor Linux (sin Docker)

### Opción 1: Usando crontab del usuario actual

1. **Abrir el editor de crontab**:
   ```bash
   crontab -e
   ```

2. **Agregar la siguiente línea** (reemplaza `/ruta/completa` con tu ruta real):
   ```bash
   * * * * * cd /home/sergio/Develop/workspace/www/x_finder && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Guardar y salir** del editor (Ctrl+O, Enter, Ctrl+X en nano; :wq en vim)

4. **Verificar que el cron se agregó correctamente**:
   ```bash
   crontab -l
   ```

### Opción 2: Usando crontab del usuario www-data

Si tu aplicación corre bajo el usuario `www-data`:

```bash
sudo crontab -u www-data -e
```

Agregar:
```bash
* * * * * cd /home/sergio/Develop/workspace/www/x_finder && php artisan schedule:run >> /dev/null 2>&1
```

### Opción 3: Con logging para debugging

Para guardar logs de la ejecución del scheduler (útil durante configuración inicial):

```bash
* * * * * cd /home/sergio/Develop/workspace/www/x_finder && php artisan schedule:run >> /home/sergio/Develop/workspace/www/x_finder/storage/logs/cron.log 2>&1
```

**Nota**: Asegúrate de que el directorio `storage/logs` tenga permisos de escritura.

## Configuración en Docker (Contenedor ypf_bdots_php)

### Método 1: Crontab dentro del contenedor

1. **Acceder al contenedor**:
   ```bash
   docker exec -it ypf_bdots_php bash
   ```

2. **Instalar cron si no está disponible**:
   ```bash
   apt-get update && apt-get install -y cron
   ```

3. **Agregar crontab**:
   ```bash
   crontab -e
   ```

   Agregar:
   ```bash
   * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
   ```

   **Nota**: Ajusta `/var/www/html` según la ruta dentro del contenedor.

4. **Iniciar el servicio cron**:
   ```bash
   service cron start
   ```

5. **Verificar que cron esté corriendo**:
   ```bash
   service cron status
   ```

### Método 2: Modificar Dockerfile (Recomendado para producción)

Edita tu `Dockerfile` para incluir cron:

```dockerfile
# Instalar cron
RUN apt-get update && apt-get install -y cron

# Copiar archivo de crontab
COPY docker/crontab /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler
RUN crontab /etc/cron.d/laravel-scheduler

# Asegurarse de que cron inicie con el contenedor
CMD cron && apache2-foreground
# O si usas PHP-FPM:
# CMD cron && php-fpm
```

Crea el archivo `docker/crontab`:
```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

### Método 3: Supervisor (Mejor para producción)

Usa Supervisor para mantener `php artisan schedule:work` corriendo:

1. **Instalar supervisor en el contenedor**:
   ```bash
   apt-get install -y supervisor
   ```

2. **Crear configuración** en `/etc/supervisor/conf.d/laravel-scheduler.conf`:
   ```ini
   [program:laravel-scheduler]
   process_name=%(program_name)s
   command=php /var/www/html/artisan schedule:work
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/var/www/html/storage/logs/scheduler.log
   ```

3. **Recargar supervisor**:
   ```bash
   supervisorctl reread
   supervisorctl update
   supervisorctl start laravel-scheduler
   ```

## Configuración para Desarrollo Local

### Opción 1: Ejecutar el scheduler manualmente cuando sea necesario

```bash
# Terminal dedicada para el scheduler (ejecuta cada minuto)
php artisan schedule:work
```

Este comando mantiene el scheduler corriendo y ejecuta las tareas programadas cada minuto. Presiona Ctrl+C para detener.

### Opción 2: Ejecutar comandos directamente para testing

```bash
# Ejecutar búsquedas manualmente
php artisan twitter:search

# Ejecutar análisis IA manualmente
php artisan tweets:analyze-ai

# Ver las tareas programadas
php artisan schedule:list
```

### Opción 3: Integrar en composer run dev (no recomendado)

Si quisieras agregar el scheduler a `composer run dev`, editarías `composer.json`:

```json
{
  "scripts": {
    "dev": [
      "Composer\\Config::disableProcessTimeout",
      "@php artisan serve & @php artisan queue:listen --tries=1 & @php artisan pail --timeout=0 & @php artisan schedule:work & npm run dev"
    ]
  }
}
```

**Nota**: Esto puede hacer difícil detener todos los procesos. Es mejor usar terminales separadas.

## Verificación de Funcionamiento

### 1. Verificar que el cron del servidor ejecuta el scheduler

Espera 1-2 minutos después de configurar el crontab y verifica los logs:

```bash
# Ver logs generales de Laravel
tail -f storage/logs/laravel.log

# Ver logs específicos del scheduler (si configuraste logging)
tail -f storage/logs/cron.log

# Ver logs de las búsquedas de Twitter
grep "CronSearches" storage/logs/laravel.log

# Ver logs del análisis IA
grep "CronIaAnalyzer" storage/logs/laravel.log
```

### 2. Verificar ejecuciones en la base de datos

```bash
php artisan tinker
```

```php
// Ver última ejecución de búsquedas
DB::table('searchs')->select('id', 'name', 'last_run_at')->get();

// Ver tweets creados recientemente
DB::table('tweets')->latest('created_at')->take(5)->get(['id', 'text', 'created_at']);

// Ver tweets analizados por IA recientemente
DB::table('tweets')->where('ia_analyzed', 1)->latest('updated_at')->take(5)->get(['id', 'ia_score', 'updated_at']);
```

### 3. Monitorear ejecución en tiempo real

```bash
# Ver logs en tiempo real
php artisan pail --timeout=0

# O con grep para filtrar
php artisan pail --timeout=0 | grep -E "(twitter:search|tweets:analyze-ai)"
```

### 4. Verificar que el worker de colas está corriendo

Los comandos despachan jobs (como `NotifyHighScoreTweet`), así que asegúrate de que el queue worker esté activo:

```bash
# Verificar jobs pendientes
php artisan queue:failed

# Ver estadísticas de la cola
php artisan queue:monitor

# Iniciar worker si no está corriendo
php artisan queue:work
# O mejor aún para desarrollo:
php artisan queue:listen --tries=1
```

## Troubleshooting

### El cron no se ejecuta

**Problema**: El scheduler no ejecuta las tareas.

**Soluciones**:
1. Verifica que el cron esté configurado correctamente: `crontab -l`
2. Asegúrate de que la ruta al proyecto sea absoluta
3. Verifica que PHP esté en el PATH: `which php`
4. Prueba con la ruta completa a PHP: `/usr/bin/php artisan schedule:run`
5. Revisa los logs del sistema: `grep CRON /var/log/syslog`

### Permisos insuficientes

**Problema**: `Permission denied` al ejecutar comandos.

**Soluciones**:
```bash
# Dar permisos correctos a storage y bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# O si usas tu usuario:
chown -R $USER:$USER storage bootstrap/cache
```

### Los comandos no aparecen en `artisan list`

**Problema**: Los comandos personalizados no se muestran.

**Soluciones**:
```bash
# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear

# Optimizar autoloader
composer dump-autoload

# Verificar que los archivos estén en la ubicación correcta
ls -la app/Console/Commands/
```

### El scheduler se ejecuta pero los comandos fallan

**Problema**: El cron corre pero los comandos tienen errores.

**Soluciones**:
1. Ejecuta los comandos manualmente para ver el error completo:
   ```bash
   php artisan twitter:search
   php artisan tweets:analyze-ai
   ```

2. Verifica las dependencias:
   ```bash
   # TwitterService disponible
   php artisan tinker
   >>> app(App\Services\TwitterService::class);

   # ChatGptService disponible
   >>> app(App\Services\ChatGptService::class);
   ```

3. Verifica las variables de entorno:
   ```bash
   php artisan config:show | grep -E "(TWITTERAPIIIO|OPENAI)"
   ```

4. Revisa la conexión a la base de datos:
   ```bash
   php artisan migrate:status
   ```

### En Docker, el cron no persiste

**Problema**: Después de reiniciar el contenedor, el cron desaparece.

**Solución**: Usa el Método 2 (Dockerfile) o Método 3 (Supervisor) descritos arriba para que el cron se configure automáticamente al iniciar el contenedor.

### Jobs en cola no se procesan

**Problema**: Los tweets se analizan pero no se envían notificaciones.

**Soluciones**:
```bash
# Verificar que el worker esté corriendo
ps aux | grep "queue:work"

# Ver jobs fallidos
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all

# Limpiar jobs fallidos si es necesario
php artisan queue:flush
```

## Logs Importantes

| Tipo de Log | Ubicación | Descripción |
|-------------|-----------|-------------|
| Laravel General | `storage/logs/laravel.log` | Todos los logs de la aplicación |
| Cron Scheduler | `storage/logs/cron.log` | Si configuraste logging del cron |
| Sistema (Linux) | `/var/log/syslog` o `/var/log/cron.log` | Logs del sistema sobre ejecuciones de cron |
| Queue Worker | Output de `queue:work` | Logs de procesamiento de jobs |
| Pail (tiempo real) | Terminal | Logs en vivo con `php artisan pail` |

## Comandos Útiles de Referencia

```bash
# Ejecutar scheduler una sola vez (útil para testing)
php artisan schedule:run

# Ejecutar scheduler continuamente cada minuto
php artisan schedule:work

# Ver todas las tareas programadas
php artisan schedule:list

# Ejecutar un comando específico
php artisan twitter:search
php artisan tweets:analyze-ai

# Ver logs en tiempo real
php artisan pail --timeout=0

# Limpiar cachés si hay problemas
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Ver estado de migraciones
php artisan migrate:status

# Ver cola de jobs
php artisan queue:monitor
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all
```

## Mejores Prácticas

1. **Usar `schedule:work` en desarrollo**: En lugar de configurar crontab local, usa `php artisan schedule:work` en una terminal separada durante el desarrollo.

2. **Configurar logging temporal**: Durante la configuración inicial, redirige la salida a un archivo de log para detectar problemas:
   ```bash
   * * * * * cd /path/to/project && php artisan schedule:run >> /path/to/project/storage/logs/cron.log 2>&1
   ```

3. **Remover logging en producción**: Una vez que todo funcione, redirige a `/dev/null` para evitar archivos de log gigantes:
   ```bash
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Monitorear el queue worker**: Los comandos despachan jobs, asegúrate de que siempre haya un worker corriendo:
   ```bash
   # Usar supervisor o similar en producción
   php artisan queue:work --daemon
   ```

5. **Usar Horizon (opcional)**: Para una gestión más avanzada de colas, considera Laravel Horizon:
   ```bash
   composer require laravel/horizon
   php artisan horizon:install
   php artisan horizon
   ```

## Resumen de Configuración Rápida

**Para desarrollo local**:
```bash
# Terminal 1: Servidor
php artisan serve

# Terminal 2: Queue worker
php artisan queue:listen --tries=1

# Terminal 3: Scheduler
php artisan schedule:work

# Terminal 4: Logs en tiempo real
php artisan pail --timeout=0
```

**Para servidor Linux**:
```bash
# Agregar al crontab
crontab -e

# Línea a agregar:
* * * * * cd /home/sergio/Develop/workspace/www/x_finder && php artisan schedule:run >> /dev/null 2>&1
```

**Para Docker**:
```bash
# Acceder al contenedor y agregar cron
docker exec -it ypf_bdots_php bash
apt-get update && apt-get install -y cron
crontab -e
# Agregar la línea del cron
service cron start
```

---

**Última actualización**: 2025-01-12

Para más información sobre el Laravel Task Scheduler, consulta la [documentación oficial de Laravel](https://laravel.com/docs/scheduling).
