<x-mail::message>

Thank you for requesting a {{ $quote->type->name }} quote! Your request has been placed in the queue for consideration{{ $quote->commissioner->receive_notifications ? ', and you will be notified when it is updated' : '' }}. You may view the status of your request and any additional information at any time via its page, accessible by clicking the button below.

<x-mail::button :url="$quote->url" color="success">
    View Quote Request
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $quote, 'subject' => 'quote', 'subjectTitle' => 'Quote Request'])

</x-mail::message>
