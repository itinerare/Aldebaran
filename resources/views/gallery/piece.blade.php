@extends('layouts.app')

@section('title')
    {{ $piece->name }}
@endsection

@if ($piece->thumbnailUrl)
    @section('meta-img')
        {{ $piece->thumbnailUrl }}
    @endsection
@endif

@section('meta-desc')
    {{ strip_tags($piece->description) }}
@endsection

@section('content')
    {!! breadcrumbs([
        $origin == 'gallery' ? 'Gallery' : $piece->project->name => $origin == 'gallery' ? 'gallery' : 'projects/' . $piece->project->slug,
        $piece->name => 'pieces/' . $piece->id,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            @if (!$piece->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif
            {{ $piece->name }}
            <x-admin-edit-button name="Piece" :object="$piece" />
            @if (Request::get('source'))
                <a class="float-right btn btn-secondary ml-2" href="{{ url(Request::get('source') . (Request::get('page') ? '?page=' . Request::get('page') : '')) }}">Go Back</a>
            @endif
        </h1>
    </div>

    @if ($neighbors)
        <div class="row mb-4">
            <div class="col-6 text-left float-left">
                @if ($neighbors['previous'])
                    <a class="btn btn-outline-secondary" href="{{ $neighbors['previous']->url }}{{ Request::get('source') ? '?source=' . Request::get('source') : '' }}">
                        <i class="text-primary fas fa-angle-double-left"></i> {{ $neighbors['previous']->name }}
                    </a>
                @endif
            </div>
            <div class="col-6 text-right float-right">
                @if ($neighbors['next'])
                    <a class="btn btn-outline-secondary" href="{{ $neighbors['next']->url }}{{ Request::get('source') ? '?source=' . Request::get('source') : '' }}">
                        {{ $neighbors['next']->name }} <i class="text-primary fas fa-angle-double-right"></i><br />
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Images -->
    @if ($piece->images->count())
        <div class="row">
            @foreach ($piece->primaryImages->where('is_visible', 1) as $image)
                <div class="col-md text-center align-self-center mb-2">
                    <a href="{{ $image->imageUrl }}" data-lightbox="entry" data-title="{{ isset($image->description) ? $image->description : '' }}">
                        <img class="img-thumbnail p-2" src="{{ $image->imageUrl }}" style="max-width:100%; max-height:60vh;" alt="{{ $image->alt_text ?? 'Primary image ' . $loop->iteration . ' for ' . $piece->name }}" />
                    </a>
                </div>
                {!! $loop->odd && $loop->count > 2 ? '<div class="w-100"></div>' : '' !!}
            @endforeach
        </div>

        <div class="row mb-2">
            @foreach ($piece->otherImages->where('is_visible', 1) as $image)
                <div class="col-sm text-center align-self-center mb-2">
                    <a href="{{ $image->imageUrl }}" data-lightbox="entry" data-title="{{ isset($image->description) ? $image->description : '' }}">
                        <img class="img-thumbnail p-2" src="{{ $image->thumbnailUrl }}" style="max-width:100%; max-height:60vh;" alt="{{ $image->alt_text ?? 'Thumbnail for secondary image ' . $loop->iteration . ' for ' . $piece->name }}" />
                    </a>
                </div>
                {!! $loop->iteration % ($loop->count % 4 == 0 ? 4 : 3) == 0 ? '<div class="w-100"></div>' : '' !!}
            @endforeach
        </div>
    @endif

    <!-- Literature -->
    @if ($piece->literatures->count())
        @foreach ($piece->literatures as $literature)
            <div class="card mb-4">
                <div class="card-body">
                    {!! $literature->text !!}
                </div>
            </div>
        @endforeach
    @endif

    <!-- Information -->
    <div class="card card-body">
        <div class="borderhr mb-2">
            <p>
                <strong>
                    {!! $piece->date->format('F Y') !!}
                </strong>
                ・
                Last updated {{ $piece->updated_at->toFormattedDateString() }}
                ・
                In {!! $piece->project->displayName !!}
                @if ($piece->tags()->visible()->count())
                    <br />
                    <small>
                        @foreach ($piece->tags()->visible()->get()->sortBy(function ($tags) {
                return $tags->tag->name;
            }) as $tag)
                            {!! $tag->tag->getDisplayName(Request::get('source') ? Request::get('source') : null) !!}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </small>
                @endif
                @if ($piece->programs()->visible()->count())
                    <small class="float-right mb-2">
                        @foreach ($piece->programs()->visible()->get()->sortBy(function ($programs) {
                return $programs->program->name;
            }) as $program)
                            @if ($program->program->has_image)
                                <img class="mw-100" style="height:16px;" src="{{ $program->program->imageUrl }}" alt="Icon for {{ $program->program->name }}" />
                            @endif{!! $program->program->name !!}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </small>
                @endif
            </p>
        </div>
        <div>
            @if ($piece->images->whereNotNull('alt_text')->count())
                <div class="card bg-secondary text-light mw-100 mx-auto mx-md-3 mb-2">
                    <div class="card-body">
                        <h5>Image description{{ $piece->images->whereNotNull('alt_text')->count() > 1 ? 's' : '' }}:</h5>
                        <span class="skip"><a href="#description">To Description</a></span>

                        @foreach ($piece->images->whereNotNull('alt_text') as $image)
                            <p>
                                @if ($loop->count > 1)
                                    <strong>Image #{{ $loop->iteration }}:</strong>
                                @endif
                                {{ $image->alt_text }}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div id="description">
                {!! $piece->description ?? '<i>No description provided.</i>' !!}
            </div>
        </div>
    </div>
@endsection
