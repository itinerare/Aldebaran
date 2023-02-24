<x-mail::message>

{!! $entry->text !!}

<hr /><br />

You're receiving this message because you are subscribed to the {{ $entry->mailingList->name }} mailing list. To unsubscribe, click <a href="{{ $subscriber->unsubscribeUrl }}">here</a> or visit <a href="{{ $subscriber->unsubscribeUrl }}">{{ $subscriber->unsubscribeUrl }}</a> in your web browser.

</x-mail::message>
