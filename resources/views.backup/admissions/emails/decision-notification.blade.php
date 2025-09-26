{{-- File: resources/views/admissions/emails/decision-notification.blade.php --}}
@component('mail::message')
@if($application->decision === 'admit')
# 🎉 Congratulations! You've Been Admitted!
@elseif($application->decision === 'conditional_admit')
# Conditional Admission Offer
@elseif($application->decision === 'waitlist')
# Waitlist Notification
@elseif($application->decision === 'deny')
# Admission Decision
@elseif($application->decision === 'defer')
# Application Deferred
@endif

Dear {{ $application->first_name }} {{ $application->last_name }},

@if($application->decision === 'admit')
We are delighted to inform you that you have been **admitted** to the **{{ $application->program->name }}** program at {{ config('app.name') }} for the **{{ $application->term->name }}** term!

Your academic achievements, personal qualities, and potential for success have impressed our admissions committee. We believe you will make valuable contributions to our academic community.

@elseif($application->decision === 'conditional_admit')
We are pleased to offer you **conditional admission** to the **{{ $application->program->name }}** program at {{ config('app.name') }} for the **{{ $application->term->name }}** term.

Your admission is contingent upon fulfilling the following conditions:

@component('mail::panel')
@foreach($conditions as $index => $condition)
{{ $index + 1 }}. {{ $condition }}
@endforeach
@endcomponent

@elseif($application->decision === 'waitlist')
After careful review of your application, the admissions committee has placed you on the **waitlist** for the **{{ $application->program->name }}** program for the **{{ $application->term->name }}** term.

@component('mail::panel')
**Waitlist Position:** {{ $waitlist_position ?? 'Not ranked' }}  
**Waitlist Status:** Active  
**Notification Date:** We will notify you by {{ $notification_date?->format('F j, Y') ?? 'May 1, 2025' }}
@endcomponent

@elseif($application->decision === 'deny')
After careful consideration of your application for the **{{ $application->program->name }}** program, we regret to inform you that we are unable to offer you admission for the **{{ $application->term->name }}** term.

This decision was particularly difficult as we received many qualified applications for a limited number of spaces. Please know that this decision does not diminish your accomplishments or potential for success.

@elseif($application->decision === 'defer')
After reviewing your application for the **{{ $application->program->name }}** program, the admissions committee has decided to **defer** your application to the **{{ $deferred_term->name ?? 'next admission term' }}**.

Your application will be automatically reconsidered with the deferred term's applicant pool. No additional action is required from you at this time.
@endif

## Application Details

@component('mail::table')
| Information | Details |
|:------------|:--------|
| **Application Number** | {{ $application->application_number }} |
| **Program** | {{ $application->program->name }} |
| **Degree Type** | {{ $application->program->degree_type }} |
| **Term** | {{ $application->term->name }} |
| **Decision Date** | {{ $application->decision_date->format('F j, Y') }} |
@endcomponent

@if(in_array($application->decision, ['admit', 'conditional_admit']))
## Next Steps

### 1. Accept Your Offer
You must confirm your enrollment by **{{ $enrollment_deadline?->format('F j, Y') ?? 'May 1, 2025' }}**

@component('mail::button', ['url' => route('enrollment.confirm', ['uuid' => $application->application_uuid])])
Accept Admission Offer
@endcomponent

### 2. Pay Enrollment Deposit
Secure your spot with a **{{ config('admissions.enrollment_deposit', '$500') }}** enrollment deposit

@component('mail::panel')
**Deposit Deadline:** {{ $deposit_deadline?->format('F j, Y') ?? 'May 15, 2025' }}  
**Amount:** {{ config('admissions.enrollment_deposit', '$500') }}  
**Refundable:** {{ config('admissions.deposit_refundable') ? 'Yes, until ' . $refund_deadline?->format('F j') : 'Non-refundable' }}
@endcomponent

### 3. Complete Enrollment Checklist

@component('mail::table')
| Task | Deadline | Status |
|:-----|:---------|:------:|
| Accept Offer | {{ $enrollment_deadline?->format('M j') }} | ⏳ Pending |
| Pay Deposit | {{ $deposit_deadline?->format('M j') }} | ⏳ Pending |
| Submit Final Transcripts | {{ $transcript_deadline?->format('M j') ?? 'Jul 1' }} | ⏳ Pending |
| Complete Health Forms | {{ $health_deadline?->format('M j') ?? 'Jul 15' }} | ⏳ Pending |
| Register for Orientation | {{ $orientation_deadline?->format('M j') ?? 'Jul 30' }} | ⏳ Pending |
| Apply for Housing | {{ $housing_deadline?->format('M j') ?? 'Jun 1' }} | ⏳ Optional |
@endcomponent

## Financial Aid & Scholarships

@if($financial_aid_eligible ?? false)
**Good News!** You may be eligible for financial aid and scholarships.

