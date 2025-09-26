@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="auth-container">
                <div class="card shadow-sm border-0">
                    <div class="card-header text-center py-4">
                        <div class="logo-section">
                            <i class="fas fa-envelope-open-text logo-icon"></i>
                            <h3 class="mb-0">Verify Your Email</h3>
                            <p class="mb-0 mt-2 text-muted">One more step</p>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                A new verification link has been sent to your email address.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <p class="text-muted">
                                Before proceeding, please check your email for a verification link. 
                                If you didn't receive the email, you can request another one below.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i> Resend Verification Email
                                </button>
                            </div>
                        </form>

                        <hr class="my-3">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-sign-out-alt me-2"></i> Log Out
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection