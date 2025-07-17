<nav class="mt-2">
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
      <li class="nav-item">
          <a href="{{ route('dashboard.super') }}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
          </a>
      </li>

      <!-- Dropdown: Data Super Admin -->
      <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
              <i class="nav-icon fas fa-database"></i>
              <p>
                  Data Super Admin
                  <i class="right fas fa-angle-left"></i>
              </p>
          </a>
          <ul class="nav nav-treeview">
              <li class="nav-item">
                  <a href="{{route('dashboard.super.users')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>User</p>
                  </a>
              </li>
          </ul>
      </li>

      <li class="nav-item mt-3">
          <form action="{{ route('logout.super') }}" method="POST" class="d-flex justify-content-center">
              @csrf
              <button class="btn btn-danger btn-block" type="submit">
                  <i class="fas fa-sign-out-alt mr-1"></i> Logout
              </button>
          </form>
      </li>

  </ul>
</nav>
