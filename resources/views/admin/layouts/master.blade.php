<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'This is admin panel')">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (config('GET.main_favicon'))
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('storage/' . config('GET.main_favicon')) }}">
    @else
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/favicon.png">
    @endif
    <title>@yield('title', 'Admin Panel')</title>
    <link href="{{ asset('backend/assets/libs/chartist/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/custom.css') }}" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                                <img src="{{ asset('storage/' . config('GET.main_logo')) }}" alt="Logo"
                                    height="30">
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
                                <li class="sidebar-item" {{ Route::is('admin.roles.*') ? 'selected' : '' }}>
                                    <a href="{{ route('admin.roles.index') }}"
                                        class="sidebar-link {{ Route::is('admin.roles.*') ? 'active' : '' }}">
                                        <i class="fas fa-circle"></i>
                                        <span class="hide-menu">Role Manager</span>
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
                                        <span class="hide-menu">Permission Manager</span>
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
                        ->whereNull('deleted_at')
                        ->get();
                        @endphp
                        <li
                            class="sidebar-item {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow waves-effect waves-dark {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'active' : '' }}"
                                href="javascript:void(0)">
                                <i class="fas fa-users"></i>
                                <span class="hide-menu">User Manager</span>
                            </a>
                            <ul aria-expanded="{{ Route::is('admin.users.*') ? 'true' : 'false' }}"
                                class="collapse first-level {{ Route::is('admin.users.*', 'admin.user_roles.*') ? 'in' : '' }}">
                                @admincan('user_roles_manager_list')
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

                        {{-- Tag Manager --}}
                        @admincan('tags_manager_list')
                        @if (Route::has('admin.tags.index'))
                        <li class="sidebar-item {{ Route::is('admin.tags.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.tags.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.tags.*') ? 'active' : '' }}">
                                <i class="fas fa-tags"></i>
                                <span class="hide-menu">Tag Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Course Manager --}}
                        @admincan('courses_manager_list')
                        @if (Route::has('admin.courses.index'))
                        <li class="sidebar-item {{ Route::is('admin.courses.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.courses.*') ? 'active' : '' }}"
                                href="{{ route('admin.courses.index') }}" aria-expanded="false">
                                <i class="fas fa-book"></i>
                                <span class="hide-menu">Course Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Lecture Manager --}}
                        @admincan('lectures_manager_list')
                        @if (Route::has('admin.lectures.index'))
                        <li class="sidebar-item {{ Route::is('admin.lectures.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.lectures.*') ? 'active' : '' }}"
                                href="{{ route('admin.lectures.index') }}" aria-expanded="false">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span class="hide-menu">Lecture Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Brand Manager --}}
                        @admincan('brands_manager_list')
                        @if (Route::has('admin.brands.index'))
                        <li class="sidebar-item {{ Route::is('admin.brands.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.brands.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.brands.*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="hide-menu">Brand Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Product Manager --}}
                        @admincan('products_manager_list')
                        @if (Route::has('admin.products.index'))
                        <li class="sidebar-item {{ Route::is('admin.products.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.products.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.products.*') ? 'active' : '' }}">
                                <i class="fas fa-box-open"></i>
                                <span class="hide-menu">Product Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Product Manager --}}
                        @admincan('product_orders_manager_list')
                        @if (Route::has('admin.orders.index'))
                        <li class="sidebar-item {{ Route::is('admin.orders.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.orders.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.orders.*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="hide-menu">Order Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Certificate Manager --}}
                        @admincan('certificates_manager_list')
                        @if (Route::has('admin.certificates.index'))
                        <li class="sidebar-item {{ Route::is('admin.certificates.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.certificates.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.certificates.*') ? 'active' : '' }}">
                                <i class="fas fa-certificate"></i>
                                <span class="hide-menu">Certificate Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Shipping Charges Manager --}}
                        @admincan('shipping_charges_manager')
                        @if (Route::has('admin.shipping_rates.index') || Route::has('admin.shipping_methods.index'))
                        <li
                            class="sidebar-item {{ Route::is('admin.shipping_methods.*') || Route::is('admin.shipping_rates.*') ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow waves-effect waves-dark {{ Route::is('admin.shipping_methods.*') || Route::is('admin.shipping_rates.*') ? 'active' : '' }}"
                                href="javascript:void(0)">
                                <i class="fas fa-truck"></i>
                                <span class="hide-menu">Shipping Charges Manager</span>
                            </a>
                            <ul aria-expanded="{{ Route::is('admin.shipping_methods.*') || Route::is('admin.shipping_rates.*') ? 'true' : 'false' }}"
                                class="collapse first-level {{ Route::is('admin.shipping_methods.*') || Route::is('admin.shipping_rates.*') ? 'in' : '' }}">

                                @admincan('shipping_methods_manager_list')
                                @if (Route::has('admin.shipping_methods.index'))
                                <li
                                    class="sidebar-item {{ Route::is('admin.shipping_methods.*') ? 'selected' : '' }}">
                                    <a href="{{ route('admin.shipping_methods.index') }}"
                                        class="sidebar-link {{ Route::is('admin.shipping_methods.*') ? 'active' : '' }}">
                                        <i class="fas fa-box-open"></i>
                                        <span class="hide-menu">Shipping Methods Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan

                                @admincan('shipping_rates_manager_view')
                                @if (Route::has('admin.shipping_rates.index'))
                                <li
                                    class="sidebar-item {{ Route::is('admin.shipping_rates.*') ? 'selected' : '' }}">
                                    <a href="{{ route('admin.shipping_rates.index') }}"
                                        class="sidebar-link {{ Route::is('admin.shipping_rates.*') ? 'active' : '' }}">
                                        <i class="fas fa-box-open"></i>
                                        <span class="hide-menu">Shipping Rates Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan
                            </ul>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Product Coupons --}}
                        @admincan('product_coupons_manager_list')
                        @if (Route::has('admin.coupons.index'))
                        <li class="sidebar-item {{ Route::is('admin.coupons.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.coupons.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.coupons.*') ? 'active' : '' }}">
                                <i class="fas fa-tags"></i>
                                <span class="hide-menu">Coupon Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Quiz Manager --}}
                        @admincan('quizzes_manager_list')
                        @if (Route::has('admin.quizzes.index'))
                        <li class="sidebar-item {{ Route::is('admin.quizzes.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.quizzes.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.quizzes.*') ? 'active' : '' }}">
                                <i class="fas fa-question-circle"></i>
                                <span class="hide-menu">Quiz Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Transaction Manager --}}
                        @admincan('transactions_manager_list')
                        @if (Route::has('admin.transactions.index'))
                        <li class="sidebar-item {{ Route::is('admin.transactions.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.transactions.*') ? 'active' : '' }}"
                                href="{{ route('admin.transactions.index') }}" aria-expanded="false">
                                <i class="fas fa-exchange-alt"></i>
                                <span class="hide-menu">Transaction Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Course Purchases Manager --}}
                        @admincan('course_purchases_manager_list')
                        @if (Route::has('admin.course-purchases.index'))
                        <li class="sidebar-item {{ Route::is('admin.course-purchases.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.course-purchases.*') ? 'active' : '' }}"
                                href="{{ route('admin.course-purchases.index') }}" aria-expanded="false">
                                <i class="fas fa-graduation-cap"></i>
                                <span class="hide-menu">Purchase Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Commissions Manager --}}
                        @admincan('commissions_manager_list')
                        @if (Route::has('admin.commissions.index'))
                        <li class="sidebar-item {{ Route::is('admin.commissions.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.commissions.*') ? 'active' : '' }}"
                                href="{{ route('admin.commissions.index') }}" aria-expanded="false">
                                <i class="fas fa-percent"></i>
                                <span class="hide-menu">Commission Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Rating Manager --}}
                        @admincan('ratings_manager_list')
                        @if (Route::has('admin.ratings.index'))
                        <li class="sidebar-item {{ Route::is('admin.ratings.*') ? 'selected' : '' }}">
                            <a href="{{ route('admin.ratings.index') }}"
                                class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.ratings.*') ? 'active' : '' }}">
                                <i class="fas fa-star"></i>
                                <span class="hide-menu">Rating Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Wishlist Manager --}}
                        @admincan('wishlists_manager_list')
                        @if (Route::has('admin.wishlists.index'))
                        <li class="sidebar-item {{ Route::is('admin.wishlists.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.wishlists.*') ? 'active' : '' }}"
                                href="{{ route('admin.wishlists.index') }}" aria-expanded="false">
                                <i class="fas fa-heart"></i>
                                <span class="hide-menu">Wishlist Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Return Refund Manager --}}
                        @admincan('return_refunds_manager_list')
                        @if (Route::has('admin.return_refunds.index'))
                        <li class="sidebar-item {{ Route::is('admin.return_refunds.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.return_refunds.*') ? 'active' : '' }}"
                                href="{{ route('admin.return_refunds.index') }}" aria-expanded="false">
                                <i class="fas fa-money-check-dollar"></i>
                                <span class="hide-menu">Return Refund Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Report Manager --}}
                        @admincan('reports_manager_list')
                        @if (Route::has('admin.reports.index'))
                        <li class="sidebar-item {{ Route::is('admin.reports.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link {{ Route::is('admin.reports.*') ? 'active' : '' }}"
                                href="{{ route('admin.reports.index') }}" aria-expanded="false">
                                <i class="fas fa-chart-line"></i>
                                <span class="hide-menu">Report Manager</span>
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

                        @admincan('enquiry_manager_list')
                        {{-- Enquiry Manager --}}
                        @if (Route::has('admin.enquiries.index'))
                        <li class="sidebar-item {{ Route::is('admin.enquiries.*') ? 'selected' : '' }}">
                            <a class="sidebar-link waves-effect waves-dark {{ Route::is('admin.enquiries.*') ? 'active' : '' }}"
                                href="{{ route('admin.enquiries.index') }}" aria-expanded="false">
                                <i class="fas fa-phone"></i>
                                <span class="hide-menu">Enquiry Manager</span>
                            </a>
                        </li>
                        @endif
                        @endadmincan

                        {{-- Setting Manager --}}
                        @admincan('settings_manager')
                        <li class="sidebar-item {{ Route::is('admin.settings.*') ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow waves-effect waves-dark {{ Route::is('admin.settings.*') ? 'active' : '' }}"
                                href="javascript:void(0)">
                                <i class="fas fa-cog"></i>
                                <span class="hide-menu">Setting Manager</span>
                            </a>
                            <ul aria-expanded="{{ Route::is('admin.settings.*') ? 'true' : 'false' }}"
                                class="collapse first-level {{ Route::is('admin.settings.*') ? 'in' : '' }}">
                                @admincan('settings_manager_list')
                                @if (Route::has('admin.settings.index'))
                                <li class="sidebar-item"
                                    {{ Route::is('admin.settings.*') && !Route::is('admin.settings.getlogos') ? 'selected' : '' }}>
                                    <a href="{{ route('admin.settings.index') }}"
                                        class="sidebar-link {{ Route::is('admin.settings.*') && !Route::is('admin.settings.getlogos') ? 'active' : '' }}">
                                        <i class="fas fa-user-tag"></i>
                                        <span class="hide-menu">General Settings Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan
                                @admincan('logo_favicon_manager_view')
                                @if (Route::has('admin.settings.getlogos'))
                                <li class="sidebar-item"
                                    {{ Route::is('admin.settings.getlogos') ? 'selected' : '' }}>
                                    <a href="{{ route('admin.settings.getlogos') }}"
                                        class="sidebar-link {{ Route::is('admin.settings.getlogos') ? 'active' : '' }}">
                                        <i class="fas fa-user-tag"></i>
                                        <span class="hide-menu">Logo/Favicon Manager</span>
                                    </a>
                                </li>
                                @endif
                                @endadmincan
                            </ul>
                        </li>
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
                                        @if (Route::is('admin.dashboard'))
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
    <script src="{{ asset('backend/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js') }}">
    </script>
    <script src="{{ asset('backend/dist/js/pages/dashboards/dashboard1.js') }}"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--custom script -->
    <script src="{{ asset('backend/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
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