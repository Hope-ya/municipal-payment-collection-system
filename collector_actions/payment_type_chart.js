let paymentChart = null; // Store chart instance globally

fetch('collector_actions/fetch_payment_data.php')
    .then(response => response.json())
    .then(data => {
        const ctx = document.getElementById('paymentTypeChart').getContext('2d');

        paymentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Cash', 'Check'],
                datasets: [{
                    data: [data.Cash, data.Check],
                    backgroundColor: ['#3B82F6', '#10B981'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#6B7280'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let value = context.raw;
                                return `${context.label}: ₱${value.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}`;
                            }
                        }
                    }
                },
                layout: {
                    padding: 10
                }
            }
        });

        // ✅ Resize observer to handle zoom or container size changes
        const container = document.getElementById('paymentTypeChartContainer');
        const observer = new ResizeObserver(() => {
            if (paymentChart) {
                paymentChart.resize();
            }
        });
        observer.observe(container);
    })
    .catch(err => console.error(err));
