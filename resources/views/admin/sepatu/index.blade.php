@extends('admin.layout.layout')

@section('title', 'Data Sepatu')

@section('content')
<div class="container">
    <h1>Data Sepatu</h1>
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addModal">
        Tambah Sepatu
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
                        <th>Nama Sepatu</th>
                        <th>Kategori</th>
                        <th>Ukuran</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ $item->size }}</td>
                            <td>{{ $item->stok }}</td>
                            <td>{{ $item->harga_sepatu }}</td>
                            <td>
                                @if($item->image_sepatu)
                                    <img src="{{ asset('storage/' . $item->image_sepatu) }}" width="80">
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editSepatu({{ $item }})">Edit</button>
                                <form action="{{ route('admin.sepatu.destroy', $item->id) }}" method="POST" style="display:inline;" id="deleteForm_{{ $item->id }}">
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

    {{-- Modal Tambah --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.sepatu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Sepatu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Sepatu</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ukuran</label>
                            <input type="number" name="size" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="id_kat" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga Sepatu</label>
                            <input type="number" name="harga_sepatu" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="image_sepatu" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sepatu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Sepatu</label>
                            <input type="text" name="title" id="edit_title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ukuran</label>
                            <input type="number" name="size" id="edit_size" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="id_kat" id="edit_id_kat" class="form-control">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" id="edit_stok" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga Sepatu</label>
                            <input type="number" name="harga_sepatu" id="edit_harga_sepatu" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Baru (Opsional)</label>
                            <input type="file" name="image_sepatu" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Saat Ini</label><br>
                            <img id="preview_image" src="" width="120" class="border rounded" />
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editSepatu(item) {
        $('#editForm').attr('action', '/admin-sepatu/' + item.id);
        $('#edit_title').val(item.title);
        $('#edit_size').val(item.size);
        $('#edit_id_kat').val(item.id_kat);
        $('#edit_deskripsi').val(item.deskripsi);
        $('#edit_stok').val(item.stok);
        $('#edit_harga_sepatu').val(item.harga_sepatu);

        if (item.image_sepatu) {
        $('#preview_image').attr('src', '/storage/' + item.image_sepatu);
        } else {
            $('#preview_image').attr('src', 'https://via.placeholder.com/120x120?text=No+Image');
        }
        $('#editModal').modal('show');
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data sepatu ini akan dihapus!',
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
