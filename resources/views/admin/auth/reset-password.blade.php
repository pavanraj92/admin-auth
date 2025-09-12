@extends('admin::admin.auth.layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h2 class="mb-4 text-center">Reset Password</h2>

    <form method="POST" action="{{ route('admin.password.update') }}" id="resetPasswordForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? '' }}">
        <input type="hidden" name="email" value="{{ $email ?? '' }}">

        <div class="mb-3 password-toggle">
            <label for="password" class="form-label">New Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                name="password">
            <span toggle="#password" class="fa fa-fw fa-eye-slash toggle-password"></span>
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3 password-toggle">
            <label for="password-confirm" class="form-label">Confirm Password</label>
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
            <span toggle="#password-confirm" class="fa fa-fw fa-eye-slash toggle-password"></span>
            @error('password_confirmation')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary" id="resetPasswordBtn">
                Reset Password
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.validator.addMethod("strongPassword", function(value, element) {
                return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/
                    .test(value);
            }, "Password must include uppercase, lowercase, number, and special character");
            //jquery validation for the form
            $('#resetPasswordForm').validate({
                rules: {
                    password: {
                        required: true,
                        strongPassword: true
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    },
                },
                messages: {
                    password: {
                        required: "Please enter password",
                    },
                    password_confirmation: {
                        required: "Please enter confirm password",
                        equalTo: "Password and confirm password does not match."
                    }
                },
                submitHandler: function(form) {
                    // Update textarea before submit
                    const $btn = $('#resetPasswordBtn');
                    $btn.prop('disabled', true).text('Resetting Password...');
                    // Now submit
                    form.submit();
                },
                errorElement: 'div',
                errorClass: 'text-danger custom-error',
                errorPlacement: function(error, element) {
                    $('.validation-error').hide(); // hide blade errors
                    error.insertAfter(element);
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const toggles = document.querySelectorAll(".toggle-password");

            toggles.forEach(function(toggle) {
                toggle.addEventListener("click", function() {
                    const input = document.querySelector(this.getAttribute("toggle"));
                    const type = input.getAttribute("type") === "password" ? "text" : "password";
                    input.setAttribute("type", type);

                    // Toggle icon class
                    this.classList.toggle("fa-eye");
                    this.classList.toggle("fa-eye-slash");
                });
            });
        });
    </script>
@endpush
