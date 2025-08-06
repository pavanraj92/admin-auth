@extends('admin::admin.layouts.master')

@section('title', 'Package Manager')
@section('page-title', 'Package Manager')
@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Package Manager</li>
@endsection

@section('content')
@php
    // Use config list if available, otherwise fallback to these three packages
    $protected = config('constants.protected_packages', [
        'admin/settings',
        'admin/admin_auth',
        'admin/product_orders',
    ]);
@endphp

<div id="page-overlay"
     style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 99999;  background:rgba(0,0,0,0.3); cursor: not-allowed;">
</div>
<div id="package-progress-bar-container"
     style="height: 4px; width: 100%; background: #eee; z-index: 9999; display: none; margin-bottom: 0; position: relative; top: -47px;">
    <div id="package-progress-bar" style="height: 100%; width: 0; background: #4caf50; transition: width 0.5s;"></div>
    <span id="package-progress-percent" style="position:absolute; right:10px; top:0; color:#333; font-weight:bold; display:none;">1%</span>
</div>
<div class="container-fluid">
    <!-- Common Packages Section -->
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="text-primary mb-3" style="font-size: 1.5rem; font-weight: 600;">
                <i class="fas fa-cogs me-3"></i> Common Packages
            </h4>
        </div>
        @foreach ($commonPackages as $package)
        @php
        [$vendor, $packageName] = explode('/', $package->package_name);
        $installed = $package->is_installed;
        @endphp
        <div class="col-md-3 mb-2">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3" style="min-height: 110px;">
                            <h5 class="card-title font-weight-bold">
                                {{ $package->display_name }}
                                <span
                                    class="badge badge-pill badge-{{ $installed ? 'success' : 'danger' }} float-right p-1">
                                    {{ $installed ? 'Installed' : 'Not Installed' }}
                                </span>
                            </h5>
                            <p class="card-text"
                                style="max-height: 100px; overflow-y: auto; text-overflow: ellipsis;">
                                {{ $package->description ?: 'No description available.' }}
                            </p>
                        </div>
                        <div class="col-md-12 text-right">
                            <form method="POST"
                                action="{{ route('admin.packages.toggle', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                @csrf
                                @if (in_array($package->package_name, $protected))
                                    {{-- Hide button for protected packages (show install only when not installed) --}}
                                    @if (! $installed)
                                        <button type="button"
                                            class="btn btn-outline-success install-uninstall-btn"
                                            data-package="{{ $package->package_name }}" data-name="{{ $package->display_name }}"
                                            data-action="install">
                                            Install
                                        </button>
                                    @else
                                        <div style="visibility: hidden;">
                                            <button type="button" class="btn btn-outline-secondary">Placeholder</button>
                                        </div>
                                    @endif
                                @else
                                    <button type="button"
                                        class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                        data-package="{{ $package->package_name }}" data-name="{{ $package->display_name }}"
                                        data-action="{{ $installed ? 'uninstall' : 'install' }}">
                                        {{ $installed ? 'Uninstall' : 'Install' }}
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Industry-Specific Packages Section -->
    @if($industryPackages->count() > 0)
    <div class="row">
        <div class="col-12">
            <h4 class="text-success mb-3" style="font-size: 1.5rem; font-weight: 600;">
                <i class="{{ config('constants.industry_icons.' . $industry, 'fas fa-industry') }} me-3"></i> {{ config('constants.industryAryList.' . $industry, $industry) }} Packages
            </h4>
            <p class="text-muted mb-3">
                The {{ config('constants.industryAryList.' . $industry, $industry) }} package provides essential features for building {{ strtolower(config('constants.industryAryList.' . $industry, $industry)) }} applications.
            </p>
        </div>
        @foreach ($industryPackages as $package)
        @php
        [$vendor, $packageName] = explode('/', $package->package_name);
        $installed = $package->is_installed;
        @endphp
        <div class="col-md-3 mb-2">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3" style="min-height: 110px;">
                            <h5 class="card-title font-weight-bold">
                                {{ $package->display_name }}
                                <span
                                    class="badge badge-pill badge-{{ $installed ? 'success' : 'danger' }} float-right p-1">
                                    {{ $installed ? 'Installed' : 'Not Installed' }}
                                </span>
                            </h5>
                            <p class="card-text"
                                style="max-height: 100px; overflow-y: auto; text-overflow: ellipsis;">
                                {{ $package->description ?: 'No description available.' }}
                            </p>
                        </div>
                        <div class="col-md-12 text-right">
                            <form method="POST"
                                action="{{ route('admin.packages.toggle', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                @csrf
                                @if (in_array($package->package_name, $protected))
                                    {{-- Hide button for protected packages (show install only when not installed) --}}
                                    @if (! $installed)
                                        <button type="button"
                                            class="btn btn-outline-success install-uninstall-btn"
                                            data-package="{{ $package->package_name }}" data-name="{{ $package->display_name }}"
                                            data-action="install">
                                            Install
                                        </button>
                                    @else
                                        <div style="visibility: hidden;">
                                            <button type="button" class="btn btn-outline-secondary">Placeholder</button>
                                        </div>
                                    @endif
                                @else
                                    <button type="button"
                                        class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                        data-package="{{ $package->package_name }}" data-name="{{ $package->display_name }}"
                                        data-action="{{ $installed ? 'uninstall' : 'install' }}">
                                        {{ $installed ? 'Uninstall' : 'Install' }}
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
@push('scripts')
<script>
    let progressBarTimeout = null;

    function startPackageProgressBar(durationMs) {
        $('#package-progress-bar-container').show();
        let bar = $('#package-progress-bar');
        bar.stop(true, true).css('width', '0%');

        bar.css({
            transition: `width ${durationMs / 1000}s linear`,
            width: '90%'
        });
    }

    function finishPackageProgressBar(fromPercent) {
        let bar = $('#package-progress-bar');

        // Start from current width and finish to 100%
        bar.css({
            transition: 'none',
            width: `${fromPercent}%`
        });

        // Force reflow
        bar[0].offsetHeight;

        // Then animate to 100%
        bar.css({
            transition: 'width 0.5s linear',
            width: '100%'
        });

        setTimeout(() => {
            $('#package-progress-bar-container').fadeOut(300, function () {
                bar.css({
                    transition: 'none',
                    width: '0%'
                });
            });
        }, 700);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.install-uninstall-btn').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const allButtons = document.querySelectorAll('.install-uninstall-btn');
                const form = this.closest('form');
                const action = this.dataset.action;
                const displayName = this.dataset.name;
                const url = form.action;
                const token = form.querySelector('input[name="_token"]').value;

                Swal.fire({
                    text: `Are you sure you want to ${action} ${displayName} package?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Yes, ${action} it!`,
                    cancelButtonText: 'No, cancel!',
                    customClass: {
                        confirmButton: 'btn btn-outline-success',
                        cancelButton: 'btn btn-outline-danger',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        allButtons.forEach(btn => btn.disabled = true);
                        document.getElementById('page-overlay').style.display = 'block';
                        const originalText = this.innerHTML;
                        this.innerHTML = `<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Processing... <span class="processing-percent">1%</span>`;

                        let percent = 1;
                        const targetPercent = 90;

                        const progressDuration = (action === 'install') ? 40000 : 25000; // in ms
                        const intervalTime = progressDuration / (targetPercent - percent);

                        const btn = this;

                        // First interval: 1 → 90
                        const firstInterval = setInterval(() => {
                            if (percent < targetPercent) {
                                percent++;
                                btn.querySelector('.processing-percent').textContent = percent + '%';
                            } else {
                                clearInterval(firstInterval);
                            }
                        }, intervalTime);

                        startPackageProgressBar(progressDuration);

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({})
                        })
                        .then(response => response.json())
                        .then(data => {
                            clearInterval(firstInterval);

                            // Second interval: from current → 100
                            const secondInterval = setInterval(() => {
                                if (percent < 100) {
                                    percent++;
                                    btn.querySelector('.processing-percent').textContent = percent + '%';
                                } else {
                                    clearInterval(secondInterval);
                                }
                            }, 30); // fast

                            finishPackageProgressBar(percent);

                            if (data.success || data.status === 'success') {
                                setTimeout(() => {
                                    document.getElementById('page-overlay').style.display = 'none';
                                    btn.innerHTML = `Completed`;
                                    Swal.fire({
                                        title: 'Success',
                                        text: data.message || 'Operation successful.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => window.location.reload());
                                }, 700);
                            } else {
                                setTimeout(() => {
                                    document.getElementById('page-overlay').style.display = 'none';
                                    Swal.fire('Error', data.message || 'Operation failed.', 'error');
                                    allButtons.forEach(btn => btn.disabled = false);
                                    btn.innerHTML = originalText;
                                }, 700);
                            }
                        })
                        .catch((error) => {
                            clearInterval(firstInterval);

                            // Fail case: also finish to 100 for UI consistency
                            const secondInterval = setInterval(() => {
                                if (percent < 100) {
                                    percent++;
                                    this.querySelector('.processing-percent').textContent = percent + '%';
                                } else {
                                    clearInterval(secondInterval);
                                }
                            }, 30);

                            finishPackageProgressBar(percent);

                            console.error('Fetch error:', error);
                            setTimeout(() => {
                                document.getElementById('page-overlay').style.display = 'none';
                                Swal.fire('Error', 'Something went wrong.', 'error');
                                allButtons.forEach(btn => btn.disabled = false);
                                this.innerHTML = originalText;
                            }, 700);
                        });
                    }
                });
            });
        });
    });

</script>
@endpush
