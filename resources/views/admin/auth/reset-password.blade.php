<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    @if (config('GET.main_favicon'))
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/' . config('GET.main_favicon')) }}">
    @else
        <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/favicon.png">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('backend/assets/libs/chartist/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/custom.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-sm p-4" style="min-width: 350px;">
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
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: {!! json_encode(session('success')) !!},
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: {!! json_encode($errors->first()) !!},
                showConfirmButton: true,
            });
        </script>
    @endif

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
</body>

</html>
