@extends('layouts.app')

@section('title')
    Commission (#{{ $commission->id }})
@endsection

@section('content')
    {!! breadcrumbs([
        $commission->type->category->class->name . ' Commissions' => 'commissions/' . $commission->type->category->class->slug,
        $commission->type->name . ' Commission' => 'commissions/view/' . $commission->commission_key,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            #{{ $commission->id }} ・ {!! $commission->commissioner->displayName !!}
            <x-admin-edit-button name="Commission" :object="$commission" class="badge badge-secondary" />
            <div
                class="float-right badge
        {{ $commission->status == 'Pending' ? 'badge-primary' : '' }}
        {{ $commission->status == 'Accepted' || $commission->status == 'Complete' ? 'badge-success' : '' }}
        {{ $commission->status == 'Declined' ? 'badge-danger' : '' }}
        ">
                {{ $commission->status }}
            </div>
        </h1>
    </div>

    @if ($commission->commissioner->is_banned)
        <div class="alert alert-danger">
            You have been banned. All pending commission requests from you have been automatically declined and you may not
            make any further commission requests.
        </div>
    @endif

    <p>
        This page displays the status of your commission. It can only be accessed via this URL (displayed below for
        convenience as well), so make sure to save it!
        @if ($commission->status != 'Declined')
            {{ $commission->status == 'Pending' ? 'If your commission is accepted' : 'When your commission is completed' }},
            files relating to your commission-- including the finished commission-- will be available here.
        @endif
    </p>

    @include('commissions._basic_info', ['type' => 'commission', 'subject' => $commission])

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h2>Commission-related Info</h2>
            <p>This is the information you provided when filling out the commission request form.</p>

            @include('commissions._form_builder', ['type' => $commission->type, 'form' => false])

            <div class="row mb-2">
                <div class="col-md-4">
                    <h5>Additional Information</h5>
                </div>
                <div class="col-md">
                    {!! isset($commission->data['additional_information']) ? nl2br(htmlentities($commission->data['additional_information'])) : '-' !!}
                </div>
            </div>

            @if ($commission->quote)
                <div class="row mb-2">
                    <div class="col-md-4">
                        <h5>Quote</h5>
                    </div>
                    <div class="col-md">
                        <a href="{{ $commission->quote->url }}">
                            #{{ $commission->quote->id }}
                            @if ($commission->quote->subject)
                                - {{ $commission->quote->subject }}
                            @endif
                        </a>
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label('link', 'Link') !!} {!! add_help('The URL of this page, as mentioned above!') !!}
                {!! Form::text('link', $commission->url, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
    </div>

    @if ($commission->status == 'Accepted' || $commission->status == 'Complete')
        <h2>Pieces</h2>
        <p>These are the piece(s) associated with your commission! Pieces may have multiple images and/or pieces of
            literature, depending. Each image associated with a piece is displayed via its thumbnail, and each thumbnail
            links to the full-sized image. Likewise, the full text of each piece of literature is displayed in a collapsed
            section.</p>

        @if ($commission->pieces->count())
            <div class="mb-4">
                @foreach ($commission->pieces as $piece)
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                @if ($piece->piece->images->count())
                                    <div class="row">
                                        @foreach ($piece->piece->primaryImages as $image)
                                            <div class="col-md text-center align-self-center mb-2">
                                                <a href="{{ url('commissions/view/' . $commission->commission_key . '/' . $image->id) }}">
                                                    <img class="img-thumbnail p-2" src="{{ $image->thumbnailUrl }}" style="max-width:100%; max-height:60vh;" alt="Thumbnail for image #{{ $image->id }} from piece {{ $piece->piece->name }}" />
                                                </a>
                                            </div>
                                            {!! $loop->odd ? '<div class="w-100"></div>' : '' !!}
                                        @endforeach
                                    </div>

                                    <div class="row mb-2">
                                        @foreach ($piece->piece->otherImages as $image)
                                            <div class="col-sm text-center align-self-center mb-2">
                                                <a href="{{ url('commissions/view/' . $commission->commission_key . '/' . $image->id) }}">
                                                    <img class="img-thumbnail p-2" src="{{ $image->thumbnailUrl }}" style="max-width:100%; max-height:60vh;" alt="Thumbnail for image #{{ $image->id }} from piece {{ $piece->piece->name }}" />
                                                </a>
                                            </div>
                                            {!! $loop->even ? '<div class="w-100"></div>' : '' !!}
                                        @endforeach
                                    </div>
                                @else
                                    <i>No image(s) provided.</i>
                                @endif
                            </div>
                            <div class="col-md mb-2">
                                <div class="card card-body">
                                    <div class="borderhr">
                                        <h4>
                                            {{ $piece->piece->name }}
                                            @if ($piece->piece->is_visible)
                                                <span class="float-right"><a class="btn btn-primary" href="{{ $piece->piece->url }}">View in Gallery</a></span>
                                            @endif
                                        </h4>
                                        @if ($piece->piece->images->count())
                                            <p>
                                                {{ $piece->piece->primaryImages->count() }} Primary
                                                Image{{ $piece->piece->primaryImages->count() == 1 ? '' : 's' }} ・
                                                {{ $piece->piece->otherImages->count() }} Secondary
                                                Image{{ $piece->piece->otherImages->count() == 1 ? '' : 's' }}<br />
                                                {{ $piece->piece->images->count() }}
                                                Image{{ $piece->piece->images->count() == 1 ? '' : 's' }} Total
                                            </p>
                                        @endif
                                        @if ($piece->piece->literatures->count())
                                            <p>
                                                {{ $piece->piece->primaryLiteratures->count() }} Primary
                                                Literature{{ $piece->piece->primaryLiteratures->count() == 1 ? '' : 's' }}
                                                ・ {{ $piece->piece->otherImages->count() }} Secondary
                                                Literature{{ $piece->piece->otherLiteratures->count() == 1 ? '' : 's' }}<br />
                                                {{ $piece->piece->literatures->count() }}
                                                Literatures{{ $piece->piece->literatures->count() == 1 ? '' : 's' }}
                                                Total
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if ($piece->piece->literatures->count())
                                <div class="col-md-12">
                                    @foreach ($piece->piece->literatures as $literature)
                                        <div class="card mb-2">
                                            <h5 class="card-header">
                                                Literature #{{ $literature->id }}
                                                <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#literature-{{ $literature->id }}" data-toggle="collapse">Show</a></h3>
                                            </h5>
                                            <div class="card-body collapse" id="literature-{{ $literature->id }}">
                                                {!! $literature->text !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p><i>There are no pieces associated with this commission.</i></p>
        @endif
    @endif

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h3>Comments</h3>
            {!! isset($commission->comments) ? $commission->comments : '<p><i>No comment provided.</i></p>' !!}
        </div>
    </div>

@endsection
