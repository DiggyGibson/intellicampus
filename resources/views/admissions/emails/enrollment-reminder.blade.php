{{-- File: resources/views/admissions/emails/enrollment-reminder.blade.php --}}
@component('mail::message')
# ⏰ Enrollment Deadline Reminder

Dear {{ $application->first_name }} {{ $application->last_name }},

@if($reminder_type === 'first')
Congratulations again on your admission to **{{ $application->program->name }}** at {{ config('app.name') }}!

This is a friendly reminder that you have **{{ $days_remaining }} days** remaining to confirm your enrollment for the **{{ $application->term->name }}** term.
@elseif($reminder_type === 'urgent')
**⚠️ URGENT: Only {{ $days_remaining }} days left to secure your spot!**

We haven't received your enrollment confirmation for the **{{ $application->program->name }}** program yet. Please take action soon to secure your place in the **{{ $application->term->name }}** class.
@elseif($reminder_type === 'final')
**🚨 FINAL NOTICE: Your enrollment deadline is {{ $days_remaining <= 1 ? 'TOMORROW' : 'in ' . $days_remaining . ' days' }}!**

This is your final reminder to confirm your enrollment in the **{{ $application->program->name }}** program. After the deadline, your admission offer may be withdrawn and your spot offered to another student.
@endif

## Enrollment Status

@component('mail::panel')
📋 **Your Current Status**

**Admission Decision:** ✅ Admitted  
**Enrollment Confirmation:** {{ $enrollment_confirmed ? '✅ Confirmed' : '⏳ Pending' }}  
**Enrollment Deposit:** {{ $deposit_paid ? '✅ Paid' : '⏳ Not Received' }}  
**Deadline:** {{ $enrollment_deadline->format('F j, Y \a\t 11:59 PM') }}  
**Time Remaining:** {{ $days_remaining }} days, {{ $hours_remaining }} hours
@endcomponent

@if(!$enrollment_confirmed)
## How to Confirm Your Enrollment

Complete these steps to secure your place:

### Step 1: Accept Your Offer
Click the button below to officially accept your admission offer:

@component('mail::button', ['url' => route('enrollment.confirm', ['uuid' => $application->application_uuid])])
Accept Admission Offer Now
@endcomponent

### Step 2: Pay Your Enrollment Deposit
@if(!$deposit_paid)
Secure your spot with the enrollment deposit:

@component('mail::table')
| Deposit Information | Details |
|:-------------------|:--------|
| **Amount Due** | {{ config('admissions.enrollment_deposit', '$500.00') }} |
| **Payment Deadline** | {{ $deposit_deadline->format('F j, Y') }} |
| **Payment Methods** | Credit Card, Bank Transfer, Check |
| **Refund Policy** | {{ config('admissions.deposit_refundable') ? 'Refundable until ' . $refund_deadline?->format('F j') : 'Non-refundable' }} |
@endcomponent

@component('mail::button', ['url' => route('enrollment.deposit', ['uuid' => $application->application_uuid]), 'color' => 'success'])
Pay Deposit Now
@endcomponent
@endif
@endif

## Enrollment Checklist

Track your progress with this comprehensive checklist:

@component('mail::table')
| Required Item | Status | Deadline |
|:-------------|:------:|:---------|
| Accept Admission Offer | {{ $enrollment_confirmed ? '✅' : '⏳' }} | {{ $enrollment_deadline->format('M j') }} |
| Pay Enrollment Deposit | {{ $deposit_paid ? '✅' : '⏳' }} | {{ $deposit_deadline->format('M j') }} |
| Submit Final Transcripts | {{ $transcripts_received ? '✅' : '⏳' }} | {{ $transcript_deadline?->format('M j') ?? 'Jul 1' }} |
| Complete Health Forms | {{ $health_forms_complete ? '✅' : '⏳' }} | {{ $health_deadline?->format('M j') ?? 'Jul 15' }} |
| Submit Immunization Records | {{ $immunization_complete ? '✅' : '⏳' }} | {{ $immunization_deadline?->format('M j') ?? 'Jul 15' }} |
| Register for Orientation | {{ $orientation_registered ? '✅' : '⏳' }} | {{ $orientation_deadline?->format('M j') ?? 'Jul 30' }} |
| Apply for Housing | {{ $housing_applied ? '✅' : 'Optional' }} | {{ $housing_deadline?->format('M j') ?? 'Jun 1' }} |
| Set up Student Account | {{ $account_created ? '✅' : '⏳' }} | Before Orientation |
| Apply for Parking Permit | Optional | {{ $parking_deadline?->format('M j') ?? 'Aug 1' }} |
@endcomponent

@if($days_remaining <= 7)
## ⚠️ Why This Is Important

**If you miss the enrollment deadline:**
- Your admission offer will be withdrawn
- Your spot will be offered to a waitlisted student
- You'll need to reapply for future terms
- Any deposits paid may be forfeited
- You'll lose priority for housing and course registration
@endif

## Next Steps After Enrollment

Once you confirm your enrollment, you'll gain access to:

@component('mail::panel')
✨ **New Student Benefits**
- Early course registration
- Housing selection priority  
- Orientation registration
- Student email account
- Campus facilities access
- Financial aid disbursement
- Student ID card processing
- Parking permit eligibility
@endcomponent

## Important Upcoming Dates

