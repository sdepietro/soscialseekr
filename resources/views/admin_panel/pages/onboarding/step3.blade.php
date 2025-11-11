<!DOCTYPE html>
<html lang="es">
<head>
    <title>Paso 3: Primera Búsqueda - X Finder</title>
    @include('admin_panel.layouts.partials.head-css')
    <style>
        .template-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .template-card:hover {
            border-color: #333;
            transform: translateY(-2px);
        }
        .template-card.selected {
            border-color: #333;
            background-color: #f8f9fa;
        }
        .template-card input[type="radio"] {
            display: none;
        }
    </style>
</head>

<body class="authentication-bg">

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11 col-lg-10">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <!-- Barra de progreso -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Paso 3 de 3</span>
                                <span class="text-muted small">100% completado</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark mb-2">Crea tu primera búsqueda</h3>
                            <p class="text-muted">Elige una plantilla predefinida o crea una búsqueda personalizada</p>
                        </div>

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

                        <form action="{{ url('admin/onboarding/step-3') }}" method="post" id="step3Form">
                            {{ csrf_field() }}
                            <input type="hidden" name="use_template" id="use_template" value="1">
                            <input type="hidden" name="template_index" id="template_index" value="">

                            <!-- Templates predefinidos -->
                            <div id="templatesSection">
                                <h5 class="mb-3">Plantillas sugeridas para tu industria</h5>
                                <div class="row">
                                    @foreach($templates as $index => $template)
                                        <div class="col-md-6 mb-3">
                                            <div class="template-card card h-100 p-3" onclick="selectTemplate({{ $index }})">
                                                <input type="radio" name="selected_template" value="{{ $index }}" id="template_{{ $index }}">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-2 fw-bold">{{ $template['name'] }}</h6>
                                                        <p class="mb-2 small text-muted">{{ $template['description'] }}</p>
                                                        <div class="small">
                                                            <span class="badge bg-secondary">{{ $template['country'] }}</span>
                                                            <span class="badge bg-secondary">{{ $template['lang'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="text-center my-4">
                                    <button type="button" class="btn btn-outline-dark" onclick="showCustomForm()">
                                        O crea una búsqueda personalizada
                                    </button>
                                </div>
                            </div>

                            <!-- Formulario personalizado (oculto por defecto) -->
                            <div id="customSection" style="display: none;">
                                <h5 class="mb-3">Búsqueda personalizada</h5>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="search_name" class="form-label">Nombre de la búsqueda <span class="text-danger">*</span></label>
                                            <input type="text" name="search_name" class="form-control" id="search_name"
                                                   placeholder="Ej: Quejas de clientes" value="{{ old('search_name') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="keywords" class="form-label">Palabras clave <span class="text-danger">*</span></label>
                                            <input type="text" name="keywords" class="form-control" id="keywords"
                                                   placeholder="Ej: queja OR reclamo OR problema" value="{{ old('keywords') }}">
                                            <small class="text-muted">
                                                Usa OR para buscar cualquiera de las palabras. Ej: "queja OR reclamo"
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country" class="form-label">País <span class="text-danger">*</span></label>
                                            <select name="country" class="form-control" id="country">
                                                <option value="{{ $company->country ?? 'AR' }}">{{ $company->country ?? 'AR' }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lang" class="form-label">Idioma</label>
                                            <select name="lang" class="form-control" id="lang">
                                                <option value="es">Español</option>
                                                <option value="en">Inglés</option>
                                                <option value="pt">Portugués</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center my-3">
                                    <button type="button" class="btn btn-outline-secondary" onclick="showTemplates()">
                                        ← Volver a las plantillas
                                    </button>
                                </div>
                            </div>

                            <!-- Información del plan -->
                            <div class="alert alert-light border mt-4">
                                <small class="text-muted">
                                    <strong>Tu plan Free Trial incluye:</strong><br>
                                    • Hasta {{ $company->max_searches }} búsquedas simultáneas<br>
                                    • Actualización cada {{ $company->max_frequency_minutes }} minutos<br>
                                    • Válido hasta {{ $company->trial_ends_at ? $company->trial_ends_at->format('d/m/Y') : '14 días' }}
                                </small>
                            </div>

                            <div class="d-grid mt-4">
                                <button class="btn btn-success btn-lg fw-medium" type="submit">
                                    Finalizar y comenzar →
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="{{ url('admin/onboarding/skip/3') }}" class="text-muted small">
                                    Saltar este paso, configurar después
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectTemplate(index) {
    // Remover selección previa
    document.querySelectorAll('.template-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Seleccionar el template actual
    document.getElementById('template_' + index).checked = true;
    document.getElementById('template_index').value = index;
    document.getElementById('use_template').value = '1';
    event.currentTarget.classList.add('selected');
}

function showCustomForm() {
    document.getElementById('templatesSection').style.display = 'none';
    document.getElementById('customSection').style.display = 'block';
    document.getElementById('use_template').value = '0';
    document.getElementById('template_index').value = '';
}

function showTemplates() {
    document.getElementById('templatesSection').style.display = 'block';
    document.getElementById('customSection').style.display = 'none';
    document.getElementById('use_template').value = '1';
}
</script>

</body>
</html>
