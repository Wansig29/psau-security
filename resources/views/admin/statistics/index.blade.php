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
    <div class="stat-grid" style="margin-top:20px;">
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
        <div class="stat-card" style="border-left-color:#f59e0b;">
            <div class="stat-icon" style="color:#f59e0b;background:#fef3c7;"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalWarnings) }}</div>
                <div class="stat-label">Warnings Issued</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#ef4444;">
            <div class="stat-icon" style="color:#ef4444;background:#fee2e2;"><i class="fas fa-ban"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalSevere) }}</div>
                <div class="stat-label">Suspensions & Revocations</div>
            </div>
        </div>
    </div>

    <!-- Row 1: Doughnut + Monthly Bar -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;margin-top:20px;">

        <!-- Doughnut Chart: Types -->
        <div class="card" style="padding:24px;">
            <h3 style="margin-bottom:20px;font-size:15px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:12px;">
                <i class="fas fa-chart-pie" style="color:var(--maroon);margin-right:8px;"></i> Violation Type Breakdown
            </h3>
            @if($typeBreakdown->isEmpty())
                <div class="empty-state" style="padding:40px 0;">
                    <i class="fas fa-chart-pie" style="font-size:32px;color:#d1d5db;margin-bottom:10px;display:block;"></i>
                    <p style="color:#6b7280;font-size:13px;">No data for this school year.</p>
                </div>
            @else
                <div style="position:relative;height:220px;">
                    <canvas id="typeChart"></canvas>
                </div>
                {{-- Totals per violation type --}}
                <div style="margin-top:16px;border-top:1px solid #f3f4f6;padding-top:12px;">
                    @php $palette = ['#800000','#ef4444','#f59e0b','#3b82f6','#10b981','#6366f1','#8b5cf6','#14b8a6']; $ti = 0; @endphp
                    @foreach($typeBreakdown as $typeName => $typeCount)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f9fafb;">
                            <div style="display:flex;align-items:center;gap:7px;">
                                <span style="width:10px;height:10px;border-radius:50%;background:{{ $palette[$ti % count($palette)] }};display:inline-block;flex-shrink:0;"></span>
                                <span style="font-size:12px;color:#374151;font-weight:500;">{{ $typeName }}</span>
                            </div>
                            <span style="font-size:13px;font-weight:700;color:#111827;">{{ number_format($typeCount) }}</span>
                        </div>
                        @php $ti++; @endphp
                    @endforeach
                    <div style="display:flex;justify-content:space-between;padding:7px 0 0;">
                        <span style="font-size:12px;font-weight:700;color:#374151;">Total</span>
                        <span style="font-size:13px;font-weight:800;color:var(--maroon);">{{ number_format($totalViolations) }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Bar Chart: Monthly Trend -->
        <div class="card" style="padding:24px;">
            <h3 style="margin-bottom:20px;font-size:15px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:12px;">
                <i class="fas fa-chart-bar" style="color:var(--maroon);margin-right:8px;"></i> Monthly Violation Trend ({{ $currentYear }})
            </h3>
            <div style="position:relative;height:280px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: Violations by Location (full width stacked bar) -->
    <div class="card" style="padding:24px;margin-top:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding-bottom:12px;margin-bottom:20px;">
            <h3 style="font-size:15px;color:#111827;margin:0;">
                <i class="fas fa-map-marker-alt" style="color:var(--maroon);margin-right:8px;"></i>
                Violations by Location <span style="font-size:12px;font-weight:400;color:#6b7280;">(Top 10 hotspots — helps decide where to add security)</span>
            </h3>
        </div>

        @if(empty($locationLabels))
            <div class="empty-state" style="padding:40px 0;">
                <i class="fas fa-map" style="font-size:36px;color:#d1d5db;margin-bottom:10px;display:block;"></i>
                <p style="color:#6b7280;font-size:13px;">No location data recorded yet. Make sure security personnel fill in location notes when logging violations.</p>
            </div>
        @else
            <!-- Legend pills -->
            <div id="locationLegend" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;"></div>
            <div style="position:relative;height:{{ max(280, count($locationLabels) * 48) }}px;">
                <canvas id="locationChart"></canvas>
            </div>
            <p style="font-size:11px;color:#9ca3af;margin-top:8px;text-align:right;">
                <i class="fas fa-info-circle"></i> Each bar segment represents a distinct violation type at that location.
            </p>

            {{-- Location Totals Table --}}
            <div style="margin-top:20px;border-top:2px solid #f3f4f6;padding-top:16px;">
                <h4 style="font-size:13px;font-weight:700;color:#111827;margin-bottom:12px;">
                    <i class="fas fa-table" style="color:var(--maroon);margin-right:6px;"></i> Location Summary
                </h4>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:12px;">
                        <thead>
                            <tr style="background:#f9fafb;">
                                <th style="text-align:left;padding:8px 12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Location</th>
                                @php
                                    $allTypeNames = collect($locationDatasets)->pluck('label');
                                @endphp
                                @foreach($allTypeNames as $tn)
                                    <th style="text-align:center;padding:8px 10px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;white-space:nowrap;">{{ $tn }}</th>
                                @endforeach
                                <th style="text-align:center;padding:8px 12px;color:#111827;font-weight:700;border-bottom:1px solid #e5e7eb;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; $typeTotals = array_fill(0, count($allTypeNames), 0); @endphp
                            @foreach($locationLabels as $li => $area)
                                @php
                                    $rowTotal = 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f3f4f6;{{ $li % 2 === 0 ? '' : 'background:#fafafa;' }}">
                                    <td style="padding:7px 12px;color:#374151;font-weight:500;max-width:220px;">
                                        {{ strlen($area) > 40 ? substr($area, 0, 40).'…' : $area }}
                                    </td>
                                    @foreach($locationDatasets as $di => $ds)
                                        @php
                                            $val = $ds['data'][$li] ?? 0;
                                            $rowTotal += $val;
                                            $typeTotals[$di] = ($typeTotals[$di] ?? 0) + $val;
                                        @endphp
                                        <td style="text-align:center;padding:7px 10px;color:{{ $val > 0 ? '#111827' : '#d1d5db' }};font-weight:{{ $val > 0 ? '600' : '400' }};">
                                            {{ $val > 0 ? $val : '—' }}
                                        </td>
                                    @endforeach
                                    @php $grandTotal += $rowTotal; @endphp
                                    <td style="text-align:center;padding:7px 12px;font-weight:800;color:var(--maroon);">{{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
                                <td style="padding:8px 12px;font-weight:700;color:#111827;">Total</td>
                                @foreach($typeTotals as $tt)
                                    <td style="text-align:center;padding:8px 10px;font-weight:700;color:#374151;">{{ $tt }}</td>
                                @endforeach
                                <td style="text-align:center;padding:8px 12px;font-weight:800;color:var(--maroon);font-size:14px;">{{ $grandTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const maroon = '#800000';

    // ── 1. Doughnut: Violation Types ──────────────────────────────────────────
    @if($typeBreakdown->isNotEmpty())
    new Chart(document.getElementById('typeChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($typeBreakdown->keys()) !!},
            datasets: [{
                data: {!! json_encode($typeBreakdown->values()) !!},
                backgroundColor: [maroon,'#ef4444','#f59e0b','#3b82f6','#10b981','#6366f1','#8b5cf6','#14b8a6'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
    @endif

    // ── 2. Line: Monthly Trend ────────────────────────────────────────────────
    (function() {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(128,0,0,0.25)');
        gradient.addColorStop(1, 'rgba(128,0,0,0.01)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Violations',
                    data: {!! json_encode($monthlyData) !!},
                    borderColor: maroon,
                    borderWidth: 2.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: maroon,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (item) => ` ${item.raw} violation${item.raw !== 1 ? 's' : ''}`
                        }
                    }
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
    })();

    // ── 3. Stacked Horizontal Bar: Location Hotspot ───────────────────────────
    @if(!empty($locationLabels))
    const locationDatasets = {!! json_encode($locationDatasets) !!};
    const locationLabels   = {!! json_encode($locationLabels) !!};

    const locationChart = new Chart(document.getElementById('locationChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: locationLabels,
            datasets: locationDatasets
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => items[0].label,
                        label: (item) => ` ${item.dataset.label}: ${item.raw} violations`,
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#6b7280', font: { size: 11 } },
                    grid: { color: '#f3f4f6' }
                },
                y: {
                    stacked: true,
                    ticks: {
                        color: '#374151',
                        font: { size: 12, weight: '600' },
                        callback: function(val, idx) {
                            const label = this.getLabelForValue(val);
                            return label.length > 32 ? label.substring(0, 32) + '…' : label;
                        }
                    },
                    grid: { display: false }
                }
            }
        }
    });

    // Custom legend pills
    const legendEl = document.getElementById('locationLegend');
    locationDatasets.forEach(ds => {
        const pill = document.createElement('span');
        pill.style.cssText = `display:inline-flex;align-items:center;gap:5px;background:#f3f4f6;
            border-radius:999px;padding:3px 10px;font-size:11px;font-weight:600;color:#374151;cursor:pointer;`;
        const dot = document.createElement('span');
        dot.style.cssText = `width:10px;height:10px;border-radius:50%;background:${ds.backgroundColor};display:inline-block;flex-shrink:0;`;
        pill.appendChild(dot);
        pill.appendChild(document.createTextNode(ds.label));
        legendEl.appendChild(pill);
    });
    @endif
});
</script>
@endpush
