@extends('superadmin.layout.layout')

@section('title', 'Manajemen User')

@section('content')
<div class="container">
    <h1 class="mb-4">Manajemen User</h1>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah User</button>

    <div class="card">
        <div class="card-header bg-dark text-white">Data User</div>
        <div class="card-body">
            <!-- Table for Admins -->
            <h3 class="mb-3">Admin</h3>
            @php
                $admins = $data->where('role_type', 1);
            @endphp
            @if ($admins->count() > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No HP</th>
                                <th>Gender</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admins as $user)
                                <tr>
                                    <td>{{ $user->nama_depan }} {{ $user->nama_belakang }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->no_hp ?? "Tidak Ada Data" }}</td>
                                    <td>{{ $user->gender }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="editUser({{ $user }})">Edit</button>
                                        <form action="{{ route('dashboard.super.users.destroy', $user->id) }}" method="POST" style="display:inline;" id="deleteForm_{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $user->id }})">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-4">Tidak ada data Admin.</div>
            @endif

            <!-- Table for Users -->
            <h3 class="mb-3">User</h3>
            @php
                $users = $data->where('role_type', 0);
            @endphp
            @if ($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No HP</th>
                                <th>Gender</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->nama_depan }} {{ $user->nama_belakang }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->no_hp }}</td>
                                    <td>{{ $user->gender }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="editUser({{ $user }})">Edit</button>
                                        <form action="{{ route('dashboard.super.users.destroy', $user->id) }}" method="POST" style="display:inline;" id="deleteForm_{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $user->id }})">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Tidak ada data User.</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('dashboard.super.users.store') }}" method="POST">
          @csrf
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Tambah User</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span>×</span>
                  </button>
              </div>
              <div class="modal-body">
                  <div class="form-group">
                      <label>Nama Depan</label>
                      <input type="text" name="nama_depan" required class="form-control">
                  </div>
                  <div class="form-group">
                      <label>Nama Belakang</label>
                      <input type="text" name="nama_belakang" required class="form-control">
                  </div>
                  <div class="form-group">
                      <label>Email</label>
                      <input type="email" name="email" required class="form-control">
                  </div>
                  <div class="form-group">
                      <label>No HP</label>
                      <input type="text" name="no_hp" class="form-control">
                  </div>
                  <div class="form-group">
                      <label>Gender</label>
                      <select name="gender" class="form-control" required>
                          <option value="Laki">Laki</option>
                          <option value="P">Perempuan</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Role</label>
                      <select name="role_type" class="form-control" required>
                          <option value="0">User</option>
                          <option value="1">Admin</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Password</label>
                      <input type="password" name="password" required class="form-control">
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Simpan</button>
              </div>
          </div>
      </form>
    </div>
  </div>
  
{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="editForm" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label>Nama Depan</label>
                    <input type="text" id="edit_nama_depan" name="nama_depan" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama Belakang</label>
                    <input type="text" id="edit_nama_belakang" name="nama_belakang" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" id="edit_no_hp" name="no_hp" class="form-control">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select id="edit_gender" name="gender" class="form-control" required>
                        <option value="Laki">Laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="edit_role_type" name="role_type" class="form-control" required>
                        <option value="0">User</option>
                        <option value="1">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>
    </form>
  </div>
</div>

<script>
function editUser(user) {
    $('#editForm').attr('action', '/super-dashboard-users/update/' + user.id);
    $('#edit_id').val(user.id);
    $('#edit_nama_depan').val(user.nama_depan);
    $('#edit_nama_belakang').val(user.nama_belakang);
    $('#edit_email').val(user.email);
    $('#edit_no_hp').val(user.no_hp);
    $('#edit_gender').val(user.gender);
    $('#edit_role_type').val(user.role_type);
    $('#editModal').modal('show');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Data user tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm_' + id).submit();
        }
    });
}
</script>
@endsection