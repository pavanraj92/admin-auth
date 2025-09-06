@extends('admin::admin.layouts.master')

@section('title', 'Package Manager')
@section('page-title', 'Package Manager')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Package Manager</li>
@endsection

@section('content')
    @php
        // Use config list if available, otherwise fallback to these three packages
        $protected = config('constants.protected_packages', ['admin/settings', 'admin/admin_auth']);
        $restrictedForProduct = ['categories', 'products', 'users', 'brands'];
        $restrictedForCourse = ['categories', 'courses', 'users'];
    @endphp

    <div id="page-overlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 99999; background: rgba(0,0,0,0.7); cursor: not-allowed;">

        <!-- Full Page Loading Content -->
        <div id="full-page-loader"
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
            <!-- Gradient Circular Loader -->
            <div id="gradient-loader" style="width: 80px; height: 80px; margin: 0 auto 20px; position: relative;">
                <svg width="80" height="80" viewBox="0 0 80 80" style="transform: rotate(-90deg);">
                    <circle cx="40" cy="40" r="35" stroke="rgba(255,255,255,0.2)" stroke-width="6"
                        fill="none" />
                    <circle id="progress-circle" cx="40" cy="40" r="35" stroke="url(#gradient)"
                        stroke-width="6" fill="none" stroke-linecap="round" stroke-dasharray="220"
                        stroke-dashoffset="220" style="transition: stroke-dashoffset 0.3s ease;">
                        <animateTransform attributeName="transform" type="rotate" values="0 40 40;360 40 40" dur="2s"
                            repeatCount="indefinite" />
                    </circle>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#5b73ff;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Loading Text and Percentage -->
            <div id="loading-text" style="font-size: 18px; font-weight: 500; margin-bottom: 10px;">Installing Package...
            </div>
            <div id="package-counter" style="font-size: 14px; color: #888; margin-bottom: 5px;">Package 1 of 1</div>
            <div id="loading-percentage" style="font-size: 24px; font-weight: bold; color: #00d4ff;">1%</div>
        </div>
    </div>
    <div id="package-progress-bar-container"
        style="height: 4px; width: 100%; background: #eee; z-index: 9999; display: none; margin-bottom: 0; position: relative; top: -47px;">
        <div id="package-progress-bar" style="height: 100%; width: 0; background: #4caf50; transition: width 0.5s;"></div>
        <span id="package-progress-percent"
            style="position:absolute; right:10px; top:0; color:#333; font-weight:bold; display:none;">1%</span>
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
                                            @if (!$installed)
                                                <button type="button" class="btn btn-outline-success install-uninstall-btn"
                                                    data-package="{{ $package->package_name }}"
                                                    data-name="{{ $package->display_name }}" data-action="install"
                                                    data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                                    Install
                                                </button>
                                            @else
                                                <div style="visibility: hidden;">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary">Placeholder</button>
                                                </div>
                                            @endif
                                        @elseif (
                                            ($industry === 'ecommerce' && in_array($packageName, $restrictedForProduct)) ||
                                                ($industry === 'education' && in_array($packageName, $restrictedForCourse)))
                                            {{-- Restricted package: allow install, block uninstall --}}
                                            @if (!$installed)
                                                <button type="button" class="btn btn-outline-success install-uninstall-btn"
                                                    data-package="{{ $package->package_name }}"
                                                    data-name="{{ $package->display_name }}" data-action="install"
                                                    data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                                    Install
                                                </button>
                                            @else
                                                {{-- Installed → do not show uninstall --}}
                                                <div style="visibility: hidden;">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary">Placeholder</button>
                                                </div>
                                            @endif
                                        @else
                                            <button type="button"
                                                class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                                data-package="{{ $package->package_name }}"
                                                data-name="{{ $package->display_name }}"
                                                data-action="{{ $installed ? 'uninstall' : 'install' }}"
                                                data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
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
        @if ($industryPackages->count() > 0)
            <div class="row">
                <div class="col-12">
                    <h4 class="text-success mb-3" style="font-size: 1.5rem; font-weight: 600;">
                        <i class="{{ config('constants.industry_icons.' . $industry, 'fas fa-industry') }} me-3"></i>
                        {{ config('constants.industryAryList.' . $industry, $industry) }} Packages
                    </h4>
                    <p class="text-muted mb-3">
                        The {{ config('constants.industryAryList.' . $industry, $industry) }} package provides essential
                        features for building {{ strtolower(config('constants.industryAryList.' . $industry, $industry)) }}
                        applications.
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
                                                @if (!$installed)
                                                    <button type="button"
                                                        class="btn btn-outline-success install-uninstall-btn"
                                                        data-package="{{ $package->package_name }}"
                                                        data-name="{{ $package->display_name }}" data-action="install"
                                                        data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                                        Install
                                                    </button>
                                                @else
                                                    <div style="visibility: hidden;">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary">Placeholder</button>
                                                    </div>
                                                @endif
                                            @elseif (
                                                ($industry === 'ecommerce' && in_array($packageName, $restrictedForProduct)) ||
                                                    ($industry === 'education' && in_array($packageName, $restrictedForCourse)))
                                                {{-- Restricted package: allow install, block uninstall --}}
                                                @if (!$installed)
                                                    <button type="button"
                                                        class="btn btn-outline-success install-uninstall-btn"
                                                        data-package="{{ $package->package_name }}"
                                                        data-name="{{ $package->display_name }}" data-action="install"
                                                        data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
                                                        Install
                                                    </button>
                                                @else
                                                    {{-- Installed → do not show uninstall --}}
                                                    <div style="visibility: hidden;">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary">Placeholder</button>
                                                    </div>
                                                @endif
                                            @else
                                                <button type="button"
                                                    class="btn btn-outline-{{ $installed ? 'danger' : 'success' }} install-uninstall-btn"
                                                    data-package="{{ $package->package_name }}"
                                                    data-name="{{ $package->display_name }}"
                                                    data-action="{{ $installed ? 'uninstall' : 'install' }}"
                                                    data-deps-url="{{ route('admin.packages.dependencies', ['vendor' => $vendor, 'package' => $packageName]) }}">
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
        let overlayDeps = [];
        let overlayAction = 'install';
        let overlayMainPackage = '';

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
                $('#package-progress-bar-container').fadeOut(300, function() {
                    bar.css({
                        transition: 'none',
                        width: '0%'
                    });
                });
            }, 700);
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
                    const depsUrl = this.dataset.depsUrl;

                    // Step 1: fetch dependencies and show confirmation with list
                    const queryUrl = `${depsUrl}?action=${encodeURIComponent(action)}`;
                    fetch(queryUrl, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(depRes => {
                            const deps = Array.isArray(depRes.dependencies) ? depRes
                                .dependencies : [];
                            const hasDeps = deps.length > 0;
                            const actionVerb = action === 'install' ? 'install' : 'uninstall';
                            const title = hasDeps ?
                                `${actionVerb.charAt(0).toUpperCase() + actionVerb.slice(1)} ${displayName}` :
                                `Confirm ${actionVerb}`;
                            let html =
                                `Are you sure you want to ${actionVerb} <b>${displayName}</b>?`;
                            if (hasDeps) {
                                const depList = deps.map(d => `<li>${d}</li>`).join('');
                                const note = action === 'install' ?
                                    'The following dependency packages will also be installed:' :
                                    'The following dependent packages will also be uninstalled:';
                                html +=
                                    `<div style="text-align:left;margin-top:10px">${note}<ul style="margin-top:8px">${depList}</ul></div>`;
                            }

                            // Store for overlay display
                            overlayDeps = deps;
                            overlayAction = action;
                            overlayMainPackage = displayName;

                            return Swal.fire({
                                title: title,
                                html: html,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: `Yes, ${actionVerb} it!`,
                                cancelButtonText: 'No, cancel!',
                                customClass: {
                                    confirmButton: 'btn btn-outline-success',
                                    cancelButton: 'btn btn-outline-danger',
                                }
                            });
                        })
                        .then((result) => {
                            if (result.isConfirmed) {
                                allButtons.forEach(btn => btn.disabled = true);

                                // Show full page loader with step-by-step package installation
                                const allPackages = [...overlayDeps, displayName]; // Dependencies first, then main package
                                const actionText = action === 'install' ? 'Installing Packages...' : 'Uninstalling Packages...';
                                document.getElementById('loading-text').textContent = actionText;
                                document.getElementById('loading-percentage').textContent = '1%';
                                document.getElementById('page-overlay').style.display = 'block';

                                const originalText = this.innerHTML;
                                this.innerHTML = action === 'install' ? 'Installing...' : 'Uninstalling...';

                                let currentPackageIndex = 0;
                                let currentPackagePercent = 0;
                                let totalPercent = 0;
                                let isInstallationComplete = false;
                                let progressInterval = null;
                                let packageProgressInterval = null;

                                // Calculate timing based on number of packages
                                const totalPackages = allPackages.length;
                                const baseDurationPerPackage = 40000; // 40 seconds per package
                                const totalDuration = baseDurationPerPackage * totalPackages;

                                const btn = this;

                                // Function to update progress display
                                function updateProgress(progressPercent, packageName = '') {
                                    console.log('Updating progress to:', progressPercent + '%', 'Package:', packageName);
                                    document.getElementById('loading-percentage').textContent = Math.round(progressPercent) + '%';
                                    
                                    // Update package name display
                                    if (packageName) {
                                        document.getElementById('loading-text').textContent = 
                                            (action === 'install' ? 'Installing: ' : 'Uninstalling: ') + packageName;
                                    }
                                    
                                    // Update package counter
                                    document.getElementById('package-counter').textContent = 
                                        `Package ${currentPackageIndex + 1} of ${totalPackages}`;
                                    
                                    const progressCircle = document.getElementById('progress-circle');
                                    const circumference = 2 * Math.PI * 35; // radius = 35
                                    const offset = circumference - (progressPercent / 100) * circumference;
                                    progressCircle.style.strokeDashoffset = offset;
                                }

                                // Function to start progress for current package
                                function startPackageProgress() {
                                    if (currentPackageIndex >= allPackages.length) {
                                        // All packages processed, complete to 100%
                                        completeTo100();
                                        return;
                                    }

                                    const currentPackage = allPackages[currentPackageIndex];
                                    const isLastPackage = currentPackageIndex === allPackages.length - 1;
                                    
                                    console.log(`Starting package ${currentPackageIndex + 1}/${totalPackages}: ${currentPackage}`);
                                    
                                    // Reset package progress
                                    currentPackagePercent = 0;
                                    
                                    // Update display for current package
                                    updateProgress(0, currentPackage);
                                    
                                    // Start package-specific progress
                                    packageProgressInterval = setInterval(() => {
                                        if (currentPackagePercent < 100 && !isInstallationComplete) {
                                            // For last package, stop at 90% until installation completes
                                            const targetPercent = isLastPackage ? 90 : 100;
                                            
                                            if (currentPackagePercent < targetPercent) {
                                                currentPackagePercent += 1; // 1% increments
                                                updateProgress(currentPackagePercent, currentPackage);
                                            } else if (isLastPackage && currentPackagePercent >= 90) {
                                                // Last package reached 90%, wait for real installation
                                                clearInterval(packageProgressInterval);
                                                return;
                                            } else if (!isLastPackage) {
                                                // Non-last package completed, move to next
                                                clearInterval(packageProgressInterval);
                                                finishCurrentPackage();
                                            }
                                        } else if (isInstallationComplete && isLastPackage) {
                                            // Installation completed, finish last package to 100%
                                            clearInterval(packageProgressInterval);
                                            completeLastPackage();
                                        } else if (!isLastPackage) {
                                            // Non-last package completed, move to next
                                            clearInterval(packageProgressInterval);
                                            finishCurrentPackage();
                                        }
                                    }, baseDurationPerPackage / 100); // 100 intervals per package
                                }

                                // Function to finish current package and move to next
                                function finishCurrentPackage() {
                                    updateProgress(100, allPackages[currentPackageIndex]);
                                    
                                    currentPackageIndex++;
                                    
                                    // Small delay before next package
                                    setTimeout(() => {
                                        startPackageProgress();
                                    }, 1000);
                                }

                                // Function to complete last package to 100%
                                function completeLastPackage() {
                                    const completeInterval = setInterval(() => {
                                        if (currentPackagePercent < 100) {
                                            currentPackagePercent += 2;
                                            updateProgress(currentPackagePercent, allPackages[currentPackageIndex]);
                                        } else {
                                            clearInterval(completeInterval);
                                            totalPercent = 100;
                                        }
                                    }, 50); // Fast completion
                                }

                                // Function to complete progress to 100%
                                function completeTo100() {
                                    totalPercent = 100;
                                    updateProgress(totalPercent, 'Finalizing...');
                                }

                                // Waiter to proceed only after reaching 100%
                                function waitUntilHundred(callback) {
                                    const watcher = setInterval(() => {
                                        if (totalPercent >= 100) {
                                            clearInterval(watcher);
                                            callback();
                                        }
                                    }, 50);
                                }

                                // Start the step-by-step progress
                                startPackageProgress();
                                startPackageProgressBar(totalDuration);

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
                                        // Mark installation as complete
                                        isInstallationComplete = true;

                                        // Clear any existing intervals
                                        if (packageProgressInterval) {
                                            clearInterval(packageProgressInterval);
                                        }
                                        if (progressInterval) {
                                            clearInterval(progressInterval);
                                        }

                                        // Complete last package to 100% if it's the last one
                                        if (currentPackageIndex === allPackages.length - 1) {
                                            completeLastPackage();
                                        } else {
                                            // Complete to 100% if not already there
                                            completeTo100();
                                        }

                                        finishPackageProgressBar(totalPercent);

                                        if (data.success || data.status === 'success') {
                                            waitUntilHundred(() => {
                                                document.getElementById('page-overlay').style.display = 'none';
                                                btn.innerHTML = `Completed`;
                                                Swal.fire({
                                                    title: 'Success',
                                                    text: data.message || 'Operation successful.',
                                                    icon: 'success',
                                                    timer: 1500,
                                                    showConfirmButton: false
                                                }).then(() => window.location.reload());
                                            });
                                        } else {
                                            waitUntilHundred(() => {
                                                document.getElementById('page-overlay').style.display = 'none';
                                                Swal.fire('Error', data.message || 'Operation failed.', 'error');
                                                allButtons.forEach(btn => btn.disabled = false);
                                                btn.innerHTML = originalText;
                                            });
                                        }
                                    })
                                    .catch((error) => {
                                        // Mark installation as complete (even if failed)
                                        isInstallationComplete = true;

                                        // Clear any existing intervals
                                        if (packageProgressInterval) {
                                            clearInterval(packageProgressInterval);
                                        }
                                        if (progressInterval) {
                                            clearInterval(progressInterval);
                                        }

                                        // Complete to 100% for UI consistency
                                        completeTo100();

                                        finishPackageProgressBar(totalPercent);

                                        console.error('Fetch error:', error);
                                        waitUntilHundred(() => {
                                            document.getElementById('page-overlay').style.display = 'none';
                                            Swal.fire('Error', 'Something went wrong.', 'error');
                                            allButtons.forEach(btn => btn.disabled = false);
                                            this.innerHTML = originalText;
                                        });
                                    });
                            }
                        }).catch(() => {
                            // If deps fetch fails, fallback to simple confirm
                            Swal.fire({
                                text: `Are you sure you want to ${action} ${displayName} package?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: `Yes, ${action} it!`,
                                cancelButtonText: 'No, cancel!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    allButtons.forEach(btn => btn.disabled = true);
                                    // Trigger original flow by programmatically clicking again without deps fetch
                                    // But we directly proceed with the POST below for simplicity
                                    const evt = new Event('proceed-install-uninstall');
                                    button.dispatchEvent(evt);
                                }
                            });
                        });
                });
            });
        });
    </script>
    <style>
        #page-overlay {
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        #full-page-loader {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        #gradient-loader {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        #loading-percentage {
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
            }

            to {
                text-shadow: 0 0 20px rgba(0, 212, 255, 0.8), 0 0 30px rgba(0, 212, 255, 0.6);
            }
        }

        #package-counter {
            animation: fadeInOut 1s ease-in-out;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(10px); }
            50% { opacity: 1; transform: translateY(0); }
            100% { opacity: 1; transform: translateY(0); }
        }

        #loading-text {
            transition: all 0.3s ease;
        }

        .install-uninstall-btn:disabled {
            opacity: 0.8;
            cursor: not-allowed;
        }
    </style>
@endpush
