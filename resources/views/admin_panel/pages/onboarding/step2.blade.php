<!DOCTYPE html>
<html lang="es">
<head>
    <title>Paso 2: Tu Empresa - X Finder</title>
    @include('admin_panel.layouts.partials.head-css')
</head>

<body class="authentication-bg">

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <!-- Barra de progreso -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Paso 2 de 3</span>
                                <span class="text-muted small">67% completado</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 67%"></div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark mb-2">Cuéntanos sobre tu empresa</h3>
                            <p class="text-muted">Esta información nos ayudará a personalizar tu experiencia</p>
                        </div>

                        <form action="{{ url('admin/onboarding/step-2') }}" method="post">
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
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Nombre de la empresa <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" class="form-control" id="company_name"
                                               placeholder="Mi Empresa SRL" value="{{ old('company_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="country" class="form-label">País <span class="text-danger">*</span></label>
                                        <select name="country" class="form-control" id="country" required>
                                            <option value="AR" {{ old('country', 'AR') == 'AR' ? 'selected' : '' }}>Argentina</option>
                                            <option value="MX" {{ old('country') == 'MX' ? 'selected' : '' }}>México</option>
                                            <option value="ES" {{ old('country') == 'ES' ? 'selected' : '' }}>España</option>
                                            <option value="CO" {{ old('country') == 'CO' ? 'selected' : '' }}>Colombia</option>
                                            <option value="CL" {{ old('country') == 'CL' ? 'selected' : '' }}>Chile</option>
                                            <option value="PE" {{ old('country') == 'PE' ? 'selected' : '' }}>Perú</option>
                                            <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>Estados Unidos</option>
                                            <option value="OTHER" {{ old('country') == 'OTHER' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="industry" class="form-label">Sector / Industria <span class="text-danger">*</span></label>
                                        <select name="industry" class="form-control" id="industry" required>
                                            <option value="">Selecciona un sector</option>
                                            @foreach($industries as $key => $label)
                                                <option value="{{ $key }}" {{ old('industry') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_size" class="form-label">Tamaño <span class="text-danger">*</span></label>
                                        <select name="company_size" class="form-control" id="company_size" required>
                                            <option value="">Selecciona un tamaño</option>
                                            @foreach($companySizes as $key => $label)
                                                <option value="{{ $key }}" {{ old('company_size') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Sitio web</label>
                                        <input type="url" name="website" class="form-control" id="website"
                                               placeholder="https://miempresa.com" value="{{ old('website') }}">
                                        <small class="text-muted">Opcional</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Teléfono</label>
                                        <input type="text" name="phone" class="form-control" id="phone"
                                               placeholder="+54 11 1234-5678" value="{{ old('phone') }}">
                                        <small class="text-muted">Opcional</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Plan incluido -->
                            <div class="alert alert-info mt-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><strong>Plan Free Trial incluido</strong></h6>
                                        <p class="mb-0 small">
                                            • 14 días de prueba gratis<br>
                                            • Hasta 3 búsquedas simultáneas<br>
                                            • Actualización cada 60 minutos
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Continuar →</button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Al continuar, aceptas nuestros términos y condiciones
                                </small>
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
