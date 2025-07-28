<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'This is admin panel')">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/favicon.png">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="{{ asset('backend/assets/libs/chartist/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/custom.css') }}" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    @stack('styles')
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper" data-navbarbg="skin6" data-theme="light" data-layout="vertical" data-sidebartype="full"
        data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <div class="navbar-header" data-logobg="skin5">
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                        <i class="ti-menu ti-close"></i>
                    </a>
                    <div class="navbar-brand">
                        <a href="{{ route('admin.dashboard') }}" class="logo d-flex align-items-center">
                            <b class="logo-icon text-white fs-4 fw-bold">
                                {{ auth('admin')->user()->website_name ?? 'Website' }}
                            </b>
                        </a>
                    </div>
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                        data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ti-more"></i>
                    </a>
                </div>
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin6">
                    <ul class="navbar-nav float-left mr-auto"></ul>
                    <ul class="navbar-nav float-right">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic"
                                href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ asset('backend/assets/images/users/1.jpg') }}" alt="user"
                                    class="rounded-circle" width="31">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="{{ route('admin.profile') }}"><i
                                        class="ti-user m-r-5 m-l-5"></i>
                                    My Profile</a>
                                <a class="dropdown-item" href="{{ route('admin.change-password') }}"><i
                                        class="fas fa-lock m-r-5 m-l-5"></i></i>
                                    Change Password</a>
                                <!--Logout admin -->
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('admin.logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="ti-power-off m-r-5 m-l-5"></i> Logout</a>
                                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <aside class="left-sidebar d-flex flex-column" data-sidebarbg="skin5">
            <div class="scroll-sidebar flex-grow-1" style="overflow-y: auto;">
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        @admincan('dashboard')
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="{{ route('admin.dashboard') }}" aria-expanded="false">
                                <i class="mdi mdi-av-timer"></i>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        @endadmincan

                        @admincan('admin_manager_list')
                        @if (Route::has('admin.admins.index'))
                        <li class="sidebar-item {{ Route::is('admin.admins.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.admins.*') ? 'active' : '' }}"
                                href="{{ route('admin.admins.index') }}" aria-expanded="false">
                                <i class="fas fa-users"></i>
                                <span class="hide-menu">Admin Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan


                        @admincan('roles_manager_list|permission_manager_list')
                        @if (Route::has('admin.roles.index') || Route::has('admin.permissions.index'))
                        @php
                        $activeRoutes = ['admin.roles.*', 'admin.permissions.*'];
                        @endphp
                        <li class="sidebar-item {{ Route::is($activeRoutes) ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow waves-effect waves-dark {{ Route::is($activeRoutes) ? 'active' : '' }}"
                                href="javascript:void(0)" aria-expanded="false">
                                <i class="mdi mdi-security"></i>
                                <span class="hide-menu">Role Permssion Manager</span>
                            </a>
                            <ul aria-expanded="false"
                                class="collapse first-level {{ Route::is($activeRoutes) ? 'in' : '' }}">
                                @admincan('roles_manager_list')
                                @if (Route::has('admin.roles.index'))
                                <li class="sidebar-item"
                                    {{ Route::is('admin.roles.*') ? 'selected' : '' }}>
                                    <a href="{{ route('admin.roles.index') }}"
                                        class="sidebar-link {{ Route::is('admin.roles.*') ? 'active' : '' }}">
                                        <i class="fas fa-circle"></i>
                                        <span class="hide-menu">Roles Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan

                                @admincan('permission_manager_list')
                                @if (Route::has('admin.permissions.index'))
                                <li
                                    class="sidebar-item {{ Route::is('admin.permissions.*') ? 'selected' : '' }}">
                                    <a href="{{ route('admin.permissions.index') }}"
                                        class="sidebar-link {{ Route::is('admin.permissions.*') ? 'active' : '' }}">
                                        <i class="fas fa-circle"></i>
                                        <span class="hide-menu">Permissions Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan

                            </ul>
                        </li>
                        @endif
                        @endadmincan

                        @admincan('users_manager_list')
                        @if (Route::has('admin.users.index'))
                        @php
                        $sidebarRoles = \DB::table('user_roles')
                        ->where('status', 1)
                        ->orderBy('name')
                        ->get();
                        @endphp
                        <li class="sidebar-item {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow waves-effect waves-dark {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'active' : '' }}"
                                href="javascript:void(0)">
                                <i class="fas fa-users"></i>
                                <span class="hide-menu">User Manager</span>
                            </a>
                            <ul aria-expanded="{{ Route::is('admin.users.*') ? 'true' : 'false' }}"
                                class="collapse first-level {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'in' : '' }}">
                                @admincan('user_role_list')
                                @if (Route::has('admin.user_roles.index'))
                                <li class="sidebar-item"
                                    {{ Route::is('admin.user_roles.*') ? 'selected' : '' }}>
                                    <a href="{{ route('admin.user_roles.index') }}"
                                        class="sidebar-link {{ Route::is('admin.user_roles.*') ? 'active' : '' }}">
                                        <i class="fas fa-user-tag"></i>
                                        <span class="hide-menu">User Roles Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan
                                @foreach ($sidebarRoles as $role)
                                <li
                                    class="sidebar-item {{ request('type') === $role->slug ? 'selected' : '' }}">
                                    <a href="{{ route('admin.users.index', ['type' => $role->slug]) }}"
                                        class="sidebar-link {{ request('type') === $role->slug ? 'active' : '' }}">
                                        <i class="fas fa-circle"></i>
                                        <span class="hide-menu">{{ $role->name }} Manager</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        @endadmincan

                        @admincan('categories_manager_list')
                        @if (Route::has('admin.categories.index'))
                        <li class="sidebar-item {{ Route::is('admin.categories.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.categories.*') ? 'active' : '' }}"
                                href="{{ route('admin.categories.index') }}" aria-expanded="false">
                                <i class="fas fa-th-large"></i>
                                <span class="hide-menu">Category Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- CMS Pages Manager --}}
                        @admincan('pages_manager_list')
                        @if (Route::has('admin.pages.index'))
                        <li class="sidebar-item {{ Route::is('admin.pages.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.pages.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.pages.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span class="hide-menu">CMS Pages Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Email Template Manager --}}
                        @admincan('emails_manager_list')
                        @if (Route::has('admin.emails.index'))
                        <li class="sidebar-item {{ Route::is('admin.emails.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.emails.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.emails.*') ? 'active' : '' }}">
                                <i class="fas fa-envelope"></i>
                                <span class="hide-menu">Email Template Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Faq Manager --}}
                        @admincan('faqs_manager_list')
                        @if (Route::has('admin.faqs.index'))
                        <li class="sidebar-item {{ Route::is('admin.faqs.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.faqs.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.faqs.*') ? 'active' : '' }}">
                                <i class="fas fa-question-circle"></i>
                                <span class="hide-menu">Faq Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Banner Manager --}}
                        @admincan('banners_manager_list')
                        @if (Route::has('admin.banners.index'))
                        <li class="sidebar-item {{ Route::is('admin.banners.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.banners.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.banners.*') ? 'active' : '' }}">
                                <i class="fas fa-image"></i>
                                <span class="hide-menu">Banner Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        @admincan('settings_manager_list')
                        @if (Route::has('admin.settings.index'))
                        <li class="sidebar-item {{ Route::is('admin.settings.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.settings.*') ? 'active' : '' }}"
                                href="{{ route('admin.settings.index') }}" aria-expanded="false">
                                <i class="fas fa-cog"></i>
                                <span class="hide-menu">Setting Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                    </ul>
                </nav>
            </div>
            @admincan('package_manager_list')
            <div class="sidebar-bottom-link p-3 mt-auto" style="position: sticky; bottom: 0; background: #222d32;">
                <a class="sidebar-link d-flex align-items-center" href="{{ route('admin.packages') }}">
                    <i class="fas fa-box mr-2"></i>
                    <span class="hide-menu">Package Manager</span>
                </a>
            </div>
            @endadmincan
        </aside>
        <div class="page-wrapper">
            <!-- Bread crumb and right sidebar toggle -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-5 align-self-center">
                        <h4 class="page-title">
                            @yield('page-title', 'Dashboard')
                        </h4>
                    </div>
                    <div class="col-7 align-self-center">
                        <div class="d-flex align-items-center justify-content-end">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                                        @if(Route::is('admin.dashboard'))
                                            Dashboard
                                        @else
                                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                        @endif
                                    </li>
                                    @yield('breadcrumb')
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Bread crumb and right sidebar toggle -->

            @yield('content')

            <!-- footer -->
            <footer class="footer text-center">
                All Rights Reserved by Dotsquares Designed and Developed by <a href="https://www.dotsquares.com"
                    target="_blank">Dotsquares</a>.
            </footer>
            <!-- End footer -->
        </div>
        <!-- End Page wrapper  -->
    </div>
    <!-- End Wrapper -->

    <!-- All Jquery -->
    <script src="{{ asset('backend/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('backend/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{ asset('backend/assets/extra-libs/sparkline/sparkline.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ asset('backend/dist/js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('backend/dist/js/sidebarmenu.js') }}"></script>
    <!--Custom JavaScript -->
    <script src="{{ asset('backend/dist/js/custom.min.js') }}"></script>
    <!--This page JavaScript -->
    <!--chartis chart-->
    <script src="{{ asset('backend/assets/libs/chartist/dist/chartist.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js') }}"></script>
    <script src="{{ asset('backend/dist/js/pages/dashboards/dashboard1.js') }}"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--custom script -->
    <script src="{{ asset('backend/custom.js') }}"></script>

    <script>
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif

        @if(session('info'))
        toastr.info("{{ session('info') }}");
        @endif

        @if(session('warning'))
        toastr.warning("{{ session('warning') }}");
        @endif
    </script>

    @stack('scripts')
</body>

</html>