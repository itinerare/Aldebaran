<x-mail::message>

{{ $commission->commissioner->email }} has paid an invoice for commission #{{ $commission->id }} ({{ $commission->type->category->name }}: {{ $commission->type->name }})!

<x-mail::button :url="$commission->adminUrl" color="success">
    View Commission
</x-mail::button>

<hr /><br />

If you're having trouble clicking the "View Commission" button, copy and paste the following URL into your web browser: <a href="{{ $commission->adminUrl }}">{{ $commission->adminUrl }}</a>

</x-mail::message>
