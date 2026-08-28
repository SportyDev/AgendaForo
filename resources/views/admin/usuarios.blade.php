@extends('layouts.app')

@section('titulo', 'Usuarios | SRA')
@section('titulo_pagina', 'Usuarios')
@section('subtitulo_pagina', 'Usuarios, seguridad y bitácora del sistema')

@section('acciones_cabecera')
<button type="button" onclick="openUsersModal('modalNuevoUsuario')" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800">
    <span class="material-symbols-rounded text-[20px]">person_add</span>
    Nuevo usuario
</button>
@endsection

@section('contenido')
@php
    $tabActual = in_array(request('tab'), ['usuarios', 'bitacora'], true) ? request('tab') : 'usuarios';

    $rolLabel = static fn (?string $rol): string => match ($rol) {
        'admin' => 'Administrador',
        'solicitante' => 'Solicitante',
        default => ucfirst((string) $rol),
    };

    $rolClass = static fn (?string $rol): string => match ($rol) {
        'admin' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'solicitante' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        default => 'border-slate-200 bg-slate-100 text-slate-600',
    };

    $estadoLabel = static fn (?string $estado): string => match ($estado) {
        'activo' => 'Activo',
        'suspendido' => 'Suspendido',
        default => ucfirst((string) $estado),
    };

    $estadoClass = static fn (?string $estado): string => match ($estado) {
        'activo' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'suspendido' => 'border-amber-200 bg-amber-50 text-amber-700',
        default => 'border-slate-200 bg-slate-100 text-slate-600',
    };

    $nivelClass = static fn (?string $nivel): string => match ($nivel) {
        'critica' => 'border-red-200 bg-red-50 text-red-700',
        'importante' => 'border-amber-200 bg-amber-50 text-amber-700',
        default => 'border-sky-200 bg-sky-50 text-sky-700',
    };

    $nivelLabel = static fn (?string $nivel): string => match ($nivel) {
        'critica' => 'Crítica',
        'importante' => 'Importante',
        default => 'Operativa',
    };
@endphp

