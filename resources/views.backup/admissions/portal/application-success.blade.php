{{-- resources/views/admissions/portal/application-success.blade.php --}}
@extends('layouts.app')

@section('title', 'Application Submitted Successfully')

@section('styles')
<style>
    .success-animation {
        animation: successPulse 1s ease-in-out;
    }
    
    @keyframes successPulse {
        0% {
            transform: scale(0);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .success-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 60px;
        box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
    }
    
    .timeline-step {
        position: relative;
        padding-left: 50px;
        margin-bottom: 30px;
    }
    
    .timeline-step::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        bottom: -30px;
        width: 2px;
        background: #dee2e6;
    }
    
    .timeline-step:last-child::before {
        display: none;
    }
    
    .timeline-step-icon {
        position: absolute;
        left: 10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #dee2e6;
    }
    
    .timeline-step.completed .timeline-step-icon {
        background: #28a745;
        border-color: #28a745;
    }
    
    .timeline-step.current .timeline-step-icon {
        background: #007bff;
        border-color: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25);
    }
    
    .checklist-card {
        border-left: 4px solid #17a2b8;
        transition: all 0.3s ease;
    }
    
    .checklist-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    
    .confetti {
        position: fixed;
        width: 10px;
        height: 10px;
        background: #ffc107;
        position: absolute;
        animation: confetti-fall 3s linear;
    }
    
    @keyframes confetti-fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }
    
    .print-section {
        display: none;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        .print-section {
            display: block !important;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Success Message --}}
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <div class="success-icon success-animation mb-4">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="display-4 font-weight-bold mb-3">Congratulations!</h1>
            <p class="lead text-muted mb-4">
                Your application has been successfully submitted to IntelliCampus.
            </p>
            
            <div class="alert alert-success shadow-sm">
                <h4 class="alert-heading">
                    <i class="fas fa-check-circle me-2"></i>Application #{{ $application->application_number }}
                </h4>
                <p class="mb-0">
                    Submitted on {{ $application->submitted_at->format('F d, Y \a\t g:i A') }}
                </p>
            </div>
            
            {{-- Quick Actions --}}
            <div class="d-flex justify-content-center gap-3 mt-4 no-print">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>Print Confirmation
                </button>
                <a href="{{ route('admissions.application.download', $application->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-2"></i>Download Application
                </a>
                <a href="{{ route('admissions.portal.status', $application->id) }}" class="btn btn-primary">
                    <i class="fas fa-chart-line me-2"></i>Track Status
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Application Details --}}
        <div class="col-lg-8">
            {{-- Confirmation Details --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Application Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Application Number:</td>
                                    <td class="fw-bold">{{ $application->application_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Application Type:</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $application->application_type)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Program Applied:</td>
                                    <td>{{ $application->program->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Entry Term:</td>
                                    <td>{{ $application->term->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Applicant Name:</td>
                                    <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>{{ $application->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone:</td>
                                    <td>{{ $application->phone_primary }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Submission Date:</td>
                                    <td>{{ $application->submitted_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    {{-- QR Code for verification --}}
                    <div class="text-center mt-3 pt-3 border-top">
                        <p class="text-muted small mb-2">Scan to verify application</p>
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 150px; height: 150px;">
                        <p class="text-muted small mt-2">Verification Code: {{ $application->application_uuid }}</p>
                    </div>
                </div>
            </div>

            {{-- What's Next Timeline --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-road me-2"></i>What Happens Next?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-step completed">
                        <div class="timeline-step-icon"></div>
                        <h6>Application Submitted</h6>
                        <p class="text-muted">Your application has been received and is being processed.</p>
                        <small class="text-success"><i class="fas fa-check me-1"></i>Completed</small>
                    </div>
                    
                    <div class="timeline-step current">
                        <div class="timeline-step-icon"></div>
                        <h6>Application Fee Payment</h6>
                        <p class="text-muted">
                            @if($application->application_fee_paid)
                                Application fee has been paid.
                                <span class="badge bg-success ms-2">Paid</span>
                            @else
                                Please pay the application fee within 48 hours to avoid delays.
                                <a href="{{ route('admissions.fee.pay', $application->id) }}" class="btn btn-sm btn-warning ms-2">
                                    Pay Now
                                </a>
                            @endif
                        </p>
                    </div>
                    
                    <div class="timeline-step">
                        <div class="timeline-step-icon"></div>
                        <h6>Document Verification</h6>
                        <p class="text-muted">Our admissions team will verify all submitted documents (3-5 business days).</p>
                    </div>
                    
                    <div class="timeline-step">
                        <div class="timeline-step-icon"></div>
                        <h6>Application Review</h6>
                        <p class="text-muted">Your application will be reviewed by the admissions committee (2-3 weeks).</p>
                    </div>
                    
                    <div class="timeline-step">
                        <div class="timeline-step-icon"></div>
                        <h6>Decision Notification</h6>
                        <p class="text-muted">You'll receive an email with the admission decision by {{ $application->term->decision_release_date?->format('F d, Y') ?? 'TBD' }}.</p>
                    </div>
                </div>
            </div>

            {{-- Important Reminders --}}
            <div class="card shadow checklist-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Important Reminders
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check1" {{ $application->application_fee_paid ? 'checked' : '' }}>
                        <label class="form-check-label" for="check1">
                            <strong>Pay Application Fee</strong>
                            @if(!$application->application_fee_paid)
                                - Due by {{ now()->addDays(2)->format('F d, Y') }}
                            @else
                                <span class="badge bg-success ms-2">Completed</span>
                            @endif
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check2">
                        <label class="form-check-label" for="check2">
                            <strong>Check Email Regularly</strong> - Important updates will be sent to {{ $application->email }}
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check3">
                        <label class="form-check-label" for="check3">
                            <strong>Submit Missing Documents</strong> - If any documents are pending
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check4">
                        <label class="form-check-label" for="check4">
                            <strong>Complete Financial Aid Application</strong> - If you're applying for aid
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="check5">
                        <label class="form-check-label" for="check5">
                            <strong>Save Your Application Number</strong> - {{ $application->application_number }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Links --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-link me-2"></i>Quick Links
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admissions.portal.status', $application->id) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Track Application Status
                    </a>
                    <a href="{{ route('admissions.document.upload', $application->id) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-upload me-2 text-warning"></i>Upload Additional Documents
                    </a>
                    @if(!$application->application_fee_paid)
                    <a href="{{ route('admissions.fee.pay', $application->id) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-credit-card me-2 text-danger"></i>Pay Application Fee
                    </a>
                    @endif
                    <a href="{{ route('financial.aid.apply') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-hand-holding-usd me-2 text-success"></i>Apply for Financial Aid
                    </a>
                    <a href="{{ route('admissions.portal.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2 text-info"></i>Return to Dashboard
                    </a>
                </div>
            </div>

            {{-- Confirmation Email Sent --}}
            <div class="card shadow mb-4 info-card">
                <div class="card-body text-center text-white">
                    <i class="fas fa-envelope fa-3x mb-3"></i>
                    <h5>Confirmation Email Sent!</h5>
                    <p class="mb-0">A detailed confirmation has been sent to:</p>
                    <p class="fw-bold">{{ $application->email }}</p>
                    <small>Check your spam folder if you don't see it</small>
                </div>
            </div>

            {{-- Contact Support --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-headset me-2"></i>Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-3">Our admissions team is here to help you:</p>
                    
                    <div class="mb-2">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <strong>Phone:</strong><br>
                        <a href="tel:+2317700000">+231 77 000 0000</a>
                    </div>
                    
                    <div class="mb-2">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <strong>Email:</strong><br>
                        <a href="mailto:admissions@intellicampus.edu">admissions@intellicampus.edu</a>
                    </div>
                    
                    <div class="mb-2">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        <strong>Office Hours:</strong><br>
                        Mon-Fri: 8:00 AM - 5:00 PM
                    </div>
                    
                    <div>
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        <strong>Visit Us:</strong><br>
                        Admissions Office, Main Building
                    </div>
                </div>
            </div>

            {{-- Share Your Success --}}
            <div class="card shadow no-print">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-share-alt me-2"></i>Share Your Success
                    </h6>
                </div>
                <div class="card-body text-center">
                    <p class="small mb-3">Excited about applying to IntelliCampus? Share with friends!</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-primary" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook"></i>
                        </button>
                        <button class="btn btn-sm btn-info text-white" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="shareViaEmail()">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Print Section (Hidden on screen, visible on print) --}}
<div class="print-section">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2>IntelliCampus University</h2>
        <h3>Application Confirmation</h3>
    </div>
    
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td><strong>Application Number:</strong></td>
            <td>{{ $application->application_number }}</td>
            <td><strong>Date Submitted:</strong></td>
            <td>{{ $application->submitted_at->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Applicant Name:</strong></td>
            <td>{{ $application->first_name }} {{ $application->last_name }}</td>
            <td><strong>Email:</strong></td>
            <td>{{ $application->email }}</td>
        </tr>
        <tr>
            <td><strong>Program:</strong></td>
            <td>{{ $application->program->name }}</td>
            <td><strong>Term:</strong></td>
            <td>{{ $application->term->name }}</td>
        </tr>
    </table>
    
    <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #000;">
        <p><strong>Important:</strong> Please keep this confirmation for your records.</p>
        <p>For questions, contact admissions@intellicampus.edu or call +231 77 000 0000</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Confetti animation on load
    createConfetti();
    
    // Check all checkboxes animation
    setTimeout(function() {
        $('.form-check-input:not(:checked)').each(function(index) {
            setTimeout(() => {
                $(this).prop('checked', true);
            }, 500 * (index + 1));
        });
    }, 2000);
    
    // Auto-save application number to clipboard
    const applicationNumber = '{{ $application->application_number }}';
    $('#copy-number').click(function() {
        navigator.clipboard.writeText(applicationNumber).then(function() {
            toastr.success('Application number copied to clipboard!');
        });
    });
});

// Create confetti effect
function createConfetti() {
    const colors = ['#ffc107', '#28a745', '#17a2b8', '#dc3545', '#6610f2'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        setTimeout(() => {
            const confetti = $('<div class="confetti"></div>');
            confetti.css({
                left: Math.random() * 100 + '%',
                background: colors[Math.floor(Math.random() * colors.length)],
                animationDelay: Math.random() * 3 + 's',
                animationDuration: Math.random() * 3 + 2 + 's'
            });
            $('body').append(confetti);
            
            setTimeout(() => confetti.remove(), 5000);
        }, i * 30);
    }
}

// Social sharing functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent("I just submitted my application to IntelliCampus University! 🎓");
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
}

function shareOnTwitter() {
    const text = encodeURIComponent("I just submitted my application to IntelliCampus University! 🎓 #IntelliCampus #FutureStudent");
    const url = encodeURIComponent(window.location.href);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
}

function shareOnWhatsApp() {
    const text = encodeURIComponent("I just submitted my application to IntelliCampus University! 🎓");
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function shareViaEmail() {
    const subject = encodeURIComponent("My IntelliCampus Application");
    const body = encodeURIComponent("I'm excited to share that I just submitted my application to IntelliCampus University!");
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

// Print function with custom settings
function printConfirmation() {
    window.print();
}

// Download application as PDF (would need backend implementation)
function downloadApplication() {
    window.location.href = '{{ route("admissions.application.download", $application->id) }}';
}
</script>
@endsection