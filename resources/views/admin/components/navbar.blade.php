
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{route('admin.home')}}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
        </li>
        {{-- dropdown --}}
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-database"></i>
            <p>
              Manajemen Data
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{route('admin.kategori')}}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Kategori Sepatu</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('admin.sepatu')}}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>List Sepatu</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <form action="{{route('logout.action')}}" method="POST">
              @csrf
              <button class="btn btn-danger btn-block" type="submit">Logout</button>
          </form>
        </li>
    </ul>
  </nav>