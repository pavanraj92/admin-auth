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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-sm p-4" style="min-width: 350px;">
            <h2 class="mb-4 text-center">Forgot Password</h2>

            <form method="POST" action="{{ route('admin.sendResetLinkEmail') }}" id="forgotPasswordForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" autofocus>
                    @error('email')
                        <div class="text-danger validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" id="resetBtn">
                        Send Password Reset Link
                    </button>
                </div>
            </form>
            <div class="mt-3 text-center">
                <a href="{{ route('admin.login') }}">Back to Login</a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
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
            $.validator.addMethod("customEmail", function(value, element) {
                return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(
                    value);
            }, "Please enter a valid email address");

            $('#forgotPasswordForm').validate({
                rules: {
                    email: {
                        required: true,
                        email: true,
                        customEmail: true
                    }
                },
                messages: {
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email address"
                    }
                },
                submitHandler: function(form) {
                    // Update textarea before submit
                    const $btn = $('#resetBtn');
                    $btn.prop('disabled', true).text('Sending...');
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
            // $('form').on('submit', function () {
            //     let btn = $('#resetBtn');
            //     btn.prop('disabled', true).text('Sending...');
            // });
        });
    </script>
</body>

</html>
