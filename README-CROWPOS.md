# CrowPOS Multi-Tenant POS System

Sistema POS multi-tenant desarrollado con Laravel 11, Docker, PostgreSQL y Redis.

## 🚀 Características

- **Multi-tenant** con aislamiento por base de datos
- **Subdominios únicos** por cliente
- **Branding dinámico** por tenant
- **Módulos activables** por feature flags
- **Seguridad robusta** con Argon2id, 2FA, rate limiting
- **Infraestructura Docker** completa

## 📋 Requisitos

- Docker y Docker Compose
- PHP 8.3+
- Composer
- PostgreSQL 15+
- Redis 7+

## 🛠️ Instalación

### 1. Clonar y configurar el proyecto

```bash
# El proyecto ya está configurado con Laravel 11
# Las dependencias ya están instaladas
```

### 2. Configurar variables de entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar las variables necesarias
# DB_CONNECTION=landlord
# DB_HOST=postgres
# DB_DATABASE=crowpos_landlord
# DB_USERNAME=crowpos
# DB_PASSWORD=crowpos_password
```

### 3. Generar clave de aplicación

```bash
php artisan key:generate
```

### 4. Levantar los servicios con Docker

```bash
docker-compose up -d
```

### 5. Ejecutar migraciones del landlord

```bash
# Conectar al contenedor de la aplicación
docker exec -it crowpos_app bash

# Ejecutar migraciones del landlord
php artisan migrate --path=database/migrations/landlord --force
```

### 6. Crear un tenant de prueba

```bash
# Crear tenant con comando personalizado
php artisan tenant:create "Empresa Demo" "demo.localhost" --admin-email="admin@demo.localhost" --admin-password="password123"
```

## 🏗️ Estructura del Proyecto

```
├── app/
│   ├── Console/Commands/
│   │   └── CreateTenantCommand.php    # Comando para crear tenants
│   └── Providers/
├── config/
│   ├── tenancy.php                    # Configuración multi-tenant
│   └── database.php                   # Conexiones landlord/tenant
├── database/migrations/
│   ├── landlord/                      # Migraciones del landlord
│   └── tenant/                        # Migraciones de cada tenant
├── docker/                            # Configuración Docker
│   ├── nginx/
│   ├── php/
│   ├── postgres/
│   └── supervisor/
├── routes/
│   ├── web.php                        # Rutas del landlord
│   ├── tenant.php                     # Rutas de los tenants
│   └── api.php                        # Rutas API
└── docker-compose.yml                 # Servicios Docker
```

## 🔧 Comandos Útiles

### Gestión de Tenants

```bash
# Crear un nuevo tenant (completo con migraciones y seeds)
php artisan tenant:create "Nombre Empresa" "subdominio.midominio.com" --admin-email="admin@empresa.com"

# Ejecutar migraciones en todos los tenants
php artisan tenants:migrate

# Ejecutar seeders en todos los tenants
php artisan tenants:seed

# Listar todos los tenants
php artisan tenants:list
```

### Backups y Mantenimiento

```bash
# Crear backups de todos los tenants
php artisan tenants:backup

# Crear backups comprimidos
php artisan tenants:backup --compress

# Configurar retención personalizada
php artisan tenants:backup --retention-days=14 --cold-retention-days=60
```

### Colas y Workers

```bash
# Verificar estado de colas
php artisan queue:work --once

# Procesar colas fallidas
php artisan queue:retry all

# Limpiar colas fallidas
php artisan queue:flush
```

### Testing

```bash
# Ejecutar tests de seguridad
php artisan test --filter="TenantSessionIsolationTest"

# Ejecutar tests de restricción de acceso
php artisan test --filter="FilamentAccessRestrictionTest"

# Ejecutar tests de CSP nonce
php artisan test --filter="CSPNonceTest"

# Ejecutar tests de prefijo de caché por tenant
php artisan test --filter="TenantCachePrefixTest"

