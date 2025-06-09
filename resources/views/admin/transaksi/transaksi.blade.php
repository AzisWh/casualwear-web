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
                        <th>Shipping Status</th>
                        <th>Cancellation Status</th>
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
                            <td>
                                <span class="badge bg-{{ $transaction->status === 'pending' ? 'warning' : ($transaction->status === 'success' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction->shipping_status ? ($transaction->shipping_status === 'delivered' ? 'success' : ($transaction->shipping_status === 'shipped' ? 'info' : ($transaction->shipping_status === 'processed' ? 'primary' : 'danger'))) : 'secondary' }}">
                                    {{ $transaction->shipping_status ?? 'menunggu pembayaran' }}
                                </span>
                            </td>
                            <td>
                                @if ($transaction->cancellation_status)
                                    <span class="badge bg-{{ $transaction->cancellation_status === 'pending_cancellation' ? 'warning' : ($transaction->cancellation_status === 'approved' ? 'success' : 'danger') }}">
                                        {{ ucfirst($transaction->cancellation_status) }}
                                    </span>
                                    @if ($transaction->cancel_reason)
                                        <br><small>Alasan: {{ $transaction->cancel_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Tidak Ada</span>
                                @endif
                            </td>
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
                                <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#updateShippingModal_{{ $transaction->id }}">
                                    Update Shipping Status
                                </button>
                                @if ($transaction->cancellation_status === 'pending_cancellation')
                                    <button class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#approveCancelModal_{{ $transaction->id }}">Approve Cancellation</button>
                                    <button class="btn btn-warning btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#rejectCancelModal_{{ $transaction->id }}">Reject Cancellation</button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal for Transaction Details -->
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
                                        <p><strong>Status:</strong> 
                                            <span class="badge bg-{{ $transaction->status === 'pending' ? 'warning' : ($transaction->status === 'success' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </p>
                                        <p><strong>Status Pembatalan:</strong> 
                                            @if ($transaction->cancellation_status)
                                                <span class="badge bg-{{ $transaction->cancellation_status === 'pending_cancellation' ? 'warning' : ($transaction->cancellation_status === 'approved' ? 'success' : 'danger') }}">
                                                    {{ ucfirst($transaction->cancellation_status) }}
                                                </span>
                                                @if ($transaction->cancel_reason)
                                                    <br><small>Alasan: {{ $transaction->cancel_reason }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Tidak Ada</span>
                                            @endif
                                        </p>
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
                                        <p><strong>Status Pengiriman:</strong> 
                                            <span class="badge bg-{{ $transaction->shipping_status ? ($transaction->shipping_status === 'delivered' ? 'success' : ($transaction->shipping_status === 'shipped' ? 'info' : ($transaction->shipping_status === 'processed' ? 'primary' : 'danger'))) : 'secondary' }}">
                                                {{ $transaction->shipping_status ?? 'menunggu pembayaran' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Updating Shipping Status -->
                        <div class="modal fade" id="updateShippingModal_{{ $transaction->id }}" tabindex="-1" aria-labelledby="updateShippingModalLabel_{{ $transaction->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateShippingModalLabel_{{ $transaction->id }}">Update Status Pengiriman</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('admin.transactions.update.shipping', $transaction->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3">
                                                <label for="shipping_status_{{ $transaction->id }}" class="form-label">Status Pengiriman</label>
                                                <select name="shipping_status" id="shipping_status_{{ $transaction->id }}" class="form-control form-select" required>
                                                    <option value="">Pilih Status</option>
                                                    <option value="processed" {{ $transaction->shipping_status === 'processed' ? 'selected' : '' }}>Diproses</option>
                                                    <option value="shipped" {{ $transaction->shipping_status === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                                                    <option value="delivered" {{ $transaction->shipping_status === 'delivered' ? 'selected' : '' }}>Diterima</option>
                                                    <option value="cancelled" {{ $transaction->shipping_status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Approving Cancellation -->
                        <div class="modal fade" id="approveCancelModal_{{ $transaction->id }}" tabindex="-1" aria-labelledby="approveCancelModalLabel_{{ $transaction->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="approveCancelModalLabel_{{ $transaction->id }}">Setujui Pembatalan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin menyetujui pembatalan untuk transaksi #{{ $transaction->id }}?</p>
                                        <form action="{{ route('admin.transactions.approve.cancellation', $transaction->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3">
                                                <label for="admin_notes_{{ $transaction->id }}" class="form-label">Catatan Admin (Opsional)</label>
                                                <textarea name="admin_notes" id="admin_notes_{{ $transaction->id }}" class="form-control" rows="3" placeholder="Tambahkan catatan jika perlu"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success">Setujui</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Rejecting Cancellation -->
                        <div class="modal fade" id="rejectCancelModal_{{ $transaction->id }}" tabindex="-1" aria-labelledby="rejectCancelModalLabel_{{ $transaction->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="rejectCancelModalLabel_{{ $transaction->id }}">Tolak Pembatalan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin menolak pembatalan untuk transaksi #{{ $transaction->id }}?</p>
                                        <form action="{{ route('admin.transactions.reject.cancellation', $transaction->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3">
                                                <label for="admin_notes_{{ $transaction->id }}" class="form-label">Catatan Admin (Opsional)</label>
                                                <textarea name="admin_notes" id="admin_notes_{{ $transaction->id }}" class="form-control" rows="3" placeholder="Tambahkan catatan jika perlu"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        </form>
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