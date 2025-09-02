@extends('admin::admin.layouts.master')

@section('title', 'Admin Profile')
@section('meta_description')
    View and update your admin profile information.
@endsection

@section('page-title', 'Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('admin.profileUpdate') }}" id="updateProfileForm">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name<span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name" placeholder="First Name"
                                        class="form-control form-control-line alphabets-only"
                                        value="{{ $admin->first_name ?? '' }}">
                                    @error('first_name')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name<span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" placeholder="Last Name"
                                        class="form-control form-control-line alphabets-only"
                                        value="{{ $admin->last_name ?? '' }}">
                                    @error('last_name')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="example-email">Email<span class="text-danger">*</span></label>
                                    <input type="email" placeholder="Email" class="form-control form-control-line"
                                        name="email" id="email" value="{{ $admin->email ?? '' }}">
                                    @error('email')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website_name">Website Name<span class="text-danger">*</span></label>
                                    <input type="text" name="website_name" id="website_name" placeholder="Website Name"
                                        class="form-control form-control-line" value="{{ $admin->website_name ?? '' }}">
                                    @error('website_name')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"
                                id="saveBtn">Update Profile</button>
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
              $.validator.addMethod("customEmail", function(value, element) {
                return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
            }, "Please enter a valid email address");

            //jquery validation for the form
            $('#updateProfileForm').validate({
                ignore: [],
                rules: {
                    first_name: {
                        required: true,
                        minlength: 3
                    },
                    last_name: {
                        required: true,
                        minlength: 3
                    },
                    email: {
                        required: true,
                        email: true,
                        customEmail: true
                    },
                    website_name: {
                        required: true,
                        minlength: 3
                    }
                },
                messages: {
                    first_name: {
                        required: "Please enter first name",
                        minlength: "First name must be at least 3 characters long"
                    },
                    last_name: {
                        required: "Please enter last name",
                        minlength: "Last name must be at least 3 characters long"
                    },
                    email: {
                        required: "Please enter email",
                        email: "Please enter a valid email address"
                    },
                    website_name: {
                        required: "Please enter website name",
                        minlength: "Website name must be at least 3 characters long"
                    }
                },
                submitHandler: function(form) {
                    const $btn = $('#saveBtn');
                    $btn.prop('disabled', true).text('Updating Profile...');

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