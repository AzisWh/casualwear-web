@extends('admin.layout.layout')

@section('title', 'Admin Dashboard - Monitor Transaksi')

@section('content')
    <div class="container">
        <h2>Monitor Transaksi</h2>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Sepatu</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Expired At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->user->nama_depan ?? 'N/A' }} {{ $transaction->user->nama_belakang ?? '' }}</td>
                            <td>{{ $transaction->sepatu->title ?? 'N/A' }}</td>
                            <td>{{ $transaction->jumlah }}</td>
                            <td>Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($transaction->status) }}</td>
                            <?php
                            $expiredDate = \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta');
                            $now = \Carbon\Carbon::now('Asia/Jakarta');
                            ?>
                            <td>{{ $expiredDate->format('d M Y H:i') }} WIB</td>
                            <td>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal_{{ $transaction->id }}">
                                    Detail Transaksi
                                </button>
                                <form action="{{ route('admin.transactions.destroy', $transaction->id) }}" method="POST" style="display:inline;" id="deleteForm_{{ $transaction->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $transaction->id }})">Delete Transaksi</button>
                                </form>
                            </td>
                        </tr>

                          <!-- Modal -->
                        <div class="modal fade" id="detailModal_{{ $transaction->id }}" tabindex="-1" aria-labelledby="detailModalLabel_{{ $transaction->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h5 class="modal-title" id="detailModalLabel_{{ $transaction->id }}">Detail Transaksi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                <p><strong>ID Transaksi:</strong> {{ $transaction->id }}</p>
                                <p><strong>User:</strong> {{ $transaction->user->nama_depan ?? 'N/A' }} {{ $transaction->user->nama_belakang ?? '' }}</p>
                                <p><strong>Sepatu:</strong> {{ $transaction->sepatu->title ?? 'N/A' }}</p>
                                <p><strong>Jumlah:</strong> {{ $transaction->jumlah }}</p>
                                <p><strong>Total Harga:</strong> Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
                                <p><strong>Diskon:</strong> Rp {{ number_format($transaction->discount, 0, ',', '.') }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
                                <?php
                                    $expiredDate = \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta');
                                    $now = \Carbon\Carbon::now('Asia/Jakarta');
                                    ?>
                                <p><strong>Expired At:</strong> {{ $expiredDate->format('d M Y H:i') }} WIB</p>
                                <p><strong>Order ID:</strong> {{ $transaction->order_id ?? 'N/A' }}</p>
                                <p><strong>Snap Token:</strong> {{ $transaction->snap_token ?? 'N/A' }}</p>
                                <hr>
                                <h5>Informasi Pengiriman</h5>
                                <p><strong>Asal:</strong> {{ $transaction->origin ?? 'N/A' }}</p>
                                <p><strong>Tujuan:</strong> {{ $transaction->destination_city ?? $transaction->destination ?? 'N/A' }}</p>
                                <p><strong>Kurir:</strong> {{ $transaction->courier ?? 'N/A' }}</p>
                                <p><strong>Biaya Pengiriman:</strong> Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</p>
                                <p><strong>Layanan:</strong> {{ $transaction->service ?? 'N/A' }}</p>
                                <p><strong>Alamat:</strong> {{ $transaction->alamat ?? 'N/A' }}</p>
                                <p><strong>Deskripsi Alamat:</strong> {{ $transaction->deskripsi_alamat ?? 'N/A' }}</p>
                                </div>
                            </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

      
    </div>

    <script>
        // $(document).ready(function() {
        //     $('.detail-btn').click(function() {
        //         const id = $(this).data('id');

        //         $.ajax({
        //             url: '/admin/transactions/' + id + '/detail',
        //             type: 'GET',
        //             dataType: 'html',
        //             success: function(response) {
        //                 $('#detailContent').html(response);
        //             },
        //             error: function() {
        //                 $('#detailContent').html('<p class="text-danger">Gagal memuat detail transaksi.</p>');
        //             }
        //         });
        //     });
        // });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data transaksi ini akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm_' + id).submit();
                }
            });
        }
    </script>
@endsection
