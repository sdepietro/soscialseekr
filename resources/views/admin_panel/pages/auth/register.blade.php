<!DOCTYPE html>
<html lang="es">
<head>
    <title>Crear cuenta - X Finder</title>
    @include('admin_panel.layouts.partials.head-css')
</head>

<body class="authentication-bg">

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <h3 class="fw-bold text-dark mb-2">Crear cuenta</h3>
                            <p class="text-muted">Completa tus datos para comenzar</p>
                        </div>
                        <form action="{{ url('admin/register') }}" class="mt-4" method="post">
                            {{ csrf_field() }}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <h5 class="mb-2">Se encontraron los siguientes errores:</h5>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre</label>
                                        <input type="text" name="name" class="form-control" id="name"
                                               placeholder="Tu nombre" value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lastname" class="form-label">Apellido</label>
                                        <input type="text" name="lastname" class="form-control" id="lastname"
                                               placeholder="Tu apellido" value="{{ old('lastname') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" id="email"
                                       placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" id="password"
                                       placeholder="Mínimo 8 caracteres" required>
                                <small class="text-muted">Debe tener al menos 8 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Repetir contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                       id="password_confirmation" placeholder="Repite tu contraseña" required>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Crear cuenta</button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted mb-0">
                                    ¿Ya tienes una cuenta?
                                    <a href="{{ url('admin/login') }}" class="text-dark fw-medium">Inicia sesión</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
