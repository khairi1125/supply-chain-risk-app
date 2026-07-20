<div class="card mb-4" style="border-radius: 10px;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Risk Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="riskDistributionChart" height="200"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('riskDistributionChart');
    if (ctx) {
        window.riskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High', 'Critical'],
                datasets: [{
                    data: [
                        {{ $stats['low_risk'] }},
                        {{ $stats['medium_risk'] }},
                        {{ $stats['high_risk'] }},
                        {{ $stats['critical_risk'] }}
                    ],
                    backgroundColor: [
                        '#28a745',  // Green
                        '#ffc107',  // Yellow
                        '#fd7e14',  // Orange
                        '#dc3545'   // Red
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': ' + value + ' countries';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
