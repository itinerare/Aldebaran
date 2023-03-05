<x-mail::message>

You are receiving this email because you requested a subscription to the {{ $subscriber->mailingList->name }} mailing list. If you did not request this subscription, feel free to disregard this email.

<x-mail::button :url="$subscriber->verifyUrl" color="success">
    Verify Address
</x-mail::button>

<hr /><br />

If you're having trouble clicking the "Verify Address" button, copy and paste the following URL into your web browser: <a href="{{ $subscriber->verifyUrl }}">{{ $subscriber->verifyUrl }}</a>

</x-mail::message>