<div class="space-y-5">
    @if (session('success'))
        <div data-auto-dismiss="5000" class="users-notification fixed bottom-5 right-5 z-[150] max-w-md rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl transition duration-300">
            <div class="flex items-start gap-3"><span class="material-symbols-rounded text-emerald-600">check_circle</span><div class="min-w-0 flex-1"><p class="font-black text-slate-900">Operación completada</p><p class="mt-1 text-sm text-slate-600">{{ session('success') }}</p></div><button type="button" onclick="dismissUsersNotification(this)" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100"><span class="material-symbols-rounded text-[20px]">close</span></button></div>
        </div>
    @endif

    @if ($errors->any())
        <div data-auto-dismiss="8000" class="users-notification fixed bottom-5 right-5 z-[150] max-w-md rounded-2xl border border-red-200 bg-white p-4 shadow-2xl transition duration-300">
            <div class="flex items-start gap-3"><span class="material-symbols-rounded text-red-600">warning</span><div class="min-w-0 flex-1"><p class="font-black text-slate-900">Revisa la información capturada</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-600">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div><button type="button" onclick="dismissUsersNotification(this)" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100"><span class="material-symbols-rounded text-[20px]">close</span></button></div>
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 pt-4 sm:px-6">
            <div class="flex gap-2 overflow-x-auto pb-3">
                <button type="button" data-users-tab="usuarios" onclick="setUsersTab('usuarios')" class="users-tab inline-flex items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-bold">
                    <span class="material-symbols-rounded text-[20px]">manage_accounts</span>
                    Usuarios
                </button>
                <button type="button" data-users-tab="bitacora" onclick="setUsersTab('bitacora')" class="users-tab inline-flex items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-bold">
                    <span class="material-symbols-rounded text-[20px]">history</span>
                    Bitácora
                </button>
            </div>
        </div>

        <div id="users-tab-usuarios" class="users-panel p-4 sm:p-6">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <input type="hidden" name="tab" value="usuarios">
                <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Buscar usuario</label>
                        <div class="relative"><span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span><input name="search" value="{{ request('search') }}" type="search" placeholder="Nombre, correo o teléfono..." class="w-full rounded-xl border-slate-200 pl-10 text-sm focus:border-primary focus:ring-primary"></div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Estado</label>
                        <select name="estado" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"><option value="todos">Todos</option><option value="activo" @selected(request('estado') === 'activo')>Activos</option><option value="suspendido" @selected(request('estado') === 'suspendido')>Suspendidos</option></select>
                    </div>
                </div>
                <div class="flex gap-2"><a href="{{ route('admin.usuarios.index', ['tab' => 'usuarios']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Limpiar</a><button class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800"><span class="material-symbols-rounded text-[19px]">filter_alt</span>Filtrar</button></div>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200"><thead class="bg-slate-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Usuario</th>
                    <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Contacto</th>
                    <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Rol</th>
                    <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Estado</th>
                    <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Último acceso</th>
                    <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acciones</th>
                </tr></thead><tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($usuarios as $usuario)
                        @php
                            $detalleUsuario = [
                                'name' => $usuario->name,
                                'email' => $usuario->email,
                                'telefono' => $usuario->telefono,
                                'role' => $rolLabel($usuario->role),
                                'estado' => $estadoLabel($usuario->estado),
                                'ultimo_acceso' => $usuario->ultimo_acceso_at ? \Illuminate\Support\Carbon::parse($usuario->ultimo_acceso_at)->format('d/m/Y H:i') : 'Sin accesos registrados',
                                'ultima_ip' => $usuario->ultima_ip ?: 'Sin registro',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3"><div class="flex items-center gap-3"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-black text-white">{{ strtoupper(substr($usuario->name, 0, 1)) }}</div><div class="min-w-0"><p class="truncate font-bold text-slate-900">{{ $usuario->name }}</p><p class="mt-0.5 text-[11px] text-slate-500">Registrado: {{ $usuario->created_at?->format('d/m/Y') }}</p></div></div></td>
                            <td class="px-3 py-3"><p class="truncate text-[12px] font-semibold text-slate-700">{{ $usuario->email }}</p><p class="mt-1 text-[11px] text-slate-500">{{ $usuario->telefono ?: 'Sin teléfono' }}</p></td>
                            <td class="px-3 py-3"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $rolClass($usuario->role) }}">{{ $rolLabel($usuario->role) }}</span></td>
                            <td class="px-3 py-3"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $estadoClass($usuario->estado) }}">{{ $estadoLabel($usuario->estado) }}</span></td>
                            <td class="px-3 py-3"><p class="text-[12px] font-semibold text-slate-700">{{ $usuario->ultimo_acceso_at ? \Illuminate\Support\Carbon::parse($usuario->ultimo_acceso_at)->format('d/m/Y H:i') : 'Sin accesos' }}</p><p class="mt-1 text-xs text-slate-400">IP: {{ $usuario->ultima_ip ?: 'Sin registro' }}</p></td>
                            <td class="px-3 py-3"><div class="flex justify-end gap-1">
                                <button type="button" onclick="showAdministratorDetail(@js($detalleUsuario))" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Ver información"><span class="material-symbols-rounded text-[20px]">visibility</span></button>
                                <button type="button" onclick="preparePasswordReset(@js(['name' => $usuario->name, 'email' => $usuario->email, 'url' => route('admin.usuarios.password.reset', $usuario)]))" class="rounded-lg p-2 text-indigo-600 hover:bg-indigo-50" title="Restablecer contraseña"><span class="material-symbols-rounded text-[20px]">lock_reset</span></button>
                                @if (!$usuario->is(auth()->user()))
                                    <button type="button" onclick="prepareStatusChange(@js(['name' => $usuario->name, 'email' => $usuario->email, 'role' => $usuario->role, 'estado' => $usuario->estado, 'url' => route('admin.usuarios.estado', $usuario)]))" class="rounded-lg p-2 {{ $usuario->estado === 'activo' ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}" title="{{ $usuario->estado === 'activo' ? 'Suspender' : 'Reactivar' }}"><span class="material-symbols-rounded text-[20px]">{{ $usuario->estado === 'activo' ? 'person_off' : 'person_check' }}</span></button>
                                @else
                                    <button disabled class="cursor-not-allowed rounded-lg p-2 text-slate-300" title="No puedes suspender tu propia cuenta"><span class="material-symbols-rounded text-[20px]">shield_lock</span></button>
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">No se encontraron usuarios.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
            <div class="mt-5">{{ $usuarios->links() }}</div>
        </div>

        <div id="users-tab-bitacora" class="users-panel hidden p-4 sm:p-6">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <input type="hidden" name="tab" value="bitacora">
                <div class="md:col-span-2"><label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Buscar en bitácora</label><div class="relative"><span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span><input name="log_search" value="{{ request('log_search') }}" type="search" placeholder="Usuario, acción o descripción..." class="w-full rounded-xl border-slate-200 pl-10 text-sm focus:border-primary focus:ring-primary"></div></div>
                <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Módulo</label><select name="log_modulo" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"><option value="todos">Todos</option>@foreach ($modulos as $modulo)<option value="{{ $modulo }}" @selected(request('log_modulo') === $modulo)>{{ $modulo }}</option>@endforeach</select></div>
                <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Importancia</label><select name="log_nivel" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"><option value="todos">Todas</option><option value="critica" @selected(request('log_nivel') === 'critica')>Críticas</option><option value="importante" @selected(request('log_nivel') === 'importante')>Importantes</option><option value="operativa" @selected(request('log_nivel') === 'operativa')>Operativas</option></select></div>
                <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Fecha</label><div class="flex gap-2"><input name="log_fecha" value="{{ request('log_fecha') }}" type="date" class="min-w-0 flex-1 rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"><button class="rounded-xl bg-primary px-3 text-white" title="Aplicar filtros"><span class="material-symbols-rounded">filter_alt</span></button></div></div>
            </form>

            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">La bitácora registra operaciones relevantes del sistema.</div>

            <div class="overflow-hidden rounded-2xl border border-slate-200"><div class="overflow-x-auto"><table class="min-w-[900px] w-full divide-y divide-slate-200"><thead class="bg-slate-50"><tr>
                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Fecha</th><th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Usuario</th><th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Módulo</th><th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Acción</th><th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Importancia</th><th class="px-4 py-3 text-right text-xs font-black uppercase tracking-wider text-slate-500">Detalle</th>
            </tr></thead><tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($bitacora as $registro)
                    @php $detalleLog = ['accion' => str($registro->accion)->replace('_', ' ')->title()->toString(), 'fecha' => $registro->created_at?->format('d/m/Y H:i:s'), 'usuario' => $registro->actor_name ?: 'Sistema', 'rol' => $rolLabel($registro->actor_role), 'modulo' => $registro->modulo, 'nivel' => $nivelLabel($registro->nivel), 'ip' => $registro->ip_address ?: 'Sin registro', 'descripcion' => $registro->descripcion]; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4 align-top"><p class="truncate text-sm font-semibold text-slate-700">{{ $registro->created_at?->format('d/m/Y') }}</p><p class="mt-0.5 truncate text-xs text-slate-400">{{ $registro->created_at?->format('H:i') }}</p></td>
                        <td class="px-4 py-4 align-top"><p class="truncate text-sm font-bold text-slate-900">{{ $registro->actor_name ?: 'Sistema' }}</p><p class="mt-0.5 truncate text-xs text-slate-500">{{ $rolLabel($registro->actor_role) }}</p></td>
                        <td class="px-4 py-4 align-top"><span class="inline-flex max-w-full rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $registro->modulo }}</span></td>
                        <td class="px-4 py-4 align-top"><p class="truncate text-sm font-semibold text-slate-800">{{ str($registro->accion)->replace('_', ' ')->title() }}</p></td>
                        <td class="px-4 py-4 align-top"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $nivelClass($registro->nivel) }}">{{ $nivelLabel($registro->nivel) }}</span></td>
                        <td class="px-4 py-4 text-right align-top"><button type="button" onclick="showAuditDetail(@js($detalleLog))" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Ver detalle"><span class="material-symbols-rounded text-[20px]">open_in_new</span></button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">No hay registros con esos filtros.</td></tr>
                @endforelse
            </tbody></table></div></div>
            <div class="mt-5">{{ $bitacora->links() }}</div>
        </div>
    </section>
