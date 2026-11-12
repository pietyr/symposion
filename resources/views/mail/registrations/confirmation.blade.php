<x-mail::message>
# {{ __('Your registration') }}

{{ __('We have received your registration for :title.', ['title' => $title]) }}
{{ $confirmed ? __('The registration is confirmed.') : __('The registration will be confirmed once the payment arrives.') }}

<x-mail::table>
| {{ __('Registration details') }} | |
|:--|:--|
| {{ __('Date') }} | {{ $dates }} |
@if ($place)
| {{ __('Venue') }} | {{ $place }} |
@endif
| {{ __('Attendance') }} | {{ $attendance }} |
| {{ __('Amount') }} | {{ $amount }} |
</x-mail::table>

@if ($transfer)
<x-mail::table>
| {{ __('Transfer details') }} | |
|:--|:--|
| {{ __('Recipient') }} | {{ $transfer['recipient'] }} |
| {{ __('Account number') }} | {{ $transfer['account_number'] }} |
| {{ __('Amount') }} | {{ $amount }} |
| {{ __('Transfer title') }} | {{ $transfer['title'] }} |
</x-mail::table>
@endif

<x-mail::button :url="$eventUrl">
{{ __('Go to the event') }}
</x-mail::button>

{{ __('Regards,') }}<br>
{{ config('app.name') }}
</x-mail::message>
