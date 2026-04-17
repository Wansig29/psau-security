@extends('layouts.admin')

@section('title', 'Violation Statistics — PSAU Parking')

@section('topbar-title', 'Violation Analytics & Statistics')

@section('content')
    <div class="tab-nav">
        <a class="tab-btn" href="{{ route('admin.dashboard') }}"><i class="fas fa-hourglass-half"></i> Pending Reviews</a>
        <a class="tab-btn" href="{{ route('admin.approved.index') }}"><i class="fas fa-check-circle"></i> Approved</a>
        <a class="tab-btn" href="{{ route('admin.sanctions.index') }}"><i class="fas fa-balance-scale"></i> Violations & Sanctions</a>
        <span class="tab-btn active tab-statistics"><i class="fas fa-chart-pie"></i> Statistics</span>
    </div>

    <!-- Summary Metrics -->
    <div class="stat-grid" style="margin-top: 20px;">
        <div class="stat-card c-maroon">
            <div class="stat-icon c-maroon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalViolations) }}</div>
                <div class="stat-label">Total Violations ({{ $currentYear }})</div>
            </div>
        </div>
        <div class="stat-card c-red">
            <div class="stat-icon c-red"><i class="fas fa-gavel"></i></div>
            <div>
                <div class="stat-value">{{ number_format($activeSanctions) }}</div>
                <div class="stat-label">Active Sanctions</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #f59e0b;">
            <div class="stat-icon" style="color: #f59e0b; background: #fef3c7;"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalWarnings) }}</div>
                <div class="stat-label">Warnings Issued</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #ef4444;">
            <div class="stat-icon" style="color: #ef4444; background: #fee2e2;"><i class="fas fa-ban"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalSevere) }}</div>
                <div class="stat-label">Suspensions & Revocations</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
        
        <!-- Doughnut Chart: Types -->
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 20px; font-size: 16px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px;">
                <i class="fas fa-chart-pie" style="color: var(--maroon); margin-right: 8px;"></i> Violation Breakdown
            </h3>
            @if($typeBreakdown->isEmpty())
                <div class="empty-state" style="padding: 40px 0;">
                    <i class="fas fa-chart-pie" style="font-size: 32px; color: #d1d5db; margin-bottom: 10px;"></i>
                    <p style="color: #6b7280; font-size: 13px;">No data available for this year.</p>
                </div>
            @else
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="typeChart"></canvas>
                </div>
            @endif
        </div>

        <!-- Bar Chart: Monthly Trend -->
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 20px; font-size: 16px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px;">
                <i class="fas fa-chart-line" style="color: var(--maroon); margin-right: 8px;"></i> Frequency ({{ $currentYear }})
            </h3>
            
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const maroonHex = '#800000';
        
        // 1. Violation Types Chart
        @if($typeBreakdown->isNotEmpty())
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        const typeLabels = {!! json_encode($typeBreakdown->keys()) !!};
        const typeData = {!! json_encode($typeBreakdown->values()) !!};
        
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: [
                        maroonHex,
                        '#ef4444', // Red
                        '#f59e0b', // Amber
                        '#3b82f6', // Blue
                        '#10b981', // Emerald
                        '#6366f1', // Indigo
                        '#8b5cf6'  // Purple
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                },
                cutout: '70%'
            }
        });
        @endif

        // 2. Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyLabels = {!! json_encode($monthlyLabels) !!};
        const monthlyData = {!! json_encode($monthlyData) !!};

        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Violations',
                    data: monthlyData,
                    backgroundColor: maroonHex,
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6b7280', font: { size: 11 } },
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        ticks: { color: '#6b7280', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
