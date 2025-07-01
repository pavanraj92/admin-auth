@extends('admin::admin.layouts.master')

@section('title', 'Package Manager')
@section('page-title', 'Package Manager')

@section('content')
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
                                        <button type="button"
                                            class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                            data-package="{{ $route }}" data-name="{{ $displayName }}"
                                            data-action="{{ $installed ? 'uninstall' : 'install' }}">
                                            {{ $installed ? 'Uninstall' : 'Install' }}
                                        </button>
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.install-uninstall-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                const action = this.dataset.action;
                const displayName = this.dataset.name;
                console.log(form.action)
                const url = form.action;
                const token = form.querySelector('input[name="_token"]').value;

                Swal.fire({
                    text: `Are you sure you want to ${action} this package?`,
                    //text: `${displayName}`,
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
                        button.disabled = true;
                        const originalText = button.innerHTML;
                        button.innerHTML =
                            `<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Processing...`;
                        // AJAX request
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
                                if (data.success || data.status === 'success') {
                                    button.innerHTML = `Completed`;
                                    Swal.fire({
                                        title: 'Success',
                                        text: data.message ||
                                            'Operation successful.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire('Error', data.message ||
                                        'Operation failed.', 'error');
                                    button.disabled = false;
                                    button.innerHTML = originalText;
                                }
                            })
                            .catch((error) => {
                                console.error('Fetch error:', error);
                                Swal.fire('Error', 'Something went wrong.',
                                    'error');
                                button.disabled = false;
                                button.innerHTML = originalText;
                            });
                    }
                });
            });
        });
    });
</script>
