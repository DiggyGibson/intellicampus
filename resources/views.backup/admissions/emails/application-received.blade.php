{{-- File: resources/views/admissions/emails/application-received.blade.php --}}
@component('mail::message')
# Application Successfully Received

Dear {{ $application->first_name }} {{ $application->last_name }},

Thank you for submitting your application to **{{ config('app.name') }}** for the **{{ $application->program->name }}** program.

## Application Details

@component('mail::panel')
- **Application Number:** {{ $application->application_number }}
- **Program:** {{ $application->program->name }}
- **Term:** {{ $application->term->name }}
- **Submitted Date:** {{ $application->submitted_at->format('F j, Y g:i A') }}
- **Application Type:** {{ ucfirst(str_replace('_', ' ', $application->application_type)) }}
@endcomponent

## What Happens Next?

1. **Document Review** - Our admissions team will review your submitted documents within 3-5 business days
2. **Academic Evaluation** - Your academic credentials will be evaluated by the department
3. **Committee Review** - Complete applications are reviewed by the admissions committee
4. **Decision** - You will receive an admission decision by **{{ $application->term->decision_release_date?->format('F j, Y') ?? 'the announced date' }}**

## Application Status

You can check your application status at any time using your application number:

@component('mail::button', ['url' => route('admissions.status', ['uuid' => $application->application_uuid])])
Check Application Status
@endcomponent

## Required Documents Checklist

Please ensure you have submitted all required documents:

@component('mail::table')
| Document | Status |
|:---------|:------:|
| Application Form | ✅ Completed |
| Personal Statement | {{ $application->personal_statement ? '✅ Received' : '⏳ Pending' }} |
| Academic Transcripts | {{ $application->documents()->where('document_type', 'transcript')->exists() ? '✅ Received' : '⏳ Pending' }} |
| Test Scores | {{ $application->test_scores ? '✅ Received' : '⏳ Pending' }} |
| Recommendation Letters | {{ $application->references ? '✅ Received' : '⏳ Pending' }} |
| Application Fee | {{ $application->application_fee_paid ? '✅ Paid' : '⏳ Pending' }} |
@endcomponent

@if(!$application->application_fee_paid)
## Application Fee Payment

Your application fee of **{{ config('admissions.application_fee_amount', '$50.00') }}** is still pending. Please complete the payment to ensure your application is processed.

@component('mail::button', ['url' => route('admissions.pay-fee', ['uuid' => $application->application_uuid])])
Pay Application Fee
@endcomponent
@endif

## Important Reminders

- Keep your **Application Number** ({{ $application->application_number }}) safe for future reference
- Check your email regularly for updates and document requests
- Respond promptly to any requests for additional information
- Ensure all documents are authentic and verifiable
- Update us if your contact information changes

## Contact Information

If you have any questions about your application, please contact us:

**Admissions Office**  
📧 Email: admissions@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}  
📞 Phone: {{ config('admissions.contact_phone', '+1 (555) 123-4567') }}  
🕒 Office Hours: Monday - Friday, 9:00 AM - 5:00 PM

## Stay Connected

Follow us on social media for updates and important announcements:

@component('mail::subcopy')
[Facebook]({{ config('social.facebook') }}) | 
[Twitter]({{ config('social.twitter') }}) | 
[LinkedIn]({{ config('social.linkedin') }}) | 
[Instagram]({{ config('social.instagram') }})
@endcomponent

---

Thank you for choosing {{ config('app.name') }} for your academic journey. We look forward to reviewing your application.

Best regards,

**{{ config('admissions.director_name', 'Dr. Jane Smith') }}**  
Director of Admissions  
{{ config('app.name') }}

@component('mail::subcopy')
This is an automated message. Please do not reply directly to this email. For assistance, contact admissions@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}

Your application ID for reference: {{ $application->application_uuid }}
@endcomponent
@endcomponent