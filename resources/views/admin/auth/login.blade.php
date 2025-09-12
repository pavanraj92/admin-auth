@extends('admin::admin.auth.layouts.auth')

@section('title', 'Admin Login')

@section('card-class', 'admin-login')

@section('content')
    <div class="text-center mb-4">
        <img src="{{ asset('images/dots-logo-trans.png') }}" alt="Logo" style="max-width: 150px;">
    </div>
    <h2 class="mb-4 text-center">Admin Login</h2>
    <form method="POST" id="loginForm" action="{{ route('admin.login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control" autofocus>
            @error('email')
                <div class="text-danger validation-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 password-toggle">
            <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
            <input type="password" id="password" name="password" class="form-control">
            <span toggle="#password" class="fa fa-fw fa-eye-slash toggle-password"></span>
            @error('password')
                <div class="text-danger validation-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                Remember Me
            </label>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="loginBtn">Login</button>
        <div class="mt-3 text-center">
            <a href="{{ route('admin.forgotPassword') }}">Forgot Password?</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.validator.addMethod("customEmail", function(value, element) {
                return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
            }, "Please enter a valid email address");

            //jquery validation for the form
            $('#loginForm').validate({
                rules: {
                    email: {
                        required: true,
                        email: true,
                        customEmail: true
                    },
                    password: {
                        required: true,
                    },
                },
                messages: {
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter password",
                    }
                },
                submitHandler: function(form) {
                    // Update textarea before submit
                    const $btn = $('#loginBtn');
                    $btn.prop('disabled', true).text('Logging...');
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
        document.addEventListener("DOMContentLoaded", function () {
            const toggles = document.querySelectorAll(".toggle-password");

            toggles.forEach(function (toggle) {
                toggle.addEventListener("click", function () {
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