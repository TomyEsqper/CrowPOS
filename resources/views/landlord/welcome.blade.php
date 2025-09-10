@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-4">
                    Panel de Administración - CrowPOS Multi-Tenant
                </h1>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Sistema Multi-Tenant</h2>
                    <p class="text-gray-600 mb-4">
                        Bienvenido al panel de administración del sistema POS multi-tenant.
                        Desde aquí puedes gestionar todos los tenants, dominios y configuraciones.
                    </p>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Funcionalidades del Landlord</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('tenants.index') }}" class="bg-blue-100 p-4 rounded hover:bg-blue-200 transition">
                            <h3 class="font-semibold text-blue-800">Gestión de Tenants</h3>
                            <p class="text-blue-600 text-sm">Crear, editar y administrar empresas</p>
                        </a>
                        
                        <a href="{{ route('domains.index') }}" class="bg-green-100 p-4 rounded hover:bg-green-200 transition">
                            <h3 class="font-semibold text-green-800">Gestión de Dominios</h3>
                            <p class="text-green-600 text-sm">Configurar subdominios y DNS</p>
                        </a>
                        
                        <a href="{{ route('features.index') }}" class="bg-purple-100 p-4 rounded hover:bg-purple-200 transition">
                            <h3 class="font-semibold text-purple-800">Feature Flags</h3>
                            <p class="text-purple-600 text-sm">Activar/desactivar módulos por tenant</p>
                        </a>
                        
                        <a href="{{ route('reports.index') }}" class="bg-orange-100 p-4 rounded hover:bg-orange-200 transition">
                            <h3 class="font-semibold text-orange-800">Reportes Globales</h3>
                            <p class="text-orange-600 text-sm">Métricas y estadísticas del sistema</p>
                        </a>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Comandos Útiles</h2>
                    <div class="bg-gray-100 p-4 rounded">
                        <h3 class="font-semibold mb-2">Crear un nuevo tenant:</h3>
                        <code class="bg-gray-200 px-2 py-1 rounded text-sm">
                            php artisan tenant:create "Nombre Empresa" "subdominio.localhost"
                        </code>
                        
                        <h3 class="font-semibold mb-2 mt-4">Crear backups:</h3>
                        <code class="bg-gray-200 px-2 py-1 rounded text-sm">
                            php artisan tenants:backup --compress
                        </code>
                    </div>
                </div>

                <div class="bg-blue-100 p-4 rounded">
                    <h3 class="font-semibold text-blue-800 mb-2">Información de Seguridad</h3>
                    <p class="text-blue-700 text-sm">
                        Este panel está protegido con CSP (Content Security Policy) y solo es accesible desde el dominio principal.
                        Los subdominios de tenants no pueden acceder a esta interfaz administrativa.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script con nonce para demostrar CSP en landlord -->
<script nonce="{{ csp_nonce() }}">
    console.log('Landlord Panel - CSP Nonce:', '{{ csp_nonce() }}');
    
    // Funcionalidad específica del landlord
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Landlord panel loaded successfully');
        
        // Ejemplo de configuración global
        window.landlordConfig = {
            environment: '{{ app()->environment() }}',
            version: '1.0.0',
            multiTenant: true
        };
    });
</script>
@endsection
