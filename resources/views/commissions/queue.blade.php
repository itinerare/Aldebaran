@extends('layouts.app')

@section('title')
    {{ $class->name }} Queue
@endsection

@section('content')
    {!! breadcrumbs([
        $class->name . ' Commissions' => 'commissions/' . $class->slug,
        'Queue' => 'commissions/' . $class->slug . '/queue',
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            {{ $class->name }} Queue
            <div class="float-right ml-2">
                <a class="btn btn-secondary" href="{{ url('commissions/' . $class->slug) }}">Back to Commission Info</a>
            </div>
        </h1>
    </div>

    <p>These are the commissions currently in my {{ strtolower($class->name) }} queue. Only accepted commissions are
        shown, so pending requests (such as those newly submitted) will not appear.</p>

    @if ($commissions->count())
        @foreach ($commissions as $commission)
            <div class="card card-body mb-4">
                <div class="borderhr">
                    <h3>{{ $loop->iteration }} ・ {!! $commission->type->displayName !!}</h3>
                    <p>
                        Progress: {{ $commission->progress }} ・
                        Submitted {!! $commission->created_at->toFormattedDateString() !!} ・
                        Last updated {!! $commission->updated_at->toFormattedDateString() !!}
                    </p>
                </div>
            </div>
        @endforeach
    @else
        <p>The queue is currently empty.</p>
    @endif

@endsection
