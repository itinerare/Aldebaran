<p>{{ $commission->commissioner->email }} has submitted a new request for a {{ $commission->type->name }}
    {{ $commission->type->category->name }} ({{ $commission->type->category->class->name }}) commission. <a href="{{ url('admin/commissions/edit/' . $commission->id) }}">Click here to view information about this
        request</a>.<br />
    This commissioner has commissioned {!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} time{!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!} previously.</p>

<hr />

<p>There are currently
    {{ $commission->class($commission->type->category->class->id)->where('status', 'Pending')->count() }} commission
    request{{ $commission->class($commission->type->category->class->id)->where('status', 'Pending')->count() > 1? 's': '' }}
    in the {{ $commission->type->category->class->name }} queue. <a href="{{ url('admin/commissions/' . $commission->type->category->class->slug) }}">Click here to view the
        queue</a>.</p>
