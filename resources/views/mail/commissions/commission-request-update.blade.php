<x-mail::message>

Your commission has been updated! Its current progress is now "{{ $commission->progress }}"{{ $commission->pieces->count() ? ',' : '.' }}
@if ($commission->pieces->count())
    and it has {{ $commission->pieces->count() }} piece{{ $commission->pieces->count() == 1 ? '' : 's' }} associated with it.
@endif
@if (!$commission->paidStatus)
    Please also note that as of the time of this email, your commission is marked <strong>unpaid</strong>, and you will be contacted separately about payment if/when relevant.
@endif

To see more information about your commission, please view its page, accessible via the button below. Thank you again for commissioning me!

<x-mail::button :url="$commission->url" color="success">
    View Commission
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $commission, 'subject' => 'commission', 'subjectTitle' => 'Commission'])

</x-mail::message>
