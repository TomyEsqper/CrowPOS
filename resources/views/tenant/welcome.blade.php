@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-4">
                    Bienvenido a {{ tenant('name') ?? 'CrowPOS' }}
                </h1>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Información del Tenant</h2>
                    <div class="bg-gray-100 p-4 rounded">
                        <p><strong>ID:</strong> {{ tenant('id') }}</p>
                        <p><strong>Nombre:</strong> {{ tenant('name') }}</p>
                        <p><strong>Email Admin:</strong> {{ tenant('admin_email') }}</p>
                        <p><strong>Color Primario:</strong> {{ tenant('branding.primary_color') ?? '#3B82F6' }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Funcionalidades Activas</h2>
                    <div class="grid grid-cols-2 gap-4">
                        @if(tenant('features.pos'))
                            <div class="bg-green-100 p-3 rounded">
                                <span class="text-green-800">✓ POS/Ventas</span>
                            </div>
                        @endif
                        
                        @if(tenant('features.inventory'))
                            <div class="bg-green-100 p-3 rounded">
                                <span class="text-green-800">✓ Inventario</span>
                            </div>
                        @endif
                        
                        @if(tenant('features.customers'))
                            <div class="bg-green-100 p-3 rounded">
                                <span class="text-green-800">✓ Clientes</span>
                            </div>
                        @endif
                        
                        @if(tenant('features.cash'))
                            <div class="bg-green-100 p-3 rounded">
                                <span class="text-green-800">✓ Caja</span>
                            </div>
                        @endif
                        
                        @if(tenant('features.reports'))
                            <div class="bg-blue-100 p-3 rounded">
                                <span class="text-blue-800">✓ Reportes Avanzados</span>
                            </div>
                        @else
                            <div class="bg-gray-100 p-3 rounded">
                                <span class="text-gray-600">- Reportes Avanzados (Premium)</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Navegación</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('pos.index') }}" class="btn-primary text-white px-4 py-2 rounded text-center">
                            Punto de Venta
                        </a>
                        <a href="{{ route('inventory.index') }}" class="btn-primary text-white px-4 py-2 rounded text-center">
                            Inventario
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn-primary text-white px-4 py-2 rounded text-center">
                            Clientes
                        </a>
                        <a href="{{ route('cash.index') }}" class="btn-primary text-white px-4 py-2 rounded text-center">
                            Caja
                        </a>
                    </div>
                </div>

                <div class="bg-yellow-100 p-4 rounded">
                    <h3 class="font-semibold text-yellow-800 mb-2">Información de Seguridad</h3>
                    <p class="text-yellow-700 text-sm">
                        Esta aplicación utiliza CSP (Content Security Policy) con nonce para mayor seguridad.
                        Todos los scripts y estilos inline están protegidos con nonces únicos por request.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script con nonce para demostrar CSP -->
<script nonce="{{ csp_nonce() }}">
    console.log('Tenant ID:', window.tenantConfig?.id);
    console.log('Tenant Name:', window.tenantConfig?.name);
    console.log('CSP Nonce:', '{{ csp_nonce() }}');
    
    // Ejemplo de funcionalidad específica del tenant
    document.addEventListener('DOMContentLoaded', function() {
        const primaryColor = window.tenantConfig?.branding?.primary_color || '#3B82F6';
        document.documentElement.style.setProperty('--primary-color', primaryColor);
    });
</script>
@endsection
