<div class="row">
    <div class="col-md-6">
        <h5>Informasi Transaksi</h5>
        <p><strong>ID Transaksi:</strong> {{ $transaction->id }}</p>
        <p><strong>User:</strong> {{ $transaction->user->nama_depan ?? 'N/A' }} {{ $transaction->user->nama_belakang ?? '' }}</p>
        <p><strong>Sepatu:</strong> {{ $transaction->sepatu->title ?? 'N/A' }}</p>
        <p><strong>Jumlah:</strong> {{ $transaction->jumlah }}</p>
        <p><strong>Total Harga:</strong> Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</p>
        <p><strong>Diskon:</strong> Rp {{ number_format($transaction->discount, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
        <p><strong>Expired At:</strong> {{ $transaction->expired_at ? $transaction->expired_at->format('d M Y H:i') : 'N/A' }} WIB</p>
        <p><strong>Order ID:</strong> {{ $transaction->order_id ?? 'N/A' }}</p>
        <p><strong>Snap Token:</strong> {{ $transaction->snap_token ?? 'N/A' }}</p>
    </div>
    <div class="col-md-6">
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