</div>
@endsection

@section('modales')
<div id="modalNuevoUsuario" class="users-modal fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeUsersModal('modalNuevoUsuario')"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl">

            <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-xl font-black text-slate-900">Nuevo usuario</h3>
                    <p class="mt-1 text-sm text-slate-500">Registra una nueva cuenta para el sistema SRA.</p>
                </div>

                <button type="button" onclick="closeUsersModal('modalNuevoUsuario')" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form id="createUserForm" method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-5 p-6" data-prevent-double-submit>
                @csrf
                <input type="hidden" name="_form_context" value="modalNuevoUsuario">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-bold text-slate-700">Nombre completo</label>
                        <input name="name" value="{{ old('name') }}" required maxlength="150" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700">Correo</label>
                        <input name="email" value="{{ old('email') }}" type="email" required class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono') }}" maxlength="30" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label for="newUserRole" class="mb-1.5 block text-sm font-bold text-slate-700">Rol</label>
                        <select id="newUserRole" name="role" required onchange="toggleNewUserPasswordFields()" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                            <option value="solicitante" @selected(old('role', 'solicitante') === 'solicitante')>Solicitante</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrador</option>
                        </select>
                    </div>

                    <div id="temporaryPasswordContainer">
                        <label for="temporaryPassword" class="mb-1.5 block text-sm font-bold text-slate-700">Contraseña temporal</label>
                        <div class="flex gap-2">
                            <input id="temporaryPassword" name="temporary_password" type="text" readonly minlength="8" maxlength="8" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm font-bold tracking-[0.18em] text-slate-800">
                            <button type="button" onclick="generateTemporaryPassword()" class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 text-slate-500 hover:bg-slate-50" title="Generar otra contraseña">
                                <span class="material-symbols-rounded">refresh</span>
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">Solo para cuentas de solicitante.</p>
                    </div>

                    <div id="adminPasswordContainer" class="hidden">
                        <label for="adminInitialPassword" class="mb-1.5 block text-sm font-bold text-slate-700">Contraseña</label>
                        <input id="adminInitialPassword" name="password" type="password" minlength="8" autocomplete="new-password" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                        <p class="mt-1 text-[11px] text-slate-400">La contraseña del administrador se escribe manualmente.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" onclick="closeUsersModal('modalNuevoUsuario')" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700">Cancelar</button>
                    <button type="submit" data-submit-text="Guardando..." class="rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white">Guardar usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="modalDetalleAdministrador" class="users-modal fixed inset-0 z-[100] hidden overflow-y-auto"><div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeUsersModal('modalDetalleAdministrador')"></div><div class="flex min-h-full items-center justify-center p-4"><div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl"><div class="flex items-start justify-between border-b px-6 py-5"><div><h3 id="administratorDetailName" class="text-xl font-black"></h3><p id="administratorDetailEmail" class="mt-1 text-sm text-slate-500"></p></div><button type="button" onclick="closeUsersModal('modalDetalleAdministrador')" class="rounded-xl p-2"><span class="material-symbols-rounded">close</span></button></div><div class="grid gap-4 p-6 sm:grid-cols-2"><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Rol</p><p id="administratorDetailRole" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Estado</p><p id="administratorDetailStatus" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Teléfono</p><p id="administratorDetailPhone" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Último acceso</p><p id="administratorDetailLastLogin" class="mt-2 font-bold"></p><p id="administratorDetailIp" class="mt-1 text-xs text-slate-500"></p></div></div></div></div></div>

