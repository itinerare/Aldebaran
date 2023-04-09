<x-mail::message>

Thank you for requesting a {{ $commission->type->name }} commission. Unfortunately, I am unable to accept your request at this time. For further information, please see your request's page, accessible via the button below.
Your interest is appreciated, and I hope you consider commissioning me in the future!

<x-mail::button :url="$commission->url" color="success">
    View Commission Request
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $commission, 'subject' => 'commission', 'subjectTitle' => 'Commission Request'])

</x-mail::message>
