@extends('admin::admin.layouts.master')

@section('title', 'Admin Change Password')
@section('meta_description')
    Use this page to change your password
@endsection

@section('page-title', 'Change Password')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
@endsection
@section('content')
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.updatePassword') }}" id="updatePasswordForm">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card card-body">
                        <div class="row">
                            <div class="col-md-6 password-toggle">
                                <div class="form-group">
                                    <label for="old_password">Old Password<span class="text-danger">*</span></label>
                                    <input type="password" name="old_password" id="old_password"
                                        class="form-control form-control-line" placeholder="Old Password">
                                    <span toggle="#old_password" class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('old_password')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 password-toggle">
                                <div class="form-group">
                                    <label for="new_password">New Password<span class="text-danger">*</span></label>
                                    <input type="password" name="new_password" id="new_password"
                                        class="form-control form-control-line" placeholder="New Password">
                                    <span toggle="#new_password" class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('new_password')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 password-toggle">
                                <div class="form-group">
                                    <label for="confirm_new_password">Confirm New Password<span
                                            class="text-danger">*</span></label>
                                    <input type="password" name="confirm_new_password" id="confirm_new_password"
                                        class="form-control form-control-line" placeholder="Confirm New Password">
                                    <span toggle="#confirm_new_password"
                                        class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('confirm_new_password')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="saveBtn">Update Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $.validator.addMethod("strongPassword", function(value, element) {
                return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/
                    .test(value);
            }, "Password must include uppercase, lowercase, number, and special character");
            //jquery validation for the form
            $('#updatePasswordForm').validate({
                ignore: [],
                rules: {
                    old_password: {
                        required: true,
                    },
                    new_password: {
                        required: true,
                        strongPassword: true
                    },
                    confirm_new_password: {
                        required: true,
                        equalTo: "#new_password"
                    }
                },
                messages: {
                    old_password: {
                        required: "Please enter old password",
                    },
                    new_password: {
                        required: "Please enter new password",
                    },
                    confirm_new_password: {
                        required: "Please enter confirm password",
                        equalTo: "New password and confirm password does not match."
                    }
                },
                submitHandler: function(form) {
                    const $btn = $('#saveBtn');
                    $btn.prop('disabled', true).text('Updating Password...');

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
