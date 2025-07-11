@extends('admin::admin.layouts.master')

@section('title', 'Package Manager')
@section('page-title', 'Package Manager')
@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Manage Packages</li>
@endsection

@section('content')
<div id="package-progress-bar-container" style="height: 4px; width: 100%; background: #eee; position: fixed; top: 0; left: 0; z-index: 9999; display: none;">
    <div id="package-progress-bar" style="height: 100%; width: 0; background: #4caf50; transition: width 0.5s;"></div>
</div>
<div class="container-fluid">
    <div class="row">
        @foreach ($packages as $route => $displayName)
        @php
        $info = config('constants.package_info.' . $route);
        [$vendor, $package] = explode('/', $route);
        $installed = is_dir(base_path("vendor/$vendor/$package"));
        @endphp
        <div class="col-md-3 mb-4">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3" style="min-height: 110px;">
                            <h5 class="card-title font-weight-bold">
                                {{ $displayName }}
                                <span
                                    class="badge badge-pill badge-{{ $installed ? 'success' : 'danger' }} float-right p-1">
                                    {{ $installed ? 'Installed' : 'Not Installed' }}
                                </span>
                            </h5>
                            <p class="card-text"
                                style="max-height: 100px; overflow-y: auto; text-overflow: ellipsis;">
                                {{ isset($info['description']) && $info['description'] ? $info['description'] : 'No description available.' }}
                            </p>
                        </div>
                        <div class="col-md-12 text-right">
                            <form method="POST"
                                action="{{ route('admin.packages.toggle', ['vendor' => $vendor, 'package' => $package]) }}">
                                @csrf
                                @php
                                $displayName = config('constants.package_display_names.' . $route, $route);
                                @endphp
                                @if ($route === 'admin/settings')
                                @if (!$installed)
                                <button type="button"
                                    class="btn btn-outline-success install-uninstall-btn"
                                    data-package="{{ $route }}" data-name="{{ $displayName }}"
                                    data-action="install">
                                    Install
                                </button>
                                @else
                                {{-- Do nothing OR show an invisible placeholder if needed --}}
                                <div style="visibility: hidden;">
                                    <button type="button" class="btn btn-outline-secondary">Placeholder</button>
                                </div>
                                @endif
                                @else
                                <button type="button"
                                    class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                    data-package="{{ $route }}" data-name="{{ $displayName }}"
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
</div>

@endsection

<script>
    let progressBarTimeout = null;

    function startPackageProgressBar() {
        $('#package-progress-bar-container').show();
        let bar = $('#package-progress-bar');
        bar.stop(true, true).css('width', '0%');

        // Animate to 90% over 1.5 seconds (or whatever feels smooth)
        bar.css({
            transition: 'width 22s linear',
            width: '90%'
        })
    }

    function finishPackageProgressBar() {
        let bar = $('#package-progress-bar');

        // Compute real width on screen now
        const currentWidth = bar[0].getBoundingClientRect().width / bar.parent()[0].getBoundingClientRect().width * 100;

        // Immediately set current width without transition
        bar.css({
            transition: 'none',
            width: `${currentWidth}%`
        });

        // Force a reflow to apply width instantly
        bar[0].offsetHeight;

        // Then transition to 100% quickly
        bar.css({
            transition: 'width 0.3s linear',
            width: '100%'
        });

        setTimeout(() => {
            $('#package-progress-bar-container').fadeOut(300, function () {
                bar.css({
                    transition: 'none',
                    width: '0%'
                });
            });
        }, 600);
    }


    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.install-uninstall-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
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
                        // Disable all buttons
                        allButtons.forEach(btn => btn.disabled = true);
                        const originalText = this.innerHTML;
                        this.innerHTML = `<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Processing...`;
                        startPackageProgressBar(60000);
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
                                finishPackageProgressBar();
                                if (data.success || data.status === 'success') {
                                    this.innerHTML = `Completed`;
                                    Swal.fire({
                                        title: 'Success',
                                        text: data.message || 'Operation successful.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire('Error', data.message || 'Operation failed.', 'error');
                                    allButtons.forEach(btn => btn.disabled = false);
                                    this.innerHTML = originalText;
                                }
                            })
                            .catch((error) => {
                                finishPackageProgressBar();
                                console.error('Fetch error:', error);
                                Swal.fire('Error', 'Something went wrong.', 'error');
                                allButtons.forEach(btn => btn.disabled = false);
                                this.innerHTML = originalText;
                            });
                    }
                });
            });
        });
    });
</script>