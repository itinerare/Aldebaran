@extends('layouts.app')

@section('title')
    Quote (#{{ $quote->id }})
@endsection

@section('content')
    {!! breadcrumbs([
        $quote->type->category->class->name . ' Commissions' => 'commissions/' . $quote->type->category->class->slug,
        $quote->type->name . ' Quote' => 'commissions/view/' . $quote->quote_key,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            #{{ $quote->id }} ・ {!! $quote->commissioner->displayName !!}
            <x-admin-edit-button name="Quote" :object="$quote" class="badge badge-secondary" />
            <div
                class="float-right badge
        {{ $quote->status == 'Pending' ? 'badge-primary' : '' }}
        {{ $quote->status == 'Accepted' || $quote->status == 'Complete' ? 'badge-success' : '' }}
        {{ $quote->status == 'Declined' ? 'badge-danger' : '' }}
        ">
                {{ $quote->status }}
            </div>
        </h1>
    </div>

    @if ($quote->commissioner->is_banned)
        <div class="alert alert-danger">
            You have been banned. All pending quotes or commission requests from you have been automatically declined and you may not make any further quote or commission requests.
        </div>
    @endif

    <p>
        This page displays the status of your quote. It can only be accessed via this URL (displayed below for convenience as well), so make sure to save it!
    </p>

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h2>Basic Info</h2>
            <div class="row">
                <div class="col-md-4">
                    <h5>Commission Type</h5>
                </div>
                <div class="col-md">{!! $quote->type->displayName !!}</div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <h5>Submitted</h5>
                </div>
                <div class="col-md">{!! pretty_date($quote->created_at) !!}</div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <h5>Last Updated</h5>
                </div>
                <div class="col-md">{!! pretty_date($quote->updated_at) !!}</div>
            </div>
        </div>
    </div>

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h2>Quote-related Info</h2>
            <p>This is the information you provided when filling out the quote request form.</p>

            <div class="row mb-2">
                <div class="col-md-4">
                    <h5>Subject</h5>
                </div>
                <div class="col-md">
                    {!! isset($quote->subject) ? nl2br(htmlentities($quote->subject)) : '-' !!}
                </div>
            </div>

            {!! nl2br(htmlentities($quote->description)) !!}

            <div class="form-group mt-2">
                {!! Form::label('link', 'Link') !!} {!! add_help('The URL of this page, as mentioned above!') !!}
                {!! Form::text('link', $quote->url, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
    </div>

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h3>Comments</h3>
            {!! isset($quote->comments) ? $quote->comments : '<p><i>No comment provided.</i></p>' !!}
        </div>
    </div>
@endsection
