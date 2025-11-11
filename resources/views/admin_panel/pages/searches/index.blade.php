@extends('admin_panel.layouts.master')

@section('title', 'Búsquedas de Twitter')

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin::searches.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus-circle me-1"></i> Nueva Búsqueda
                    </a>
                </div>
                <h4 class="mb-0">Búsquedas de Twitter</h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin::home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Búsquedas</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Cards de búsquedas -->
    <div class="row">
        @forelse($searches as $search)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 {{ $search->active ? 'border-success' : 'border-secondary' }} border">
                    <div class="card-body">
                        <!-- Header con ID y Estado -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark">#{{ $search->id }}</span>
                            <form action="{{ route('admin::searches.toggle', $search->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-xs btn-{{ $search->active ? 'success' : 'secondary' }}" title="{{ $search->active ? 'Activa' : 'Inactiva' }}">
                                    @if($search->active)
                                        <i class="fa-solid fa-circle-check"></i> Activa
                                    @else
                                        <i class="fa-solid fa-circle-xmark"></i> Inactiva
                                    @endif
                                </button>
                            </form>
                        </div>

                        <!-- Título -->
                        <h5 class="card-title mb-3">
                            {{ $search->name ?? 'Sin nombre' }}
                        </h5>

                        <!-- Query -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Query:</small>
                            <p class="mb-0 small text-break" title="{{ $search->query }}">
                                {{ \Illuminate\Support\Str::limit($search->query, 80) }}
                            </p>
                        </div>

                        <!-- Información en badges -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge bg-secondary">
                                    <i class="fa-solid fa-globe me-1"></i>{{ $search->country ?? 'N/A' }}
                                </span>
                                @if($search->lang)
                                    <span class="badge bg-info">
                                        <i class="fa-solid fa-language me-1"></i>{{ $search->lang }}
                                    </span>
                                @endif
                                <span class="badge {{ $search->query_type === 'Latest' ? 'bg-primary' : 'bg-warning' }}">
                                    {{ $search->query_type }}
                                </span>
                            </div>
                        </div>

                        <!-- Frecuencia -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1"><i class="fa-solid fa-clock me-1"></i> Frecuencia:</small>
                            <p class="mb-0">
                                <strong>{{ $search->run_every_minutes }}</strong> minutos
                            </p>
                        </div>

                        <!-- Última ejecución -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1"><i class="fa-solid fa-calendar-check me-1"></i> Última ejecución:</small>
                            <p class="mb-0">
                                @if($search->last_run_at)
                                    <span class="text-success">{{ $search->last_run_at->diffForHumans() }}</span>
                                    <br>
                                    <small class="text-muted">{{ $search->last_run_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-muted">Nunca ejecutada</span>
                                @endif
                            </p>
                        </div>

                        <!-- Gráfico de tweets (últimos 7 días) -->
                        <div class="mb-0">
                            <small class="text-muted d-block mb-2"><i class="fa-solid fa-chart-simple me-1"></i> Tweets encontrados (últimos 7 días):</small>
                            <div style="position: relative; height: 100px; width: 100%;">
                                <canvas id="chart-{{ $search->id }}"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Footer con acciones -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin::searches.show', $search->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="fa-solid fa-eye me-1"></i> Ver
                            </a>
                            <a href="{{ route('admin::searches.edit', $search->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                <i class="fa-solid fa-pen me-1"></i> Editar
                            </a>
                            <form action="{{ route('admin::searches.destroy', $search->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta búsqueda?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">No hay búsquedas configuradas</p>
                        <a href="{{ route('admin::searches.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus-circle me-1"></i> Crear primera búsqueda
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($searches->hasPages())
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $searches->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Información de ayuda -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-primary border">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fa-solid fa-circle-info"></i> Información sobre búsquedas
                    </h5>
                    <p class="mb-2"><strong>Frecuencia:</strong> Define cada cuántos minutos se ejecutará la búsqueda automáticamente.</p>
                    <p class="mb-2"><strong>Query Type:</strong> <code>Latest</code> trae tweets recientes, <code>Top</code> trae los más relevantes.</p>
                    <p class="mb-2"><strong>Filtros:</strong> Los filtros de likes y retweets mínimos se aplican después de obtener los resultados.</p>
                    <p class="mb-0"><strong>Estado:</strong> Solo las búsquedas activas se ejecutarán automáticamente.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-specific-scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos de los gráficos
            const chartsData = [
                @foreach($searches as $search)
                {
                    id: {{ $search->id }},
                    data: @json($search->chart_data ?? [])
                },
                @endforeach
            ];

            // Crear gráfico para cada búsqueda
            chartsData.forEach(chartInfo => {
                const canvas = document.getElementById(`chart-${chartInfo.id}`);
                if (!canvas) return;

                const labels = chartInfo.data.map(d => d.label);
                const counts = chartInfo.data.map(d => d.count);
                const maxCount = Math.max(...counts, 1);

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Tweets',
                            data: counts,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Tweets: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: Math.ceil(maxCount / 3),
                                    precision: 0,
                                    font: {
                                        size: 10
                                    }
                                },
                                grid: {
                                    display: true,
                                    drawBorder: false
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
@endsection
