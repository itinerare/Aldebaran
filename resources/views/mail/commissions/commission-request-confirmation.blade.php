<x-mail::message>

Thank you for requesting a {{ $commission->type->name }} commission! Your request has been placed in the queue for consideration{{ $commission->commissioner->receive_notifications ? ', and you will be notified when it is updated' : '' }}. You may view the status of your request and any additional information at any time via its page, accessible by clicking the button below.

<x-mail::button :url="$commission->url" color="success">
    View Commission Request
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $commission, 'subject' => 'commission', 'subjectTitle' => 'Commission Request'])

</x-mail::message>