@component('mail::button', ['url' => route('financial-aid.apply'), 'color' => 'success'])
Apply for Financial Aid
@endcomponent
@endif

## Important Dates

@component('mail::panel')
📅 **Orientation:** {{ $orientation_date?->format('F j-k, Y') ?? 'August 20-22, 2025' }}  
📅 **Move-in Day:** {{ $move_in_date?->format('F j, Y') ?? 'August 25, 2025' }}  
📅 **Classes Begin:** {{ $classes_start?->format('F j, Y') ?? 'August 28, 2025' }}
@endcomponent

@elseif($application->decision === 'waitlist')
## Waitlist Information

### What This Means
- You meet our admission requirements
- Space availability is limited
- You will be considered if spots become available
- No additional documents are required

### Your Options

1. **Remain on Waitlist**
   - No action required
   - We'll notify you if a spot opens
   - Decision by {{ $notification_date?->format('F j, Y') }}

2. **Submit Letter of Continued Interest**
   - Reaffirm your interest
   - Update achievements
   - Provide new information

@component('mail::button', ['url' => route('admissions.waitlist.interest', ['uuid' => $application->application_uuid])])
Submit Letter of Interest
@endcomponent

3. **Withdraw from Waitlist**
   - Remove yourself from consideration
   - Pursue other opportunities

@elseif($application->decision === 'deny')
## Other Options to Consider

While this particular program may not be the right fit at this time, we encourage you to:

- Consider applying for a different program
- Gain additional experience and reapply in the future
- Explore our non-degree and certificate programs
- Consider our partner institutions

@component('mail::button', ['url' => route('programs.explore')])
Explore Other Programs
@endcomponent

## Request Feedback

You may request feedback on your application to understand areas for improvement:

@component('mail::button', ['url' => route('admissions.feedback.request', ['uuid' => $application->application_uuid]), 'color' => 'primary'])
Request Application Feedback
@endcomponent

@elseif($application->decision === 'defer')
## Deferral Information

### What Happens Next
- Your application remains active
- No additional application fee required
- You may update your application with new information
- Decision expected by {{ $deferred_decision_date?->format('F j, Y') }}

### Update Your Application
Submit any new achievements, test scores, or documents:

@component('mail::button', ['url' => route('admissions.update', ['uuid' => $application->application_uuid])])
Update Application
@endcomponent
@endif

## Contact Admissions Office

@if(in_array($application->decision, ['admit', 'conditional_admit']))
Our team is here to help you with the enrollment process:

**Enrollment Services**  
📧 Email: enrollment@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}  
📞 Phone: {{ config('admissions.enrollment_phone', '+1 (555) 123-4570') }}  
💬 Live Chat: Available on our portal  
@else
If you have questions about your admission decision:

**Admissions Office**  
📧 Email: admissions@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}  
📞 Phone: {{ config('admissions.contact_phone', '+1 (555) 123-4567') }}  
@endif
🕒 Office Hours: Monday - Friday, 9:00 AM - 5:00 PM

@if($application->decision === 'admit')
## Join Our Community

Connect with fellow admitted students:

@component('mail::panel')
🌐 **Facebook Group:** {{ config('app.name') }} Class of {{ $application->term->graduation_year }}  
📱 **WhatsApp:** Join orientation group chat  
🔗 **Discord:** Student community server  
📧 **Newsletter:** Weekly updates for new students
@endcomponent
@endif

## Official Admission Letter

Your official admission letter is attached to this email. You can also download it from your portal:

@component('mail::button', ['url' => route('admissions.letter', ['uuid' => $application->application_uuid]), 'color' => 'primary'])
Download Official Letter
@endcomponent

---

@if($application->decision === 'admit')
Congratulations once again on your admission! We look forward to welcoming you to the {{ config('app.name') }} family.
@elseif($application->decision === 'conditional_admit')
We look forward to welcoming you upon successful completion of the stated conditions.
@elseif($application->decision === 'waitlist')
Thank you for your patience as we finalize our admissions decisions.
@elseif($application->decision === 'deny')
We wish you the very best in your future academic endeavors.
@elseif($application->decision === 'defer')
We look forward to reconsidering your application for the {{ $deferred_term->name ?? 'deferred term' }}.
@endif

Sincerely,

**{{ config('admissions.director_name', 'Dr. Jane Smith') }}**  
Director of Admissions  
{{ config('app.name') }}

@component('mail::subcopy')
**Important:** This email serves as unofficial notification. Your official admission decision letter is attached and available in your application portal.

Application Reference: {{ $application->application_uuid }}  
Decision Reference: DEC-{{ strtoupper(substr($application->application_uuid, 0, 8)) }}-{{ $application->decision_date->format('Ymd') }}

This communication is confidential and intended solely for {{ $application->first_name }} {{ $application->last_name }}.
@endcomponent
@endcomponent