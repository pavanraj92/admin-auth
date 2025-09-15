@extends('admin::admin.auth.layouts.auth')

@section('title', 'Forgot Password')

@section('content')
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
@endsection

@push('scripts')
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
        });
    </script>
@endpush