<div id="modalResetAdministrador" class="users-modal fixed inset-0 z-[100] hidden overflow-y-auto"><div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeUsersModal('modalResetAdministrador')"></div><div class="flex min-h-full items-center justify-center p-4"><div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl"><div class="flex items-start justify-between border-b border-slate-200 px-6 py-5"><div><h3 class="text-xl font-black text-slate-900">Restablecer contraseña</h3><p id="resetAdministratorText" class="mt-1 text-sm text-slate-500"></p></div><button type="button" onclick="closeUsersModal('modalResetAdministrador')" class="rounded-xl p-2"><span class="material-symbols-rounded">close</span></button></div><form id="resetAdministratorForm" method="POST" class="space-y-5 p-6" data-prevent-double-submit>@csrf @method('PATCH')<div><label for="resetPassword" class="mb-2 block text-sm font-bold text-slate-700">Nueva contraseña</label><input id="resetPassword" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"></div><div><label for="resetPasswordConfirmation" class="mb-2 block text-sm font-bold text-slate-700">Confirmar contraseña</label><input id="resetPasswordConfirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"></div><div class="flex justify-end gap-3 border-t border-slate-200 pt-5"><button type="button" onclick="closeUsersModal('modalResetAdministrador')" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600">Cancelar</button><button type="submit" data-submit-text="Guardando..." class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Guardar contraseña</button></div></form></div></div></div>

