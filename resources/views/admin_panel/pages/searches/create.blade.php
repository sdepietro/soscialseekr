@extends('admin_panel.layouts.master')

@section('title', $search ? 'Editar Búsqueda' : 'Nueva Búsqueda')

@push('styles')
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
    }
    .term-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .term-item:hover {
        background: #e9ecef;
    }
    .query-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 1rem;
        border-radius: 0.375rem;
        font-family: monospace;
        font-size: 0.875rem;
        min-height: 100px;
        white-space: pre-wrap;
    }
    .slider-value {
        font-weight: bold;
        color: #0d6efd;
    }

    #map {
        height: 400px;
        min-height: 400px;
        width: 100%;
        border-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin::searches.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
                <h4 class="mb-0">{{ $search ? 'Editar Búsqueda' : 'Nueva Búsqueda' }} - Constructor Visual</h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin::home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin::searches.index') }}">Búsquedas</a></li>
                    <li class="breadcrumb-item active">{{ $search ? 'Editar' : 'Nueva' }}</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <form action="{{ $search ? route('admin::searches.update', $search->id) : route('admin::searches.store') }}" method="POST" id="searchForm">
        @csrf
        @if($search)
            @method('PUT')
        @endif

        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">

                <!-- Información Básica -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-circle-info text-primary"></i> Información Básica
                        </h5>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="mb-2">Se encontraron los siguientes errores:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">Nombre de la Búsqueda</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $search->name ?? '') }}" placeholder="Ej: Búsqueda Médica CABA">
                                <small class="text-muted">Nombre descriptivo para identificar esta búsqueda</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">Estado</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $search->active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">Activa</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">País</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="AR" {{ old('country', $search->country ?? 'AR') === 'AR' ? 'selected' : '' }}>🇦🇷 Argentina</option>
                                    <option value="ES" {{ old('country', $search->country ?? '') === 'ES' ? 'selected' : '' }}>🇪🇸 España</option>
                                    <option value="MX" {{ old('country', $search->country ?? '') === 'MX' ? 'selected' : '' }}>🇲🇽 México</option>
                                    <option value="US" {{ old('country', $search->country ?? '') === 'US' ? 'selected' : '' }}>🇺🇸 Estados Unidos</option>
                                    <option value="CL" {{ old('country', $search->country ?? '') === 'CL' ? 'selected' : '' }}>🇨🇱 Chile</option>
                                    <option value="CO" {{ old('country', $search->country ?? '') === 'CO' ? 'selected' : '' }}>🇨🇴 Colombia</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="lang" class="form-label">Idioma</label>
                                <select class="form-select" id="lang" name="lang">
                                    <option value="es" {{ old('lang', $search->lang ?? 'es') === 'es' ? 'selected' : '' }}>Español</option>
                                    <option value="en" {{ old('lang', $search->lang ?? '') === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="pt" {{ old('lang', $search->lang ?? '') === 'pt' ? 'selected' : '' }}>Português</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="query_type" class="form-label">Tipo de Búsqueda</label>
                                <select class="form-select" id="query_type" name="query_type" required>
                                    <option value="Latest" {{ old('query_type', $search->query_type ?? 'Latest') === 'Latest' ? 'selected' : '' }}>Recientes</option>
                                    <option value="Top" {{ old('query_type', $search->query_type ?? '') === 'Top' ? 'selected' : '' }}>Más Relevantes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Análisis IA -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-robot text-primary"></i> Configuración de Análisis IA
                        </h5>

                        <div class="mb-3">
                            <label for="ia_prompt" class="form-label">
                                Prompt Personalizado para Análisis IA <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('ia_prompt') is-invalid @enderror"
                                      id="ia_prompt"
                                      name="ia_prompt"
                                      rows="10"
                                      required
                                      placeholder="Describe cómo debe analizar ChatGPT los tweets de esta búsqueda...&#10;&#10;Ejemplo:&#10;Eres un analista de redes sociales especializado en el sector tecnológico. Evalúa cada tweet según:&#10;1. Relevancia para startups (0-40 puntos)&#10;2. Potencial de conversión (0-30 puntos)&#10;3. Engagement esperado (0-20 puntos)&#10;4. Calidad del contenido (0-10 puntos)&#10;&#10;Devuelve un array JSON con: [{&quot;id&quot;:&quot;<tweet_id>&quot;,&quot;score&quot;:85,&quot;reason&quot;:&quot;...&quot;}]">{{ old('ia_prompt', $search->ia_prompt ?? '') }}</textarea>
                            @error('ia_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fa-solid fa-circle-info"></i>
                                Define criterios específicos para que ChatGPT evalúe la relevancia de los tweets encontrados por esta búsqueda.
                                El prompt debe indicar cómo puntuar de 0 a 100 cada tweet.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Constructor de Términos -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-magnifying-glass text-primary"></i> Términos de Búsqueda
                        </h5>

                        <div id="termsContainer">
                            <!-- Los términos se agregarán aquí dinámicamente -->
                        </div>

                        <div class="row">
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="newTermInput" placeholder='Escribir palabra o "frase exacta"'>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="newTermOperator">
                                    <option value="OR">O (OR)</option>
                                    <option value="AND">Y (AND)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" id="addTermBtn">
                                    <i class="fa-solid fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <i class="fa-solid fa-lightbulb"></i>
                            Usa comillas para frases exactas: <code>"obra social"</code>.
                            Usa - para excluir: <code>-spam</code>
                        </small>
                    </div>
                </div>

                <!-- Ubicación Geográfica -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-location-dot text-primary"></i> Ubicación Geográfica
                        </h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="useGeolocation" checked>
                            <label class="form-check-label" for="useGeolocation">
                                <strong>Filtrar por ubicación</strong>
                            </label>
                        </div>

                        <div id="geolocationSection">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Buscar ubicación:</label>
                                    <input type="text" class="form-control" id="searchLocation" placeholder="Buscar ciudad o dirección...">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div id="map" style="min-height: 400px; border-radius:0.5rem" ></div>
                                    <small class="text-muted mt-2 d-block">Haz click en el mapa para seleccionar una ubicación</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Radio de búsqueda: <span class="slider-value" id="radiusValue">10 km</span></label>
                                    <input type="range" class="form-range" id="radiusSlider" min="1" max="100" value="10" step="1">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">1 km</small>
                                        <small class="text-muted">100 km</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="geoLatitude" class="form-label">Coordenadas</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Lat:</span>
                                        <input type="text" class="form-control" id="geoLatitude" readonly>
                                        <span class="input-group-text">Lng:</span>
                                        <input type="text" class="form-control" id="geoLongitude" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-filter text-primary"></i> Filtros Avanzados
                        </h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="min_replies" class="form-label">Mínimo de Respuestas</label>
                                <input type="number" class="form-control" id="min_replies" min="0" value="0">
                                <small class="text-muted">Tweets con al menos N respuestas</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="min_like_count" class="form-label">Mínimo de Likes</label>
                                <input type="number" class="form-control" id="min_like_count" name="min_like_count" min="0" value="{{ old('min_like_count', $search->min_like_count ?? 0) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="min_retweet_count" class="form-label">Mínimo de Retweets</label>
                                <input type="number" class="form-control" id="min_retweet_count" name="min_retweet_count" min="0" value="{{ old('min_retweet_count', $search->min_retweet_count ?? 0) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="since_date" class="form-label">Desde fecha (opcional)</label>
                                <input type="date" class="form-control" id="since_date">
                                <small class="text-muted">Solo tweets desde esta fecha</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Excluir</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="filter_replies" checked>
                                    <label class="form-check-label" for="filter_replies">
                                        Excluir respuestas (replies)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="filter_retweets">
                                    <label class="form-check-label" for="filter_retweets">
                                        Excluir retweets
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="only_from_accounts" class="form-label">Limitar a cuentas específicas (opcional)</label>
                            <input type="text" class="form-control" id="only_from_accounts" name="only_from_accounts" value="{{ old('only_from_accounts', $search && is_array($search->only_from_accounts) ? implode(', ', $search->only_from_accounts) : ($search->only_from_accounts ?? '')) }}" placeholder="@usuario1, @usuario2">
                            <small class="text-muted">Usernames separados por comas</small>
                        </div>
                    </div>
                </div>

                <!-- Query Preview -->
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-3">
                            <i class="fa-solid fa-code"></i> Query Generada (Preview)
                        </h5>
                        <div class="query-preview" id="queryPreview">
                            <em class="text-muted">La query se generará automáticamente...</em>
                        </div>
                        <!-- Hidden input con la query real -->
                        <input type="hidden" name="query" id="queryInput" required>
                    </div>
                </div>

            </div>

            <!-- Columna Lateral -->
            <div class="col-lg-4">
                <!-- Configuración de Ejecución -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa-solid fa-clock text-primary"></i> Configuración
                        </h5>

                        <div class="mb-3">
                            <label for="run_every_minutes" class="form-label">Ejecutar cada:</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="run_every_minutes" name="run_every_minutes" value="{{ old('run_every_minutes', $search->run_every_minutes ?? 15) }}" min="1" required>
                                <span class="input-group-text">minutos</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Zona Horaria</label>
                            <input type="text" class="form-control" id="timezone" name="timezone" value="{{ old('timezone', $search->timezone ?? 'America/Argentina/Buenos_Aires') }}">
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-save me-1"></i> {{ $search ? 'Actualizar Búsqueda' : 'Guardar Búsqueda' }}
                            </button>
                            <a href="{{ route('admin::searches.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ayuda -->
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">
                            <i class="fa-solid fa-circle-info"></i> Ayuda
                        </h5>
                        <p class="small mb-2"><strong>Términos de búsqueda:</strong></p>
                        <ul class="small">
                            <li>Usa <strong>OR</strong> para cualquiera de los términos</li>
                            <li>Usa <strong>AND</strong> para todos los términos</li>
                            <li>Usa <code>"comillas"</code> para frases exactas</li>
                            <li>Usa <code>-palabra</code> para excluir</li>
                        </ul>

                        <p class="small mb-2 mt-3"><strong>Ubicación:</strong></p>
                        <ul class="small">
                            <li>Click en el mapa para seleccionar</li>
                            <li>Arrastra el marcador para ajustar</li>
                            <li>El círculo muestra el área de búsqueda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        let map, marker, circle;
        let terms = [];
        const defaultLocation = { lat: -34.603722, lng: -58.381592 };

        @if($search)
            // Datos de la búsqueda existente
            const existingQuery = @json($search->query);
        @endif

        // Hacer global para que Google pueda invocarla
        window.initMap = function () {
            const mapEl = document.getElementById('map');
            if (!mapEl) return;

            map = new google.maps.Map(mapEl, {
                center: defaultLocation,
                zoom: 12,
                mapTypeControl: false,
            });

            // Marker (Marker clásico; si luego querés migrar a AdvancedMarkerElement te paso snippet)
            marker = new google.maps.Marker({
                position: defaultLocation,
                map,
                draggable: true,
                title: "Ubicación de búsqueda",
            });

            circle = new google.maps.Circle({
                map,
                radius: 10000,
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                strokeColor: '#0d6efd',
                strokeOpacity: 0.5,
                strokeWeight: 2,
            });
            circle.bindTo('center', marker, 'position');

            // --- Búsqueda por lugar (fallback a Autocomplete si SearchBox no está disponible) ---
            const input = document.getElementById('searchLocation');
            if (input) {
                if (google.maps.places && google.maps.places.SearchBox) {
                    const sb = new google.maps.places.SearchBox(input);
                    sb.addListener('places_changed', () => {
                        const places = sb.getPlaces();
                        if (!places || !places.length) return;
                        const place = places[0];
                        if (!place.geometry || !place.geometry.location) return;
                        marker.setPosition(place.geometry.location);
                        map.setCenter(place.geometry.location);
                        map.setZoom(13);
                        updateCoordinates();
                    });
                } else {
                    // Fallback recomendado por deprecación: Autocomplete
                    const ac = new google.maps.places.Autocomplete(input, { fields: ['geometry', 'name'] });
                    ac.addListener('place_changed', () => {
                        const place = ac.getPlace();
                        if (!place || !place.geometry || !place.geometry.location) return;
                        marker.setPosition(place.geometry.location);
                        map.setCenter(place.geometry.location);
                        map.setZoom(13);
                        updateCoordinates();
                    });
                }
            }

            map.addListener('click', (e) => {
                marker.setPosition(e.latLng);
                updateCoordinates();
            });

            marker.addListener('dragend', updateCoordinates);

            // Inicial
            updateCoordinates();
            setupEventListeners();

            @if($search)
                // Si estamos editando, mostrar la query existente
                loadExistingQuery();
            @else
                buildQuery();
            @endif
        };

        function updateCoordinates() {
            if (!marker) return;
            const pos = marker.getPosition();
            if (!pos) return;
            const latEl = document.getElementById('geoLatitude');
            const lngEl = document.getElementById('geoLongitude');
            if (latEl) latEl.value = pos.lat().toFixed(6);
            if (lngEl) lngEl.value = pos.lng().toFixed(6);
            buildQuery();
        }

        function setupEventListeners() {
            const useGeo = document.getElementById('useGeolocation');
            if (useGeo) {
                useGeo.addEventListener('change', function () {
                    const sec = document.getElementById('geolocationSection');
                    if (sec) sec.style.display = this.checked ? 'block' : 'none';
                    buildQuery();
                });
            }

            const radius = document.getElementById('radiusSlider');
            if (radius) {
                radius.addEventListener('input', function () {
                    const r = parseInt(this.value) || 10;
                    const lbl = document.getElementById('radiusValue');
                    if (lbl) lbl.textContent = r + ' km';
                    if (circle) circle.setRadius(r * 1000);
                    buildQuery();
                });
            }

            const addBtn = document.getElementById('addTermBtn');
            if (addBtn) addBtn.addEventListener('click', addTerm);

            const termInput = document.getElementById('newTermInput');
            if (termInput) {
                termInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); addTerm(); }
                });
            }

            ['min_replies','min_like_count','min_retweet_count','since_date','filter_replies','filter_retweets','only_from_accounts']
                .forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.addEventListener('change', buildQuery);
                        el.addEventListener('input', buildQuery);
                    }
                });
        }

        function addTerm() {
            const input = document.getElementById('newTermInput');
            const operator = document.getElementById('newTermOperator');
            if (!input || !operator) return;
            const term = input.value.trim();
            if (!term) return;

            terms.push({ text: term, operator: operator.value });
            input.value = '';
            renderTerms();
            buildQuery();
        }

        function removeTerm(index) {
            terms.splice(index, 1);
            renderTerms();
            buildQuery();
        }
        window.removeTerm = removeTerm;

        function renderTerms() {
            const container = document.getElementById('termsContainer');
            if (!container) return;
            if (!terms.length) {
                container.innerHTML = '<p class="text-muted"><em>No hay términos agregados. Agrega palabras clave para buscar.</em></p>';
                return;
            }
            container.innerHTML = terms.map((term, i) => `
            <div class="term-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary me-2">${term.operator}</span>
                        <code>${term.text}</code>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeTerm(${i})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
        }

        function buildQuery() {
            const parts = [];

            if (terms.length) {
                const glue = ` ${terms[0].operator} `;
                parts.push(`(${terms.map(t => t.text).join(glue)})`);
            }

            const minReplies = parseInt(document.getElementById('min_replies')?.value) || 0;
            if (minReplies > 0) parts.push(`min_replies:${minReplies}`);

            if (document.getElementById('useGeolocation')?.checked) {
                const lat = document.getElementById('geoLatitude')?.value;
                const lng = document.getElementById('geoLongitude')?.value;
                const radius = document.getElementById('radiusSlider')?.value || 10;
                if (lat && lng) parts.push(`geocode:${lat},${lng},${radius}km`);
            }

            const sinceDate = document.getElementById('since_date')?.value;
            if (sinceDate) parts.push(`since:${sinceDate}`);

            if (document.getElementById('filter_replies')?.checked) parts.push('-filter:replies');
            if (document.getElementById('filter_retweets')?.checked) parts.push('-filter:retweets');

            const finalQuery = parts.join(' ');
            const preview = document.getElementById('queryPreview');
            const input = document.getElementById('queryInput');
            if (finalQuery) {
                if (preview) preview.textContent = finalQuery;
                if (input) input.value = finalQuery;
            } else {
                if (preview) preview.innerHTML = '<em class="text-muted">Agrega términos de búsqueda para generar la query...</em>';
                if (input) input.value = '';
            }
        }

        @if($search)
        function loadExistingQuery() {
            if (!existingQuery) return;

            // Parsear la query existente
            parseExistingQuery(existingQuery);

            // Cargar la query en el preview
            const preview = document.getElementById('queryPreview');
            const input = document.getElementById('queryInput');
            if (preview) {
                preview.textContent = existingQuery;
            }
            if (input) {
                input.value = existingQuery;
            }
        }

        function parseExistingQuery(query) {
            // 1. Extraer términos de búsqueda (todo lo que está entre paréntesis al inicio)
            const termsMatch = query.match(/^\(([^)]+)\)/);
            if (termsMatch) {
                const termsString = termsMatch[1];
                // Detectar el operador predominante (OR o AND)
                const hasOr = termsString.includes(' OR ');
                const hasAnd = termsString.includes(' AND ');
                const operator = hasOr ? 'OR' : (hasAnd ? 'AND' : 'OR');

                // Dividir por el operador
                const termsList = termsString.split(new RegExp(` ${operator} `));
                terms = termsList.map(t => ({
                    text: t.trim(),
                    operator: operator
                }));
                renderTerms();
            }

            // 2. Extraer min_replies
            const minRepliesMatch = query.match(/min_replies:(\d+)/);
            if (minRepliesMatch) {
                const minRepliesEl = document.getElementById('min_replies');
                if (minRepliesEl) minRepliesEl.value = minRepliesMatch[1];
            }

            // 3. Extraer geocode
            const geocodeMatch = query.match(/geocode:([-\d.]+),([-\d.]+),(\d+)km/);
            if (geocodeMatch) {
                const lat = parseFloat(geocodeMatch[1]);
                const lng = parseFloat(geocodeMatch[2]);
                const radius = parseInt(geocodeMatch[3]);

                // Actualizar mapa
                if (marker && map) {
                    const newPos = { lat, lng };
                    marker.setPosition(newPos);
                    map.setCenter(newPos);
                }

                // Actualizar campos
                const latEl = document.getElementById('geoLatitude');
                const lngEl = document.getElementById('geoLongitude');
                if (latEl) latEl.value = lat.toFixed(6);
                if (lngEl) lngEl.value = lng.toFixed(6);

                // Actualizar radio
                const radiusSlider = document.getElementById('radiusSlider');
                const radiusValue = document.getElementById('radiusValue');
                if (radiusSlider) radiusSlider.value = radius;
                if (radiusValue) radiusValue.textContent = radius + ' km';
                if (circle) circle.setRadius(radius * 1000);

                // Activar geolocalización
                const useGeo = document.getElementById('useGeolocation');
                if (useGeo) useGeo.checked = true;
            } else {
                // Si no hay geocode, desactivar geolocalización
                const useGeo = document.getElementById('useGeolocation');
                if (useGeo) useGeo.checked = false;
                const geoSection = document.getElementById('geolocationSection');
                if (geoSection) geoSection.style.display = 'none';
            }

            // 4. Extraer since date
            const sinceDateMatch = query.match(/since:(\d{4}-\d{2}-\d{2})/);
            if (sinceDateMatch) {
                const sinceDateEl = document.getElementById('since_date');
                if (sinceDateEl) sinceDateEl.value = sinceDateMatch[1];
            }

            // 5. Extraer filtros
            const hasFilterReplies = query.includes('-filter:replies');
            const filterRepliesEl = document.getElementById('filter_replies');
            if (filterRepliesEl) filterRepliesEl.checked = hasFilterReplies;

            const hasFilterRetweets = query.includes('-filter:retweets');
            const filterRetweetsEl = document.getElementById('filter_retweets');
            if (filterRetweetsEl) filterRetweetsEl.checked = hasFilterRetweets;
        }
        @endif
    </script>

    {{-- Importá Maps DESPUÉS de definir initMap --}}
    <script
        async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $google_api_key }}&libraries=places&callback=initMap">
    </script>
@endpush

