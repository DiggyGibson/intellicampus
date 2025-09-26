{{-- resources/views/admissions/public/contact.blade.php --}}
@extends('layouts.guest')

@section('title', 'Contact Admissions - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-dark text-white rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Contact Admissions</h1>
                <p class="lead mb-4">We're here to help you on your journey to IntelliCampus University</p>
                <div class="d-flex gap-3">
                    <a href="tel:555-123-4567" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Call Now
                    </a>
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Start Application
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-headset" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Contact Information --}}
        <div class="col-lg-4 mb-4">
            {{-- Office Hours --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Office Hours</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>Monday - Friday:</td>
                            <td class="text-end"><strong>8:00 AM - 5:00 PM</strong></td>
                        </tr>
                        <tr>
                            <td>Saturday:</td>
                            <td class="text-end"><strong>9:00 AM - 2:00 PM</strong></td>
                        </tr>
                        <tr>
                            <td>Sunday:</td>
                            <td class="text-end"><strong>Closed</strong></td>
                        </tr>
                    </table>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Extended hours during application deadlines
                    </p>
                </div>
            </div>

            {{-- Contact Methods --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact Methods</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="tel:555-123-4567" class="list-group-item list-group-item-action">
                        <i class="fas fa-phone text-success me-3"></i>
                        <strong>(555) 123-4567</strong>
                    </a>
                    <a href="tel:1-800-CAMPUS" class="list-group-item list-group-item-action">
                        <i class="fas fa-phone-square text-info me-3"></i>
                        <strong>1-800-CAMPUS</strong> (Toll Free)
                    </a>
                    <a href="mailto:admissions@intellicampus.edu" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope text-primary me-3"></i>
                        admissions@intellicampus.edu
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="startLiveChat()">
                        <i class="fas fa-comments text-warning me-3"></i>
                        Live Chat Available
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fab fa-whatsapp text-success me-3"></i>
                        WhatsApp: +1 555-123-4567
                    </a>
                </div>
            </div>

            {{-- Mailing Address --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Visit Us</h5>
                </div>
                <div class="card-body">
                    <address>
                        <strong>Office of Admissions</strong><br>
                        IntelliCampus University<br>
                        123 University Boulevard<br>
                        Education City, EC 12345<br>
                        United States
                    </address>
                    <a href="#" class="btn btn-info btn-sm w-100" onclick="openMap()">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>

            {{-- Social Media --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Follow Us</h5>
                </div>
                <div class="card-body text-center">
                    <div class="d-flex justify-content-around">
                        <a href="#" class="text-primary fs-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-info fs-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-danger fs-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-primary fs-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-danger fs-3"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Send Us a Message</h4>
                </div>
                <div class="card-body">
                    <form id="contactForm" method="POST" action="#">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="studentType" class="form-label">I am a... <span class="text-danger">*</span></label>
                                <select class="form-select" id="studentType" name="student_type" required>
                                    <option value="">Select...</option>
                                    <option value="prospective_freshman">Prospective Freshman</option>
                                    <option value="transfer">Transfer Student</option>
                                    <option value="graduate">Graduate Student</option>
                                    <option value="international">International Student</option>
                                    <option value="parent">Parent/Guardian</option>
                                    <option value="counselor">School Counselor</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="interestedProgram" class="form-label">Program of Interest</label>
                                <select class="form-select" id="interestedProgram" name="program_interest">
                                    <option value="">Select...</option>
                                    <option value="computer_science">Computer Science</option>
                                    <option value="business">Business Administration</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="nursing">Nursing</option>
                                    <option value="liberal_arts">Liberal Arts</option>
                                    <option value="undecided">Undecided</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a topic...</option>
                                <option value="application_process">Application Process</option>
                                <option value="admission_requirements">Admission Requirements</option>
                                <option value="financial_aid">Financial Aid & Scholarships</option>
                                <option value="campus_visit">Campus Visit</option>
                                <option value="programs">Academic Programs</option>
                                <option value="transfer_credits">Transfer Credits</option>
                                <option value="international">International Student Services</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required 
                                      placeholder="Please provide as much detail as possible..."></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="mailingList" name="mailing_list">
                                <label class="form-check-label" for="mailingList">
                                    I would like to receive information about IntelliCampus University
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            We typically respond within 1-2 business days. For urgent matters, please call us directly.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Clear Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Regional Representatives --}}
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Regional Admissions Representatives</h5>
                </div>
                <div class="card-body">
                    <p>Our admissions counselors are assigned by geographic region to better serve you:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Northeast Region</h6>
                            <p class="mb-1"><strong>Sarah Johnson</strong></p>
                            <p class="text-muted small">
                                CT, MA, ME, NH, NJ, NY, PA, RI, VT<br>
                                <i class="fas fa-envelope me-1"></i> sarah.j@intellicampus.edu<br>
                                <i class="fas fa-phone me-1"></i> (555) 123-4571
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Southeast Region</h6>
                            <p class="mb-1"><strong>Michael Davis</strong></p>
                            <p class="text-muted small">
                                AL, FL, GA, KY, MS, NC, SC, TN, VA, WV<br>
                                <i class="fas fa-envelope me-1"></i> michael.d@intellicampus.edu<br>
                                <i class="fas fa-phone me-1"></i> (555) 123-4572
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Midwest Region</h6>
                            <p class="mb-1"><strong>Emily Wilson</strong></p>
                            <p class="text-muted small">
                                IL, IN, IA, KS, MI, MN, MO, NE, ND, OH, SD, WI<br>
                                <i class="fas fa-envelope me-1"></i> emily.w@intellicampus.edu<br>
                                <i class="fas fa-phone me-1"></i> (555) 123-4573
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>West Region</h6>
                            <p class="mb-1"><strong>David Chen</strong></p>
                            <p class="text-muted small">
                                AK, AZ, CA, CO, HI, ID, MT, NV, NM, OR, TX, UT, WA, WY<br>
                                <i class="fas fa-envelope me-1"></i> david.c@intellicampus.edu<br>
                                <i class="fas fa-phone me-1"></i> (555) 123-4574
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>International</h6>
                            <p class="mb-1"><strong>Maria Rodriguez</strong></p>
                            <p class="text-muted small">
                                All International Territories<br>
                                <i class="fas fa-envelope me-1"></i> maria.r@intellicampus.edu<br>
                                <i class="fas fa-phone me-1"></i> (555) 123-4575
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">Quick Links</h5>
            <div class="row">
                <div class="col-md-3 col-6 mb-2">
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-file-alt me-2"></i>Apply Now
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="{{ route('admissions.requirements') }}" class="btn btn-outline-success w-100">
                        <i class="fas fa-list-check me-2"></i>Requirements
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="{{ route('admissions.programs') }}" class="btn btn-outline-info w-100">
                        <i class="fas fa-graduation-cap me-2"></i>Programs
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="{{ route('admissions.faq') }}" class="btn btn-outline-warning w-100">
                        <i class="fas fa-question-circle me-2"></i>FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-dark {
        background: linear-gradient(135deg, #434343 0%, #000000 100%);
    }
    
    .list-group-item-action:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }
    
    .fab {
        transition: transform 0.3s ease;
    }
    
    .fab:hover {
        transform: scale(1.2);
    }
</style>
@endpush

@push('scripts')
<script>
    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show success message
        alert('Thank you for your message! We will respond within 1-2 business days.');
        
        // Reset form
        this.reset();
    });
    
    function startLiveChat() {
        alert('Live chat will open in a new window (feature coming soon)');
    }
    
    function openMap() {
        window.open('https://maps.google.com/?q=IntelliCampus+University', '_blank');
    }
</script>
@endpush
@endsection