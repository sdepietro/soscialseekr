@extends('admin_panel.layouts.master')

@section('title', 'Dashboard')

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <!-- Selector de período -->
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin::home', ['period' => '24h']) }}"
                           class="btn btn-sm {{ $period === '24h' ? 'btn-primary' : 'btn-outline-primary' }}">
                            24 Horas
                        </a>
                        <a href="{{ route('admin::home', ['period' => '7d']) }}"
                           class="btn btn-sm {{ $period === '7d' ? 'btn-primary' : 'btn-outline-primary' }}">
                            7 Días
                        </a>
                        <a href="{{ route('admin::home', ['period' => '30d']) }}"
                           class="btn btn-sm {{ $period === '30d' ? 'btn-primary' : 'btn-outline-primary' }}">
                            30 Días
                        </a>
                    </div>
                </div>
                <h4 class="mb-0">Dashboard</h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin::home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>

    @if(!$kpis)
        <!-- Mensaje cuando no hay búsquedas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
                        <h5>No hay búsquedas configuradas</h5>
                        <p class="text-muted">Crea tu primera búsqueda para comenzar a monitorear tweets</p>
                        <a href="{{ route('admin::searches.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus-circle me-1"></i> Crear Primera Búsqueda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- KPIs Cards -->
        <div class="row">
            <!-- Total Tweets -->
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Tweets</p>
                                <h3 class="mb-0">{{ number_format($kpis['total_tweets']['value']) }}</h3>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-{{ $kpis['total_tweets']['trend'] === 'up' ? 'success' : 'danger' }}">
                                        <i class="fa-solid fa-arrow-{{ $kpis['total_tweets']['trend'] }}"></i>
                                        {{ abs($kpis['total_tweets']['change']) }}%
                                    </span>
                                    <small class="text-muted ms-1">vs período anterior</small>
                                </p>
                            </div>
                            <div class="avatar-md bg-soft-primary rounded">
                                <i class="fa-brands fa-twitter fs-32 text-primary d-flex align-items-center justify-content-center" style="height: 100%;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tweets Alto Valor -->
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Tweets Alto Valor</p>
                                <h3 class="mb-0">{{ number_format($kpis['high_value_tweets']['value']) }}</h3>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-success">
                                        {{ $kpis['high_value_tweets']['percentage'] }}%
                                    </span>
                                    <small class="text-muted ms-1">del total</small>
                                </p>
                            </div>
                            <div class="avatar-md bg-soft-success rounded">
                                <i class="fa-solid fa-star fs-32 text-success d-flex align-items-center justify-content-center" style="height: 100%;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Promedio IA -->
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Score Promedio IA</p>
                                <h3 class="mb-2">{{ $kpis['avg_ia_score']['value'] }}/100</h3>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $kpis['avg_ia_score']['color'] }}"
                                         role="progressbar"
                                         style="width: {{ $kpis['avg_ia_score']['value'] }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="avatar-md bg-soft-{{ $kpis['avg_ia_score']['color'] }} rounded">
                                <i class="fa-solid fa-robot fs-32 text-{{ $kpis['avg_ia_score']['color'] }} d-flex align-items-center justify-content-center" style="height: 100%;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Engagement Total -->
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Engagement Total</p>
                                <h3 class="mb-0">{{ number_format($kpis['total_engagement']['value']) }}</h3>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-{{ $kpis['total_engagement']['trend'] === 'up' ? 'success' : 'danger' }}">
                                        <i class="fa-solid fa-arrow-{{ $kpis['total_engagement']['trend'] }}"></i>
                                        {{ abs($kpis['total_engagement']['change']) }}%
                                    </span>
                                    <small class="text-muted ms-1">vs período anterior</small>
                                </p>
                            </div>
                            <div class="avatar-md bg-soft-info rounded">
                                <i class="fa-solid fa-chart-line fs-32 text-info d-flex align-items-center justify-content-center" style="height: 100%;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Timeline de Tweets -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa-solid fa-chart-bar text-primary me-2"></i>
                            Timeline de Tweets
                            <small class="text-muted">({{ $period === '24h' ? 'Últimas 24 horas' : ($period === '7d' ? 'Últimos 7 días' : 'Últimos 30 días') }})</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;">
                            <canvas id="timelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance por Búsqueda -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa-solid fa-ranking-star text-primary me-2"></i>
                            Top Búsquedas
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($topSearches->count() > 0)
                            <div style="position: relative; height: 300px;">
                                <canvas id="searchesChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa-solid fa-search fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay datos disponibles</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Tweets Relevantes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa-solid fa-star text-warning me-2"></i>
                            Tweets de Alto Valor
                            <span class="badge bg-warning text-dark ms-2">Score ≥ 70</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($topTweets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 8%;">Antigüedad</th>
                                            <th style="width: 15%;">Autor</th>
                                            <th style="width: 40%;">Tweet</th>
                                            <th style="width: 15%;" class="text-center">Score IA</th>
                                            <th style="width: 12%;" class="text-center">Engagement</th>
                                            <th style="width: 10%;" class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topTweets as $tweet)
                                            <tr>
                                                <!-- Antigüedad -->
                                                <td>
                                                    <small class="text-muted">
                                                        @if($tweet->created_at_twitter)
                                                            {{ $tweet->created_at_twitter->diffForHumans(null, true, true) }}
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
                                                                <div><strong>{{ \Illuminate\Support\Str::limit($tweet->account->name, 20) }}</strong></div>
                                                                <small class="text-muted">{{ '@'.$tweet->account->username }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>

                                                <!-- Texto -->
                                                <td>
                                                    <p class="mb-0">{{ \Illuminate\Support\Str::limit($tweet->text, 120) }}</p>
                                                </td>

                                                <!-- Score IA -->
                                                <td>
                                                    <div class="text-center">
                                                        <small class="d-block mb-1">{{ $tweet->ia_score }}/100</small>
                                                        <div class="progress" style="height: 18px;">
                                                            @php
                                                                $color = $tweet->ia_score >= 80 ? 'success' : ($tweet->ia_score >= 70 ? 'warning' : 'danger');
                                                            @endphp
                                                            <div class="progress-bar bg-{{ $color }}"
                                                                 role="progressbar"
                                                                 style="width: {{ $tweet->ia_score }}%;">
                                                                {{ $tweet->ia_score }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Engagement -->
                                                <td class="text-center">
                                                    <small class="d-block">
                                                        <i class="fa-solid fa-heart text-danger"></i> {{ number_format($tweet->like_count ?? 0) }}
                                                    </small>
                                                    <small class="d-block">
                                                        <i class="fa-solid fa-retweet text-success"></i> {{ number_format($tweet->retweet_count ?? 0) }}
                                                    </small>
                                                </td>

                                                <!-- Acción -->
                                                <td class="text-center">
                                                    @if($tweet->url)
                                                        <a href="{{ $tweet->url }}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-primary"
                                                           title="Ver en Twitter">
                                                            <i class="fa-brands fa-twitter"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa-solid fa-star fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay tweets de alto valor en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('page-specific-scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @if($kpis)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Timeline Chart
            const timelineCanvas = document.getElementById('timelineChart');
            if (timelineCanvas) {
                const timelineData = @json($charts['timeline']);

                new Chart(timelineCanvas, {
                    type: 'bar',
                    data: {
                        labels: timelineData.labels,
                        datasets: [{
                            label: 'Tweets',
                            data: timelineData.data,
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
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Searches Performance Chart
            const searchesCanvas = document.getElementById('searchesChart');
            if (searchesCanvas) {
                const searchesData = @json($topSearches);

                new Chart(searchesCanvas, {
                    type: 'bar',
                    data: {
                        labels: searchesData.map(s => s.name),
                        datasets: [
                            {
                                label: 'Tweets',
                                data: searchesData.map(s => s.tweets_count),
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Score IA',
                                data: searchesData.map(s => s.avg_score),
                                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 1,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                type: 'category',
                                position: 'left'
                            },
                            y1: {
                                type: 'linear',
                                display: false,
                                position: 'right',
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
@endsection