<div id="modalEstadoAdministrador" class="users-modal fixed inset-0 z-[100] hidden overflow-y-auto"><div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeUsersModal('modalEstadoAdministrador')"></div><div class="flex min-h-full items-center justify-center p-4"><div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl"><div class="border-b px-6 py-5"><h3 id="statusAdministratorTitle" class="text-xl font-black"></h3><p id="statusAdministratorText" class="mt-1 text-sm text-slate-500"></p></div><form id="statusAdministratorForm" method="POST" class="p-6" data-prevent-double-submit>@csrf @method('PATCH')<div id="statusAdministratorWarning" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"></div><label class="mt-4 flex gap-3 rounded-xl border p-4"><input id="statusAdministratorConfirmation" type="checkbox" required class="mt-1 rounded"><span><strong>Confirmo el cambio de estado</strong><span class="block text-sm text-slate-500">La acción quedará registrada en la bitácora.</span></span></label><div class="mt-5 flex justify-end gap-3"><button type="button" onclick="closeUsersModal('modalEstadoAdministrador')" class="rounded-xl border px-4 py-2.5 text-sm font-bold">Volver</button><button id="statusAdministratorButton" type="submit" data-submit-text="Procesando..." class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white">Confirmar</button></div></form></div></div></div>

<div id="modalDetalleBitacora" class="users-modal fixed inset-0 z-[100] hidden overflow-y-auto"><div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeUsersModal('modalDetalleBitacora')"></div><div class="flex min-h-full items-center justify-center p-4"><div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl"><div class="flex justify-between border-b px-6 py-5"><div><h3 id="auditDetailAction" class="text-xl font-black"></h3><p id="auditDetailDate" class="mt-1 text-sm text-slate-500"></p></div><button type="button" onclick="closeUsersModal('modalDetalleBitacora')"><span class="material-symbols-rounded">close</span></button></div><div class="space-y-4 p-6"><div class="grid gap-4 sm:grid-cols-4"><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Usuario</p><p id="auditDetailUser" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Módulo</p><p id="auditDetailModule" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Importancia</p><p id="auditDetailLevel" class="mt-2 font-bold"></p></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">IP</p><p id="auditDetailIp" class="mt-2 font-bold"></p></div></div><div class="rounded-xl border p-4"><p class="text-xs font-black uppercase text-slate-400">Descripción</p><p id="auditDetailDescription" class="mt-2 leading-7"></p></div></div></div></div></div>
@endsection

@section('scripts')
<script>
const initialUsersTab = @js($tabActual);

document.addEventListener('DOMContentLoaded', () => {
    setUsersTab(initialUsersTab, false);
    initializeUsersNotifications();
    initializeUsersSubmitProtection();

    if (@js(old('_form_context')) === 'modalNuevoUsuario') {
        openUsersModal('modalNuevoUsuario');
    } else {
        generateTemporaryPassword();
        toggleNewUserPasswordFields();
    }
});

function setUsersTab(tabName, updateUrl = true) {
    document.querySelectorAll('.users-tab').forEach(button => {
        const active = button.dataset.usersTab === tabName;
        button.className = active
            ? 'users-tab inline-flex items-center gap-2 whitespace-nowrap rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm'
            : 'users-tab inline-flex items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-900';
    });

    document.querySelectorAll('.users-panel').forEach(panel => panel.classList.add('hidden'));
    document.getElementById(`users-tab-${tabName}`)?.classList.remove('hidden');

    if (updateUrl) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        history.replaceState({}, '', url);
    }
}