Mark your calendar with these key dates:

@component('mail::table')
| Event | Date | Required |
|:------|:-----|:--------:|
| **Enrollment Deadline** | {{ $enrollment_deadline->format('F j, Y') }} | ✅ Yes |
| **Housing Application Opens** | {{ $housing_open_date?->format('F j') ?? 'May 1' }} | Optional |
| **Course Registration** | {{ $registration_date?->format('F j-k') ?? 'July 15-20' }} | ✅ Yes |
| **Orientation Program** | {{ $orientation_dates?->format('F j-k') ?? 'August 20-22' }} | ✅ Yes |
| **Move-in Day** | {{ $move_in_date?->format('F j') ?? 'August 25' }} | If on campus |
| **First Day of Classes** | {{ $classes_start?->format('F j') ?? 'August 28' }} | ✅ Yes |
@endcomponent

## Financial Planning

@if(!$deposit_paid)
### Payment Options Available

We offer several convenient payment methods:

1. **Online Payment** (Recommended)
   - Credit/Debit Card
   - Electronic Check
   - Payment Plan Available

2. **By Mail**
   - Personal Check
   - Money Order
   - Cashier's Check

3. **In Person**
   - Visit the Bursar's Office
   - Cash, Check, or Card accepted
@endif

### Estimated Costs for {{ $application->term->name }}

@component('mail::table')
| Expense Category | Estimated Amount |
|:----------------|----------------:|
| Tuition & Fees | {{ config('admissions.tuition_estimate', '$15,000') }} |
| Room & Board | {{ config('admissions.room_board_estimate', '$10,000') }} |
| Books & Supplies | {{ config('admissions.books_estimate', '$1,200') }} |
| Personal Expenses | {{ config('admissions.personal_estimate', '$2,000') }} |
| **Total Estimate** | **{{ config('admissions.total_estimate', '$28,200') }}** |
@endcomponent

Financial aid packages will be sent separately to admitted students who have completed the FAFSA.

## Need More Time?

If you need an extension or have special circumstances:

@component('mail::button', ['url' => route('enrollment.extension.request', ['uuid' => $application->application_uuid]), 'color' => 'primary'])
Request Deadline Extension
@endcomponent

**Note:** Extension requests must be submitted at least 48 hours before the deadline and are granted only in exceptional circumstances.

## Frequently Asked Questions

**Q: What if I'm waiting for other admission decisions?**  
A: You can request a deadline extension, but we can only hold your spot until {{ $final_extension_date?->format('F j') ?? 'May 15' }}.

**Q: Can I defer my enrollment to a later term?**  
A: Yes, deferral requests can be submitted through your portal. Approval is subject to program availability.

**Q: Is the enrollment deposit refundable?**  
A: {{ config('admissions.deposit_refund_policy', 'The deposit is refundable until June 1st if you notify us in writing.') }}

**Q: What if I change my mind after confirming?**  
A: Please notify us immediately. Withdrawal policies and refund schedules apply based on the date of withdrawal.

## Get Help

Our enrollment team is ready to assist you:

**Enrollment Services**  
📧 Email: enrollment@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}  
📞 Phone: {{ config('admissions.enrollment_phone', '+1 (555) 123-4570') }}  
💬 Live Chat: [Available on Portal]({{ route('enrollment.chat') }})  
🕒 Extended Hours This Week: 8:00 AM - 7:00 PM

**Virtual Enrollment Help Sessions**  
Join our daily Zoom sessions for enrollment assistance:
- Monday-Friday: 2:00 PM & 6:00 PM
- Saturday: 10:00 AM
- [Join Session]({{ route('enrollment.help-session') }})

## Stay Connected

Join our admitted students community:

@component('mail::panel')
**Connect with Your Future Classmates**

🔵 [Facebook Group]({{ config('social.admitted_students_facebook') }}): {{ config('app.name') }} Class of {{ $graduation_year }}  
💬 [Discord Server]({{ config('social.admitted_students_discord') }}): Chat with current students  
📧 [Parent Newsletter]({{ route('parents.newsletter') }}): Monthly updates for families  
📱 [Download Our App]({{ config('app.mobile_app_url') }}): Stay updated on the go
@endcomponent

---

@if($reminder_type === 'final')
**This is your FINAL REMINDER.** Don't miss this opportunity to join our community. We're excited to welcome you to {{ config('app.name') }}!
@else
We're looking forward to welcoming you to the {{ config('app.name') }} family. Don't let this opportunity pass by!
@endif

Warm regards,

**{{ config('admissions.enrollment_coordinator', 'Michael Thompson') }}**  
Enrollment Coordinator  
Office of Admissions  
{{ config('app.name') }}

@component('mail::subcopy')
**Reference Information:**
- Application ID: {{ $application->application_uuid }}
- Admitted Program: {{ $application->program->name }}
- Term: {{ $application->term->name }}
- Reminder Type: {{ ucfirst($reminder_type) }} Notice
- Sent: {{ now()->format('F j, Y \a\t g:i A') }}

To unsubscribe from enrollment reminders, [click here]({{ route('enrollment.reminders.unsubscribe', ['uuid' => $application->application_uuid]) }}). Note: You will still receive critical deadline notifications.

This email is intended for {{ $application->first_name }} {{ $application->last_name }}. If you received this in error, please disregard.
@endcomponent
@endcomponent