<x-mail::message>

Thank you for requesting a {{ $quote->type->name }} quote! I am pleased to inform you that your request has been accepted and placed in the active queue. You may view the status of your quote and any additional information at any time via its page, accessible by clicking the button below.
Note that you will be contacted separately if any further information is required. You may also be notified as your quote progresses at my discretion.

<x-mail::button :url="$quote->url" color="success">
    View Quote
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $quote, 'subject' => 'quote', 'subjectTitle' => 'Quote'])

</x-mail::message>
