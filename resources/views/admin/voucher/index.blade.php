@extends('admin.layout.layout')

@section('title','Admin Dashboard')

@section('content')
<div class="container">
    <h1>Data Voucher</h1>
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addModal">
        Tambah Voucher
    </button>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h2>List Sepatu</h2></div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead style="background-color: black;">
                    <tr class="text-white">
                        <th>Kode</th>
                        <th>Diskon</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Berakhir</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->discount_type == 'percentage' ? $item->discount_value . '%' : 'Rp ' . number_format($item->discount_value, 0, ',', '.') }}</td>
                            <td>{{ $item->start_date->format('d M Y') }}</td>
                            <td>{{ $item->end_date->format('d M Y') }}</td>
                            <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editVoucher({{ $item }})">Edit</button>
                                <form action="{{ route('admin.voucher.destroy', $item->id) }}" method="POST" style="display:inline;" id="deleteForm_{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $item->id }})">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Voucher --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.voucher.store') }}" method="POST" enctype="multipart/form-data"> 
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addVoucherModalLabel">Tambah Voucher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Voucher</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai Diskon</label>
                            <input type="number" name="discount_value" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Diskon</label>
                            <select name="discount_type" class="form-control" required>
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" name="start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Berakhir</label>
                            <input type="datetime-local" name="end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maksimum Penggunaan (Opsional)</label>
                            <input type="number" name="max_usage" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Voucher --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editVoucherModalLabel">Edit Voucher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Voucher</label>
                            <input type="text" name="code" id="edit_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai Diskon</label>
                            <input type="number" name="discount_value" id="edit_discount_value" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Diskon</label>
                            <select name="discount_type" id="edit_discount_type" class="form-control" required>
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" name="start_date" id="edit_start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Berakhir</label>
                            <input type="datetime-local" name="end_date" id="edit_end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maksimum Penggunaan (Opsional)</label>
                            <input type="number" name="max_usage" id="edit_max_usage" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="edit_is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function editVoucher(item) {
        $('#editForm').attr('action', '/admin-voucher/' + item.id);
        $('#edit_code').val(item.code);
        $('#edit_discount_value').val(item.discount_value);
        $('#edit_discount_type').val(item.discount_type);
        $('#edit_start_date').val(new Date(item.start_date).toISOString().slice(0, 16));
        $('#edit_end_date').val(new Date(item.end_date).toISOString().slice(0, 16));
        $('#edit_max_usage').val(item.max_usage || '');
        $('#edit_is_active').val(item.is_active ? '1' : '0');

        $('#editModal').modal('show');
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data voucher ini akan dihapus!',
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