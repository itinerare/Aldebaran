@extends('layouts.app')

@section('content')

    @if ($page)
        {!! $page->text !!}
    @else
        <p>Please finish initial site setup!</p>
    @endif

    @if (config('aldebaran.settings.commissions.enabled'))
        @foreach ($commissionClasses as $class)
            <div class="card mb-4">
                <div class="card-header">
                    <h2>{{ ucfirst($class->name) }} Commissions ・ @if (Settings::get($class->slug . '_comms_open') == 1)
                            <span class="text-success">Open!</span>
                        @else
                            Closed
                        @endif
                    </h2>
                    @if (Settings::get($class->slug . '_status'))
                        <h6>{{ Settings::get($class->slug . '_status') }}</h6>
                    @endif
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-md mb-2"><a href="{{ url('commissions/' . $class->slug . '/tos') }}" class="btn btn-primary">Terms of Service</a></div>
                        <div class="col-md mb-2"><a href="{{ url('commissions/' . $class->slug) }}" class="btn @if (Settings::get($class->slug . '_comms_open') == 1) btn-success @else btn-primary @endif">Commission
                                Information</a></div>
                        <div class="col-md mb-2"><a href="{{ url('commissions/' . $class->slug . '/queue') }}" class="btn btn-primary">Queue Status</a></div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if (config('aldebaran.settings.email_features') && Settings::get('display_mailing_lists') && $mailingLists->count())
        <div class="card mb-4">
            <div class="card-header">
                <h4>Mailing Lists</h4>
            </div>
            <div class="card-body">
                @foreach ($mailingLists as $list)
                    <div class="float-right">
                        <a href="{{ $list->url }}" class="btn btn-primary">Subscribe</a>
                    </div>
                    <h5>{{ $list->name }}</h5>
                    {!! $list->description !!}
                    {!! !$loop->last ? '<hr/>' : '' !!}
                @endforeach
            </div>
        </div>
    @endif

@endsection
