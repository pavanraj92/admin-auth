@extends('admin::admin.layouts.master')

@section('title', 'Admin Change Password')
@section('meta_description')
Use this page to change your password
@endsection

@section('page-title', 'Change Password')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
@endsection
@push('styles')
<style>
    .password-toggle {
        position: relative;
    }
    .toggle-password {
        position: absolute;
        top: 38px;
        right: 15px;
        cursor: pointer;
        color: #6c757d;
        z-index: 10;
    }
</style>
@endpush
@section('content')   
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-xlg-9 col-md-7">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.updatePassword') }}" class="form-horizontal form-material">
                            @csrf
                            <div class="form-group row">
                                <div class="col-md-6 password-toggle">
                                    <label for="old_password">Old Password<span class="text-danger">*</span></label>
                                    <input type="password" name="old_password" id="old_password" class="form-control form-control-line" placeholder="Old Password">
                                    <span toggle="#old_password" class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('old_password')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="col-md-6 password-toggle">
                                    <label for="new_password">New Password<span class="text-danger">*</span></label>
                                    <input type="password" name="new_password" id="new_password" class="form-control form-control-line" placeholder="New Password">
                                    <span toggle="#new_password" class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('new_password')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <div class="col-md-6 password-toggle">
                                    <label for="confirm_new_password">Confirm New Password<span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control form-control-line" placeholder="Confirm New Password">
                                    <span toggle="#confirm_new_password" class="fa fa-fw fa-eye-slash toggle-password"></span>
                                    @error('confirm_new_password')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-success">Update Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
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