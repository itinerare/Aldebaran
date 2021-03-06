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
        $piece->showInGallery ? 'Gallery' : $piece->project->name => $piece->showInGallery
            ? 'gallery'
            : 'projects/' . $piece->project->slug,
        $piece->name => 'pieces/' . $piece->id,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            @if (!$piece->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif
            {{ $piece->name }}
            @if (Request::get('source'))
                <div class="float-right ml-2">
                    <a class="btn btn-secondary"
                        href="{{ url(Request::get('source') . (Request::get('page') ? '?page=' . Request::get('page') : '')) }}">Go
                        Back</a>
                </div>
            @endif
        </h1>
    </div>

    <!-- Images -->
    @if ($piece->images->count())
        <div class="row">
            @foreach ($piece->primaryImages->where('is_visible', 1) as $image)
                <div class="col-md text-center align-self-center mb-2">
                    <a href="{{ $image->imageUrl }}" data-lightbox="entry"
                        data-title="{{ isset($image->description) ? $image->description : '' }}">
                        <img class="img-thumbnail p-2" src="{{ $image->imageUrl }}"
                            style="max-width:100%; max-height:60vh;"
                            alt="{{ $image->alt_text ?? 'Primary image ' . $loop->iteration . ' for ' . $piece->name }}" />
                    </a>
                </div>
                {!! $loop->odd && $loop->count > 2 ? '<div class="w-100"></div>' : '' !!}
            @endforeach
        </div>

        <div class="row mb-2">
            @foreach ($piece->otherImages->where('is_visible', 1) as $image)
                <div class="col-sm text-center align-self-center mb-2">
                    <a href="{{ $image->imageUrl }}" data-lightbox="entry"
                        data-title="{{ isset($image->description) ? $image->description : '' }}">
                        <img class="img-thumbnail p-2" src="{{ $image->thumbnailUrl }}"
                            style="max-width:100%; max-height:60vh;"
                            alt="{{ $image->alt_text ?? 'Thumbnail for secondary image ' . $loop->iteration . ' for ' . $piece->name }}" />
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
                    {!! isset($piece->timestamp) ? $piece->timestamp->format('F Y') : $piece->created_at->format('F Y') !!}
                </strong>
                ???
                Last updated {{ $piece->updated_at->toFormattedDateString() }}
                ???
                In {!! $piece->project->displayName !!}
                @if ($piece->tags()->visible()->count())
                    <br />
                    <small>
                        @foreach ($piece->tags()->visible()->get()->sortBy(function ($tags) {
                return $tags->tag->name;
            })
        as $tag)
                            {!! $tag->tag->getDisplayName(Request::get('source') ? Request::get('source') : null) !!}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </small>
                @endif
                @if ($piece->programs()->visible()->count())
                    <small class="float-right mb-2">
                        @foreach ($piece->programs()->visible()->get()->sortBy(function ($programs) {
                return $programs->program->name;
            })
        as $program)
                            @if ($program->program->has_image)
                                <img class="mw-100" style="height:16px;" src="{{ $program->program->imageUrl }}"
                                    alt="Icon for {{ $program->name }}" />
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
