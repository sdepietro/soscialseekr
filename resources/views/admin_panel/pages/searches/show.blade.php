@extends('admin_panel.layouts.master')

@section('title', 'Detalles de Búsqueda')

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin::searches.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al Listado
                    </a>
                </div>
                <h4 class="mb-0">Detalles de Búsqueda</h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin::home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin::searches.index') }}">Búsquedas</a></li>
                    <li class="breadcrumb-item active">{{ $search->name ?? 'Detalles' }}</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Información de la Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card {{ $search->active ? 'border-success' : 'border-secondary' }} border">
                <div class="card-body">
                    <div class="row">
                        <!-- Columna Izquierda: Info -->
                        <div class="col-lg-6">
                            <!-- Header con ID y Estado -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-2">{{ $search->name ?? 'Sin nombre' }}</h3>
                                    <span class="badge bg-light text-dark">#{{ $search->id }}</span>
                                </div>
                                <form action="{{ route('admin::searches.toggle', $search->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-{{ $search->active ? 'success' : 'secondary' }}" title="{{ $search->active ? 'Activa' : 'Inactiva' }}">
                                        @if($search->active)
                                            <i class="fa-solid fa-circle-check"></i> Activa
                                        @else
                                            <i class="fa-solid fa-circle-xmark"></i> Inactiva
                                        @endif
                                    </button>
                                </form>
                            </div>

                            <!-- Query -->
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Query:</small>
                                <div class="alert alert-light mb-0">
                                    <code>{{ $search->query }}</code>
                                </div>
                            </div>

                            <!-- Información en badges -->
                            <div class="mb-3">
                                <div class="d-flex flex-wrap gap-2 mb-2">
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

                            <!-- Frecuencia y Última ejecución -->
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1"><i class="fa-solid fa-clock me-1"></i> Frecuencia:</small>
                                    <p class="mb-0">
                                        <strong>{{ $search->run_every_minutes }}</strong> minutos
                                    </p>
                                </div>
                                <div class="col-md-6">
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
                            </div>
                        </div>

                        <!-- Columna Derecha: Gráfico -->
                        <div class="col-lg-6">
                            <small class="text-muted d-block mb-2"><i class="fa-solid fa-chart-simple me-1"></i> Tweets encontrados (últimos 7 días):</small>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="chart-{{ $search->id }}"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Tweets -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa-brands fa-twitter text-primary me-2"></i>
                        Tweets Encontrados ({{ $tweets->total() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($tweets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%;">Antigüedad</th>
                                        <th style="width: 15%;">Autor</th>
                                        <th style="width: 35%;">Tweet</th>
                                        <th style="width: 15%;" class="text-center">Relevancia IA</th>
                                        <th style="width: 10%;" class="text-center">Métricas</th>
                                        <th style="width: 10%;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tweets as $tweet)
                                        <tr>
                                            <!-- Antigüedad -->
                                            <td>
                                                <small class="text-muted">
                                                    @if($tweet->created_at_twitter)
                                                        {{ $tweet->created_at_twitter->diffForHumans() }}
                                                        <br>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            {{ $tweet->created_at_twitter->format('d/m/Y H:i') }}
                                                        </span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </small>
                                            </td>

                                            <!-- Autor -->
                                            <td>
                                                @if($tweet->account)
                                                    <div class="d-flex align-items-center">
                                                        @if($tweet->account->profile_picture)
                                                            <img src="{{ $tweet->account->profile_picture }}"
                                                                 alt="{{ $tweet->account->username }}"
                                                                 class="rounded-circle me-2"
                                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <strong>{{ $tweet->account->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">@{{ $tweet->account->username }}</small>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fa-solid fa-users"></i>
                                                                {{ number_format($tweet->account->followers ?? 0) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>

                                            <!-- Texto del Tweet -->
                                            <td>
                                                <p class="mb-1">{{ \Illuminate\Support\Str::limit($tweet->text, 150) }}</p>
                                            </td>

                                            <!-- Relevancia IA -->
                                            <td>
                                                @if($tweet->ia_analyzed)
                                                    <div class="mb-1">
                                                        <small class="text-muted">Score: {{ $tweet->ia_score ?? 0 }}/100</small>
                                                    </div>
                                                    <div class="progress" style="height: 20px;">
                                                        @php
                                                            $score = $tweet->ia_score ?? 0;
                                                            $color = $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger');
                                                        @endphp
                                                        <div class="progress-bar bg-{{ $color }}"
                                                             role="progressbar"
                                                             style="width: {{ $score }}%;"
                                                             aria-valuenow="{{ $score }}"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100">
                                                            {{ $score }}
                                                        </div>
                                                    </div>
                                                    @if($tweet->ia_reason)
                                                        <small class="text-muted d-block mt-1" title="{{ $tweet->ia_reason }}">
                                                            {{ \Illuminate\Support\Str::limit($tweet->ia_reason, 50) }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">No analizado</span>
                                                @endif
                                            </td>

                                            <!-- Métricas -->
                                            <td class="text-center">
                                                <small class="d-block">
                                                    <i class="fa-solid fa-heart text-danger"></i> {{ number_format($tweet->like_count ?? 0) }}
                                                </small>
                                                <small class="d-block">
                                                    <i class="fa-solid fa-retweet text-success"></i> {{ number_format($tweet->retweet_count ?? 0) }}
                                                </small>
                                                <small class="d-block">
                                                    <i class="fa-solid fa-comment text-primary"></i> {{ number_format($tweet->reply_count ?? 0) }}
                                                </small>
                                            </td>

                                            <!-- Acciones -->
                                            <td class="text-center">
                                                @if($tweet->url)
                                                    <a href="{{ $tweet->url }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-primary"
                                                       title="Ver en Twitter">
                                                        <i class="fa-brands fa-twitter"></i> Ver
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-brands fa-twitter fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron tweets para esta búsqueda</p>
                        </div>
                    @endif
                </div>

                <!-- Paginación -->
                @if($tweets->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $tweets->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page-specific-scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($search->chart_data ?? []);
            const canvas = document.getElementById('chart-{{ $search->id }}');

            if (canvas && chartData.length > 0) {
                const labels = chartData.map(d => d.label);
                const counts = chartData.map(d => d.count);
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
                                    stepSize: Math.ceil(maxCount / 5),
                                    precision: 0
                                },
                                grid: {
                                    display: true,
                                    drawBorder: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
