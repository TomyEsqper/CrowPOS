# Configuración de Hosts Locales para Desarrollo

Para probar el sistema multi-tenant en desarrollo local, necesitas configurar los subdominios en tu archivo de hosts.

## Windows

Edita el archivo `C:\Windows\System32\drivers\etc\hosts` como administrador:

```
127.0.0.1 localhost
127.0.0.1 demo.localhost
127.0.0.1 cliente1.localhost
127.0.0.1 cliente2.localhost
127.0.0.1 midominio.com
```

## Linux/macOS

Edita el archivo `/etc/hosts`:

```
127.0.0.1 localhost
127.0.0.1 demo.localhost
127.0.0.1 cliente1.localhost
127.0.0.1 cliente2.localhost
127.0.0.1 midominio.com
```

## Comandos para crear tenants de prueba

```bash
# Crear tenant demo
php artisan tenant:create "Empresa Demo" "demo.localhost" --admin-email="admin@demo.localhost" --admin-password="password123"

# Crear tenant cliente1
php artisan tenant:create "Cliente 1" "cliente1.localhost" --admin-email="admin@cliente1.localhost" --admin-password="password123"

# Crear tenant cliente2
php artisan tenant:create "Cliente 2" "cliente2.localhost" --admin-email="admin@cliente2.localhost" --admin-password="password123"
```

## URLs de acceso

- **Landlord (Superadmin)**: http://localhost/crowPOS
- **Tenant Demo**: http://demo.localhost
- **Tenant Cliente 1**: http://cliente1.localhost
- **Tenant Cliente 2**: http://cliente2.localhost

## Verificación

Para verificar que los subdominios funcionan correctamente:

```bash
# Verificar resolución DNS
nslookup demo.localhost
nslookup cliente1.localhost

# Verificar acceso HTTP
curl -I http://demo.localhost
curl -I http://cliente1.localhost
```

## Notas importantes

1. **Flush DNS**: Después de modificar el archivo hosts, ejecuta `ipconfig /flushdns` en Windows o `sudo dscacheutil -flushcache` en macOS.

2. **Nginx reload**: Si cambias la configuración de Nginx, ejecuta `docker-compose restart nginx`.

3. **SSL en desarrollo**: Para HTTPS en desarrollo, puedes usar mkcert o similar.

4. **Producción**: En producción, configura DNS real y certificados SSL wildcard.
