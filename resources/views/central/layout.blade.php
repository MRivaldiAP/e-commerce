<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Central Administration</title>
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/vendors/mdi/css/materialdesignicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/vendors/css/vendor.bundle.base.css') }}" />
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/vendors/font-awesome/css/font-awesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('AdminPage/assets/css/style.css') }}" />
    <link rel="shortcut icon" href="{{ asset('AdminPage/assets/images/favicon.png') }}" />
  </head>
  <body>
    <div class="container-scroller">
      <nav class="sidebar sidebar-offcanvas" id="sidebar" style="overflow-y: auto;">
        <div class="text-center sidebar-brand-wrapper d-flex align-items-center">
          <a class="sidebar-brand brand-logo" href="{{ route('central.admin.tenants.index') }}"><img src="{{ asset('AdminPage/assets/images/logo.svg') }}" alt="logo" /></a>
          <a class="sidebar-brand brand-logo-mini pl-4 pt-3" href="{{ route('central.admin.tenants.index') }}"><img src="{{ asset('AdminPage/assets/images/logo-mini.svg') }}" alt="logo" /></a>
        </div>
        <ul class="nav">
          <li class="nav-item nav-category">Central Menu</li>
          <li class="nav-item {{ request()->routeIs('central.admin.tenants.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('central.admin.tenants.index') }}">
              <i class="mdi mdi-domain menu-icon"></i>
              <span class="menu-title">Tenants</span>
            </a>
          </li>
        </ul>
      </nav>
      <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
          <div class="content-wrapper">
            @if(session('status'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            @yield('content')
          </div>
        </div>
      </div>
    </div>

    <script src="{{ asset('AdminPage/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('AdminPage/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('AdminPage/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('AdminPage/assets/js/misc.js') }}"></script>
  </body>
</html>
