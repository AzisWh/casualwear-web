@extends('superadmin.layout.layout')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container">
    <h3 class="mb-4">Super Admin Panel</h3>

    <div class="card">
        <div class="card-body">
            <canvas id="transaksiChart" style="min-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Table for Transaction Details -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">Detail Transaksi per Bulan</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Bulan</th>
                            <th>Nama Sepatu</th>
                            <th>Invoice</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksiPerBulan as $transaction)
                            <tr>
                                <td>{{ date('F', mktime(0, 0, 0, $transaction->bulan, 1)) }}</td>
                                <td>{{ $transaction->shoe_name }}</td>
                                <td>{{ $transaction->invoice }}</td>
                                <td>{{ $transaction->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        const rawData = @json($transaksiPerBulan);
        console.log("Data transaksiPerBulan dari Laravel:", rawData);

        if (!rawData || rawData.length === 0) {
            console.warn("No transaction data available.");
            return;
        }

        const allMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Group data by month and shoe name
        const monthlyData = {};
        rawData.forEach(item => {
            const monthIndex = item.bulan - 1;
            if (!monthlyData[monthIndex]) monthlyData[monthIndex] = {};
            monthlyData[monthIndex][item.shoe_name] = (monthlyData[monthIndex][item.shoe_name] || 0) + item.total;
        });

        const shoeNames = [...new Set(rawData.map(item => item.shoe_name))];
        const chartData = allMonths.map((_, i) => shoeNames.map(shoe => monthlyData[i]?.[shoe] || 0));

        console.log("Data siap untuk Chart.js:", chartData);

        const ctx = document.getElementById('transaksiChart').getContext('2d');
        if (!ctx) {
            console.error("Canvas context not found.");
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: allMonths,
                datasets: shoeNames.map((shoe, index) => ({
                    label: shoe,
                    data: chartData.map(row => row[index]),
                    backgroundColor: `rgba(${Math.floor(255 / shoeNames.length * index)}, ${Math.floor(128 + 127 / shoeNames.length * index)}, 235, 0.6)`,
                    borderColor: `rgba(${Math.floor(255 / shoeNames.length * index)}, ${Math.floor(128 + 127 / shoeNames.length * index)}, 235, 1)`,
                    borderWidth: 1
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const invoice = rawData.find(item => 
                                    item.bulan - 1 === context.dataIndex && 
                                    shoeNames[context.datasetIndex] === item.shoe_name
                                )?.invoice;
                                return `${context.dataset.label}: ${context.raw} ${invoice ? `(Invoice: ${invoice})` : ''}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection