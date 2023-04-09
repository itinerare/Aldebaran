<x-mail::message>

Your quote has been updated! To see more information about your quote, please view its page, accessible via the button below. Thank you again for requesting a quote from me!

<x-mail::button :url="$quote->url" color="success">
    View Quote
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $quote, 'subject' => 'quote', 'subjectTitle' => 'Quote'])

</x-mail::message>
