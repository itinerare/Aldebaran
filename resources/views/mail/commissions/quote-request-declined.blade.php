<x-mail::message>

Thank you for requesting a {{ $quote->type->name }} quote. Unfortunately, I am unable to accept your request at this time. For further information, please see your request's page, accessible via the button below.
Your interest is appreciated, and I hope you consider commissioning me in the future!

<x-mail::button :url="$quote->url" color="success">
    View Quote Request
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $quote, 'subject' => 'quote', 'subjectTitle' => 'Quote Request'])

</x-mail::message>