function openUsersModal(id) {
    const modal = document.getElementById(id);
    if (!modal || !modal.classList.contains('hidden')) return;

    if (id === 'modalNuevoUsuario') {
        generateTemporaryPassword();
        toggleNewUserPasswordFields();
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function generateTemporaryPassword() {
    const role = document.getElementById('newUserRole')?.value;
    const input = document.getElementById('temporaryPassword');

    if (role !== 'solicitante' || !input) return;

    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const values = new Uint32Array(8);
    window.crypto.getRandomValues(values);
    input.value = Array.from(values, value => alphabet[value % alphabet.length]).join('');
}

function toggleNewUserPasswordFields() {
    const role = document.getElementById('newUserRole')?.value;
    const temporaryContainer = document.getElementById('temporaryPasswordContainer');
    const adminContainer = document.getElementById('adminPasswordContainer');
    const temporaryInput = document.getElementById('temporaryPassword');
    const adminInput = document.getElementById('adminInitialPassword');

    const solicitante = role === 'solicitante';

    temporaryContainer?.classList.toggle('hidden', !solicitante);
    adminContainer?.classList.toggle('hidden', solicitante);

    if (temporaryInput) {
        temporaryInput.required = solicitante;
        if (solicitante && !temporaryInput.value) generateTemporaryPassword();
        if (!solicitante) temporaryInput.value = '';
    }

    if (adminInput) {
        adminInput.required = !solicitante;
        if (solicitante) adminInput.value = '';
    }
}

function closeUsersModal(id) {
    document.getElementById(id)?.classList.add('hidden');
    if (!document.querySelector('.users-modal:not(.hidden)')) {
        document.body.classList.remove('overflow-hidden');
    }
}

function showAdministratorDetail(user) {
    document.getElementById('administratorDetailName').textContent = user.name;
    document.getElementById('administratorDetailEmail').textContent = user.email;
    document.getElementById('administratorDetailRole').textContent = user.role;
    document.getElementById('administratorDetailStatus').textContent = user.estado;
    document.getElementById('administratorDetailPhone').textContent = user.telefono || 'Sin teléfono';
    document.getElementById('administratorDetailLastLogin').textContent = user.ultimo_acceso;
    document.getElementById('administratorDetailIp').textContent = `IP: ${user.ultima_ip}`;
    openUsersModal('modalDetalleAdministrador');
}

function preparePasswordReset(user) {
    const form = document.getElementById('resetAdministratorForm');
    form.action = user.url;
    document.getElementById('resetAdministratorText').textContent = `${user.name} · ${user.email}`;
    document.getElementById('resetPassword').value = '';
    document.getElementById('resetPasswordConfirmation').value = '';
    openUsersModal('modalResetAdministrador');
    setTimeout(() => document.getElementById('resetPassword').focus(), 100);
}

function prepareStatusChange(user) {
    const active = user.estado === 'activo';
    document.getElementById('statusAdministratorTitle').textContent = active ? `Suspender ${user.role === 'admin' ? 'administrador' : 'solicitante'}` : `Reactivar ${user.role === 'admin' ? 'administrador' : 'solicitante'}`;
    document.getElementById('statusAdministratorText').textContent = `${user.name} · ${user.email}`;
    document.getElementById('statusAdministratorWarning').textContent = active ? 'La cuenta perderá el acceso inmediatamente. Sus acciones anteriores no se eliminarán.' : 'La cuenta recuperará el acceso al sistema.';
    const button = document.getElementById('statusAdministratorButton');
    button.textContent = active ? 'Sí, suspender' : 'Sí, reactivar';
    button.className = active ? 'rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white' : 'rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white';
    document.getElementById('statusAdministratorForm').action = user.url;
    document.getElementById('statusAdministratorConfirmation').checked = false;
    openUsersModal('modalEstadoAdministrador');
}

function showAuditDetail(log) {
    document.getElementById('auditDetailAction').textContent = log.accion;
    document.getElementById('auditDetailDate').textContent = log.fecha;
    document.getElementById('auditDetailUser').textContent = `${log.usuario} · ${log.rol}`;
    document.getElementById('auditDetailModule').textContent = log.modulo;
    document.getElementById('auditDetailLevel').textContent = log.nivel;
    document.getElementById('auditDetailIp').textContent = log.ip;
    document.getElementById('auditDetailDescription').textContent = log.descripcion;
    openUsersModal('modalDetalleBitacora');
}

function dismissUsersNotification(element) {
    const notification = element.closest('.users-notification');
    if (!notification) return;
    notification.classList.add('translate-y-3', 'opacity-0');
    setTimeout(() => notification.remove(), 300);
}

function initializeUsersNotifications() {
    document.querySelectorAll('.users-notification[data-auto-dismiss]').forEach(notification => {
        const delay = Number(notification.dataset.autoDismiss || 5000);
        setTimeout(() => {
            if (notification.isConnected) dismissUsersNotification(notification);
        }, delay);
    });
}

function initializeUsersSubmitProtection() {
    document.querySelectorAll('form[data-prevent-double-submit]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }
            if (!form.checkValidity()) return;
            form.dataset.submitting = 'true';
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.disabled = true;
                button.classList.add('cursor-not-allowed', 'opacity-60');
                button.innerHTML = '<span class="material-symbols-rounded animate-spin text-[18px]">sync</span> ' + (button.dataset.submitText || 'Procesando...');
            });
        });
    });
}

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        const modal = document.querySelector('.users-modal:not(.hidden)');
        if (modal) closeUsersModal(modal.id);
    }
});
</script>
@endsection
