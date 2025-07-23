@extends('admin.layout.layout')

@section('title','Admin Dashboard')

@section('content')
<div class="container">
    <h5>User Admin: <strong>{{ Auth::user()->nama_depan }}</strong></h5>

    <form method="GET" class="my-3 ">
        <select class="form-control" name="filterchart" class="form-select w-auto d-inline" onchange="this.form.submit()">
            <option value="today" {{ $filterchart == 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week" {{ $filterchart == 'week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="month" {{ $filterchart == 'month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="year" {{ $filterchart == 'year' ? 'selected' : '' }}>Tahun Ini</option>
        </select>
    </form>

    <canvas id="salesChart" height="100"></canvas>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesChart = document.getElementById('salesChart').getContext('2d');
    const chart = new Chart(salesChart, {
        type: 'bar',
        data: {
            labels: {!! json_encode($datapenjualan->pluck('tanggal')) !!},
            datasets: [{
                label: 'Total Penjualan',
                data: {!! json_encode($datapenjualan->pluck('total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