# Ejecutar tests de health check
php artisan test --filter="HealthCheckTest"

# Ejecutar tests de X-Request-Id
php artisan test --filter="RequestIdTest"

# Ejecutar tests principales (CSP, Health, RequestId)
php artisan test --filter="CSPNonceTest|HealthCheckTest|RequestIdTest"

# Ejecutar todos los tests
php artisan test
```

## 🌐 Acceso a la Aplicación

### URLs de Desarrollo

- **Landlord (Superadmin)**: `http://localhost/crowPOS`
- **Tenant Demo**: `http://demo.localhost`
- **Tenant Cliente 1**: `http://cliente1.localhost`
- **Tenant Cliente 2**: `http://cliente2.localhost`

### Configuración de Hosts Locales

Para desarrollo local, configura los subdominios en tu archivo hosts:

**Windows**: `C:\Windows\System32\drivers\etc\hosts`
**Linux/macOS**: `/etc/hosts`

```
127.0.0.1 localhost
127.0.0.1 demo.localhost
127.0.0.1 cliente1.localhost
127.0.0.1 cliente2.localhost
127.0.0.1 midominio.com
```

Ver `docker/nginx/hosts-setup.md` para instrucciones detalladas.

## 📦 Dependencias Instaladas

- **stancl/tenancy**: Multi-tenancy
- **livewire/livewire**: Frontend reactivo
- **spatie/laravel-permission**: Roles y permisos
- **barryvdh/laravel-dompdf**: Generación de PDF
- **maatwebsite/excel**: Exportar/importar Excel
- **mike42/escpos-php**: Impresión ESC/POS
- **filament/filament**: Panel administrativo
- **predis/predis**: Cliente Redis
- **sentry/sentry-laravel**: Monitoreo de errores

## 🔒 Seguridad

### Implementaciones de Seguridad

- **Hash**: Argon2id (configurado en Laravel)
- **Autenticación**: Sesiones aisladas por tenant con cookies únicas
- **Rate limiting**: 5 intentos/min por tenant+email+IP en login
- **2FA TOTP**: Opcional por usuario (preparado para implementar)
- **Cabeceras de seguridad**: 
  - HSTS (solo en producción con HTTPS)
  - X-Frame-Options: DENY
  - X-Content-Type-Options: nosniff
  - Referrer-Policy: strict-origin-when-cross-origin
  - CSP estricta con nonce
- **Auditoría**: Logs estructurados (JSON) para acciones críticas
- **Filament restringido**: Solo accesible en dominio landlord
- **Aislamiento de datos**: Base de datos separada por tenant

### Middleware de Seguridad

- `RequestId`: Genera UUID único por request para correlación de logs
- `TenantSessionCookie`: Aislamiento de sesiones por tenant
- `SecurityHeaders`: Cabeceras de seguridad globales
- `RestrictFilamentToLandlord`: Bloqueo de admin panel en tenants
- `RateLimitLogin`: Rate limiting en autenticación
- `ContentSecurityPolicy`: CSP con nonce único por request
- `TenantCachePrefix`: Prefijo de caché aislado por tenant

### X-Request-Id Middleware

- **UUID v4 único** por request para trazabilidad
- **Header `X-Request-Id`** en todas las respuestas
- **Correlación de logs** automática con contexto
- **Proxy-friendly**: respeta `X-Request-Id` entrante si existe

#### Uso en Logs:
```php
// Los logs automáticamente incluyen el request_id
Log::info('User action performed', ['user_id' => 123]);
// Output: {"message":"User action performed","user_id":123,"request_id":"550e8400-e29b-41d4-a716-446655440000"}
```

### CSP (Content Security Policy)

- **Nonce único** por request para scripts y estilos inline
- **Helper `csp_nonce()`** disponible en Blade templates
- **Directivas estrictas** sin `unsafe-inline` ni `unsafe-eval`
- **Compatibilidad** con Livewire y Alpine.js
- **WebSocket support** para Livewire en tiempo real

#### Directivas CSP Implementadas:
```text
default-src 'self';
script-src 'self' 'nonce-<nonce>';
style-src 'self' 'nonce-<nonce>' https://fonts.googleapis.com;
font-src 'self' data: https://fonts.gstatic.com;
img-src 'self' https: data: blob:;
connect-src 'self' ws: wss:;
frame-ancestors 'none';
```

#### Uso en Blade Templates:
```html
<script nonce="{{ csp_nonce() }}">
    console.log('Script protegido con CSP nonce');
</script>

<style nonce="{{ csp_nonce() }}">
    .custom-style { color: blue; }
</style>
```

## 🏥 Health Check Endpoint

### `/healthz` - Monitoreo de Salud

El sistema incluye un endpoint de health check completo para monitoreo:

```bash
# Verificar estado del sistema
curl http://localhost/healthz

# Respuesta exitosa (HTTP 200)
{
  "status": "healthy",
  "checks": {
    "db_landlord": "healthy",
    "db_tenant": "skipped",
    "redis": "healthy", 
    "horizon": "skipped",
    "storage": "healthy"
  },
  "version": {
    "app": "0.1.0",
    "git_sha": "abcdef1234"
  },
  "timestamp": "2024-01-15T10:30:45.123456Z"
}

# Respuesta con problemas (HTTP 503)
{
  "status": "unhealthy",
  "checks": {
    "db_landlord": "healthy",
    "db_tenant": "skipped",
    "redis": "unhealthy",
    "horizon": "skipped", 
    "storage": "healthy"
  },
  "version": {
    "app": "0.1.0",
    "git_sha": "abcdef1234"
  },
  "timestamp": "2024-01-15T10:30:45.123456Z"
}
```

### Características del Health Check

- ✅ **Sin autenticación** requerida
- ✅ **Rate limiting** (30 req/min por IP)
- ✅ **Verificación completa**:
  - Base de datos landlord
  - Base de datos tenant (si está activo)
  - Conexión Redis
  - Estado de Horizon (si está instalado)
  - Permisos de escritura en storage
- ✅ **Códigos HTTP** apropiados (200/503)
- ✅ **Timestamp ISO8601** para trazabilidad
- ✅ **Información de versión** (app, git_sha)
- ✅ **X-Request-Id** para correlación de logs
- ✅ **Logs seguros** sin exponer datos sensibles

### Integración con Monitoreo

```bash
# Ejemplo para Prometheus/Grafana
curl -s http://localhost/healthz | jq '.status == "healthy"'

# Ejemplo para Docker health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD curl -f http://localhost/healthz || exit 1
```

## 🔧 Variables de Entorno

### Variables de Versión
```bash
# Versión de la aplicación
APP_VERSION=0.1.0

# SHA del commit de Git (para trazabilidad)
GIT_SHA=abcdef1234
```

### Variables de Base de Datos
```bash
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=crowpos_landlord
DB_USERNAME=crowpos
DB_PASSWORD=your_password
```

### Variables de Redis
```bash
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
```

## 🚀 Próximos Pasos

1. **Configurar dominios reales** en producción
2. **Implementar vistas** para landlord y tenants
3. **Crear modelos** para productos, ventas, inventario
4. **Configurar Filament** para el panel administrativo
5. **Implementar módulos POS** (ventas, inventario, caja)
6. **Configurar SSL** con Let's Encrypt
7. **Implementar backups** automáticos

## 📝 Notas

- El proyecto está configurado para desarrollo local
- Para producción, configurar dominios reales y SSL
- Los tenants se crean con bases de datos separadas
- Cada tenant tiene su propio subdominio
- El branding se carga dinámicamente por tenant

## 🆘 Soporte

Para problemas o dudas, revisar:
1. Logs de Docker: `docker-compose logs`
2. Logs de Laravel: `storage/logs/laravel.log`
3. Estado de los contenedores: `docker-compose ps`
