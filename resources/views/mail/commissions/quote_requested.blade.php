<x-mail::message>

{{ $quote->commissioner->email }} has submitted a new request for a {{ $quote->type->name }} {{ $quote->type->category->name }} ({{ $quote->type->category->class->name }}) quote.<br />
This commissioner has commissioned {!! $quote->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} time{!! $quote->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!} previously.

<x-mail::button :url="$quote->adminUrl" color="success">
    View Quote Request
</x-mail::button>

<hr /><br />

If you're having trouble clicking the "View Quote Request" button, copy and paste the following URL into your web browser: <a href="{{ $quote->adminUrl }}">{{  $quote->adminUrl }}</a>

</x-mail::message>
