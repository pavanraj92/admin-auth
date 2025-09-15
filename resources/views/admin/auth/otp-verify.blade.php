@extends('admin::admin.auth.layouts.auth')

@section('title', 'Verify OTP')

@section('card-class', 'admin-otp-verify')

@section('content')
    <div class="text-center mb-4">
        <img src="{{ asset('images/dots-logo-trans.png') }}" alt="Logo" style="max-width: 150px;">
    </div>
    <h2 class="mb-4 text-center">Two-Factor Authentication</h2>
    
    <div class="alert alert-info text-center mb-4">
        <i class="fa fa-info-circle"></i>
        We've sent a 6-digit verification code to <strong>{{ $admin->email }}</strong>
    </div>

    <div class="text-center mb-4">
        <div class="otp-timer">
            <div class="timer-display">
                <span class="timer-text">Code expires in:</span>
                <span class="timer-countdown" id="countdown">05:00</span>
            </div>
        </div>
    </div>

    <form method="POST" id="otpForm" action="{{ route('admin.otp.verify') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Enter Verification Code<span class="text-danger">*</span></label>
            <div class="d-flex justify-content-between gap-2 otp-inputs" style="max-width: 360px; margin:0 auto;">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="0">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="1">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="2">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="3">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="4">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center otp-box" maxlength="1" data-index="5">
            </div>
            <input type="hidden" id="otp_code" name="otp_code" value="">
            @error('otp_code')
                <div class="text-danger validation-error text-center">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3" id="verifyBtn">
            Verify Code
        </button>

        <div class="text-center">
            <p class="mb-2">Didn't receive the code?</p>
            <button type="button" class="btn btn-link" id="resendBtn" disabled>
                Resend Code (<span id="resendCountdown">60</span>s)
            </button>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('admin.login') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let timeLeft = {{ isset($remainingSeconds) ? (int)$remainingSeconds : 300 }}; // server-provided remaining seconds
            // Compute resend time left based on server timestamps to avoid drift
            const serverNow = {{ isset($serverNow) ? (int)$serverNow : 'null' }};
            const resendAllowedAt = {{ isset($resendAllowedAt) ? (int)$resendAllowedAt : 'null' }};
            let resendTimeLeft = 60;
            if (serverNow && resendAllowedAt) {
                const clientNow = Math.floor(Date.now() / 1000);
                const skew = clientNow - serverNow; // client - server
                const effectiveNow = clientNow - skew; // align to server time
                resendTimeLeft = Math.max(0, resendAllowedAt - effectiveNow);
            }
            let countdownInterval;
            let resendInterval;

            // Format time as MM:SS
            function formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            // Start countdown timer
            function startCountdown() {
                countdownInterval = setInterval(function() {
                    $('#countdown').text(formatTime(timeLeft));
                    timeLeft--;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        $('#countdown').text('00:00');
                        $('.timer-display').addClass('expired');
                        $('#verifyBtn').prop('disabled', true).text('Code Expired');
                        
                        // Show expired message
                        if (!$('.expired-message').length) {
                            $('.otp-timer').after('<div class="alert alert-warning expired-message text-center">OTP has expired. Please request a new code.</div>');
                        }
                    }
                }, 1000);
            }

            // Start resend countdown
            function startResendCountdown() {
                if (resendInterval) clearInterval(resendInterval);
                // Ensure button has countdown markup
                if (!$('#resendCountdown').length) {
                    $('#resendBtn').html('Resend Code (<span id="resendCountdown">'+resendTimeLeft+'</span>s)');
                }
                $('#resendBtn').prop('disabled', resendTimeLeft > 0);
                $('#resendCountdown').text(resendTimeLeft);
                resendInterval = setInterval(function() {
                    resendTimeLeft = Math.max(0, resendTimeLeft - 1);
                    $('#resendCountdown').text(resendTimeLeft);
                    
                    if (resendTimeLeft <= 0) {
                        clearInterval(resendInterval);
                        $('#resendBtn').prop('disabled', false).html('Resend Code');
                    }
                }, 1000);
            }

            // OTP boxes behavior
            const $otpBoxes = $('.otp-box');
            $otpBoxes.on('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
                const index = parseInt($(this).data('index'));
                if (this.value && index < 5) {
                    $otpBoxes.eq(index + 1).focus();
                }
                syncHiddenOtp();
            });

            // Paste handler: distribute pasted digits across boxes
            $otpBoxes.on('paste', function(e) {
                e.preventDefault();
                const pasted = (e.originalEvent || e).clipboardData.getData('text') || '';
                const digits = pasted.replace(/\D/g, '').slice(0, 6).split('');
                if (!digits.length) return;
                $otpBoxes.val('');
                for (let i = 0; i < digits.length; i++) {
                    $otpBoxes.eq(i).val(digits[i]);
                }
                // Focus next empty or last
                const nextIndex = Math.min(digits.length, 5);
                $otpBoxes.eq(nextIndex).focus();
                syncHiddenOtp();
            });

            // Select text on focus for quick replace
            $otpBoxes.on('focus', function() {
                const input = this;
                setTimeout(function() { input.select(); }, 0);
            });

            $otpBoxes.on('keydown', function(e) {
                const index = parseInt($(this).data('index'));
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    $otpBoxes.eq(index - 1).focus().val('');
                    syncHiddenOtp();
                }
            });

            function syncHiddenOtp() {
                const code = $otpBoxes.map(function() { return this.value || ''; }).get().join('');
                $('#otp_code').val(code);
                if (code.length === 6) {
                    $('#otpForm').submit();
                }
            }

            // Resend OTP
            $('#resendBtn').on('click', function() {
                if ($(this).prop('disabled')) return;
                
                $.ajax({
                    url: '{{ route("admin.otp.resend") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#resendBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
                    },
                    success: function(response, status, xhr) {
                        if (response && response.success) {
                            // Reset timers
                            timeLeft = 300;
                            $('#countdown').text(formatTime(timeLeft));
                            $('.timer-display').removeClass('expired');
                            $('#verifyBtn').prop('disabled', false).text('Verify Code');
                            $('.expired-message').remove();
                            
                            // Restart countdown
                            clearInterval(countdownInterval);
                            startCountdown();
                            
                            // Start resend countdown from fresh 60s and restore button markup
                            resendTimeLeft = 60;
                            $('#resendBtn').html('Resend Code (<span id="resendCountdown">'+resendTimeLeft+'</span>s)').prop('disabled', true);
                            startResendCountdown();
                            
                            // Show success message
                            showAlert('success', 'New OTP sent to your email.');
                        } else {
                            showAlert('error', (response && response.message) ? response.message : 'Failed to resend OTP. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to resend OTP. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showAlert('error', message);
                    },
                    complete: function() {
                        if (!resendInterval && resendTimeLeft > 0) {
                            $('#resendBtn').html('Resend Code (<span id="resendCountdown">'+resendTimeLeft+'</span>s)').prop('disabled', true);
                        }
                    }
                });
            });

            // Form validation
            $('#otpForm').validate({
                rules: {
                    otp_code: {
                        required: true,
                        minlength: 6,
                        maxlength: 6,
                        digits: true
                    }
                },
                messages: {
                    otp_code: {
                        required: "Please enter the verification code",
                        minlength: "Please enter all 6 digits",
                        maxlength: "Please enter only 6 digits",
                        digits: "Please enter only numbers"
                    }
                },
                submitHandler: function(form) {
                    const $btn = $('#verifyBtn');
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verifying...');
                    form.submit();
                },
                errorElement: 'div',
                errorClass: 'text-danger custom-error',
                errorPlacement: function(error, element) {
                    $('.validation-error').hide();
                    error.insertAfter(element);
                }
            });

            // Show alert function
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
                
                $('.alert-info').after(alertHtml);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.alert:not(.alert-info)').fadeOut();
                }, 5000);
            }

            // Initialize display to the correct remaining time before starting interval
            $('#countdown').text(formatTime(timeLeft));
            // Start initial countdowns
            startCountdown();
            startResendCountdown();

            // Focus on OTP input
            $('#otp_code').focus();
        });
    </script>
@endpush
