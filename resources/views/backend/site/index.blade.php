@extends('layouts.app')

@section('content')
<div class="container">
    <x-page-header 
        title="Dashboard"
        icon="bi-speedometer2"
    />
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><i class="bi bi-folder2-open"></i> Total Projects</div>
                <div class="card-body">
                    <h2 class="card-title text-center display-4">{{ $totalProjects }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><i class="bi bi-people-fill"></i> Total Researchers</div>
                <div class="card-body">
                    <h2 class="card-title text-center display-4">{{ $totalResearchers }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <x-card title="Projects by Status" icon="bi-pie-chart-fill" color="blue">
                <canvas id="statusChart"></canvas>
            </x-card>
        </div>
        <div class="col-md-6 mb-4">
            <x-card title="Projects by Year (Last 5 Years)" icon="bi-bar-chart-fill" color="blue">
                <canvas id="yearChart"></canvas>
            </x-card>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($projectsByStatus->pluck('status.ps_name')) !!},
            datasets: [{
                data: {!! json_encode($projectsByStatus->pluck('total')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ]
            }]
        }
    });

    // Year Chart
    const yearCtx = document.getElementById('yearChart').getContext('2d');
    new Chart(yearCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($projectsByYear->pluck('year.year_name')) !!},
            datasets: [{
                label: 'Number of Projects',
                data: {!! json_encode($projectsByYear->pluck('total')) !!},
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
