<x-mail::message>

{{ $commission->commissioner->email }} has submitted a new request for a {{ $commission->type->name }} {{ $commission->type->category->name }} ({{ $commission->type->category->class->name }}) commission.<br />
This commissioner has commissioned {!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} time{!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!} previously.

<x-mail::button :url="$commission->adminUrl" color="success">
    View Request
</x-mail::button>

<hr /><br />

If you're having trouble clicking the "View Request" button, copy and paste the following URL into your web browser: <a href="{{ $commission->adminUrl }}">{{ $commission->adminUrl }}</a>

</x-mail::message>
