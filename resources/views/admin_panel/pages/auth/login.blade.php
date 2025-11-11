<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ingresar</title>
    @include('admin_panel.layouts.partials.head-css')
</head>

<body class="authentication-bg">

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <h3 class="fw-bold text-dark mb-2">¡Bienvenido!</h3>
                            <p class="text-muted">Ingresa tus datos para iniciar sesión</p>
                        </div>
                        <form action="{{ url('admin/login') }}" class="mt-4" method="post">
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

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="Ingresa tu correo electrónico">
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label">Password</label>
                                </div>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Ingresar contraseña">
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Ingresar</button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted mb-0">
                                    ¿No tienes una cuenta?
                                    <a href="{{ url('admin/register') }}" class="text-dark fw-medium">Regístrate aquí</a>
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
