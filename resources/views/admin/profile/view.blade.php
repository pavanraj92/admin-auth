@extends('admin::admin.layouts.master')

@section('title', 'Admin Profile')

@section('page-title', 'Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')   
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-xlg-9 col-md-7">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.profileUpdate') }}" class="form-horizontal form-material">
                            @csrf
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="first_name">First Name<span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name" placeholder="First Name" class="form-control form-control-line" value="{{$admin->first_name ?? ''}}">
                                    @error('first_name')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name">Last Name<span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" placeholder="Last Name" class="form-control form-control-line" value="{{$admin->last_name ?? ''}}">
                                    @error('last_name')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="example-email">Email<span class="text-danger">*</span></label>
                                    <input type="email" placeholder="Email" class="form-control form-control-line" name="email" id="email" value="{{$admin->email ?? ''}}">
                                    @error('email')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="website_name">Website Name<span class="text-danger">*</span></label>
                                    <input type="text" name="website_name" id="website_name" placeholder="Website Name" class="form-control form-control-line" value="{{$admin->website_name ?? ''}}">
                                    @error('website_name')
                                    <div class="text-danger validation-error">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-success">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
