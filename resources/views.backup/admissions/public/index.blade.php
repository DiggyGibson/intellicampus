{{-- resources/views/admissions/public/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admissions - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 mb-4">Welcome to Admissions</h1>
            <p class="lead">Begin your journey with us at {{ config('app.name') }}</p>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Admission Requirements</h5>
                    <p class="card-text">Learn about our admission requirements and prerequisites.</p>
                    <a href="{{ route('admissions.requirements') }}" class="btn btn-outline-primary">View Requirements</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Academic Programs</h5>
                    <p class="card-text">Explore our wide range of academic programs.</p>
                    <a href="{{ route('admissions.programs') }}" class="btn btn-outline-primary">Browse Programs</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Apply Online</h5>
                    <p class="card-text">Start your application process today.</p>
                    <a href="{{ route('admissions.portal.index') }}" class="btn btn-primary">Apply Now</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Important Dates</h5>
                    <p class="card-text">View admission deadlines and key dates.</p>
                    <a href="{{ route('admissions.calendar') }}" class="btn btn-outline-info">View Calendar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Frequently Asked Questions</h5>
                    <p class="card-text">Find answers to common admission questions.</p>
                    <a href="{{ route('admissions.faq') }}" class="btn btn-outline-info">View FAQ</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Contact Admissions</h5>
                    <p class="card-text">Get in touch with our admissions team.</p>
                    <a href="{{ route('admissions.contact') }}" class="btn btn-outline-info">Contact Us</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links Section --}}
    <div class="row mt-5">
        <div class="col-12">
            <h3>Quick Links</h3>
            <div class="list-group list-group-horizontal-md mt-3">
                <a href="{{ route('admissions.international') }}" class="list-group-item list-group-item-action">
                    International Students
                </a>
                <a href="{{ route('admissions.transfer') }}" class="list-group-item list-group-item-action">
                    Transfer Students
                </a>
                <a href="{{ route('admissions.graduate') }}" class="list-group-item list-group-item-action">
                    Graduate Programs
                </a>
                <a href="{{ route('admissions.fees') }}" class="list-group-item list-group-item-action">
                    Tuition & Fees
                </a>
            </div>
        </div>
    </div>
</div>
@endsection