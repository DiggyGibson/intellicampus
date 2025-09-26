{{-- File: resources/views/admissions/emails/document-request.blade.php --}}
@component('mail::message')
# Additional Documents Required

Dear {{ $application->first_name }} {{ $application->last_name }},

We are currently reviewing your application for admission to **{{ $application->program->name }}** at {{ config('app.name') }}.

To complete your application review, we require the following additional documents:

## Required Documents

@component('mail::panel')
@foreach($required_documents as $document)
### {{ $loop->iteration }}. {{ $document['type_display'] }}

{{ $document['description'] }}

@if(isset($document['specific_requirements']))
**Specific Requirements:**
@foreach($document['specific_requirements'] as $requirement)
- {{ $requirement }}
@endforeach
@endif

**Deadline:** {{ $document['deadline']?->format('F j, Y') ?? 'As soon as possible' }}
@endforeach
@endcomponent

## How to Submit Documents

You can upload the required documents through your application portal:

@component('mail::button', ['url' => route('admissions.documents', ['uuid' => $application->application_uuid])])
Upload Documents Now
@endcomponent

### Alternative Submission Methods

If you are unable to upload documents online, you may:

1. **Email:** Send scanned copies to documents@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
2. **Mail:** Send certified copies to:
   ```
   Admissions Office
   {{ config('app.name') }}
   {{ config('admissions.mailing_address_line1') }}
   {{ config('admissions.mailing_address_line2') }}
   ```

## Document Guidelines

Please ensure all submitted documents meet the following requirements:

@component('mail::table')
| Requirement | Details |
|:------------|:--------|
| **Format** | PDF, JPG, or PNG (max 10MB per file) |
| **Quality** | Clear, legible scans or photos |
| **Language** | English or certified translation |
| **Authenticity** | Original or certified copies only |
| **Naming** | Include your application number in filename |
@endcomponent

## Important Information

⚠️ **Deadline Alert**

@if($deadline_date)
@component('mail::panel')
**All documents must be received by {{ $deadline_date->format('F j, Y \a\t g:i A') }}**

Time remaining: **{{ $deadline_date->diffForHumans() }}**
@endcomponent
@else
Please submit the requested documents as soon as possible to avoid delays in processing your application.
@endif

## Application Status Update

@component('mail::table')
| Item | Status |
|:-----|:------:|
| **Application Number** | {{ $application->application_number }} |
| **Current Status** | {{ ucfirst(str_replace('_', ' ', $application->status)) }} |
| **Documents Received** | {{ $application->documents()->where('status', 'verified')->count() }} |
| **Documents Pending** | {{ count($required_documents) }} |
| **Review Stage** | {{ $application->review_stage ?? 'Initial Review' }} |
@endcomponent

## Frequently Asked Questions

**Q: What if I cannot obtain a document before the deadline?**  
A: Please contact us immediately at admissions@{{ str_replace(['http://', 'https://'], '', config('app.url')) }} to discuss alternatives.

**Q: Can I submit unofficial documents temporarily?**  
A: Unofficial documents may be accepted for initial review, but official documents are required for final admission.

**Q: How will I know when my documents are received?**  
A: You will receive an email confirmation within 24-48 hours of document receipt.

**Q: What if my documents are in another language?**  
A: All documents must be accompanied by certified English translations from an approved translation service.

## Need Help?

Our admissions team is here to assist you:

**Document Support Team**  
📧 Email: documents@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}  
📞 Phone: {{ config('admissions.document_support_phone', '+1 (555) 123-4568') }}  
💬 Live Chat: Available on our website  
🕒 Hours: Monday - Friday, 9:00 AM - 5:00 PM

## Track Your Application

Monitor your application status and document checklist:

@component('mail::button', ['url' => route('admissions.status', ['uuid' => $application->application_uuid]), 'color' => 'success'])
View Application Status
@endcomponent

---

Thank you for your prompt attention to this request. We look forward to completing the review of your application.

Best regards,

**{{ config('admissions.document_team_lead', 'Sarah Johnson') }}**  
Document Processing Team  
Admissions Office  
{{ config('app.name') }}

@component('mail::subcopy')
**Reference Information:**
- Application ID: {{ $application->application_uuid }}
- Request Date: {{ now()->format('F j, Y') }}
- Request Reference: DOC-{{ strtoupper(substr($application->application_uuid, 0, 8)) }}

This is an automated message. Please do not reply directly to this email. For document-related inquiries, contact documents@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
@endcomponent
@endcomponent