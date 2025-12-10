@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Projects</div>
                <div class="card-body">
                    <h2 class="card-title">{{ $totalProjects }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Researchers</div>
                <div class="card-body">
                    <h2 class="card-title">{{ $totalResearchers }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Projects by Status</div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Projects by Year (Last 5 Years)</div>
                <div class="card-body">
                    <canvas id="yearChart"></canvas>
                </div>
            </div>
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
