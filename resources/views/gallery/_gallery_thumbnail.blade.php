<div class="{{ config('aldebaran.settings.gallery_arrangement') == 'rows' ? 'flex-fill' : '' }} img-fluid align-self-center text-center m-1">
    <div class="container">
        <div class="content align-self-center" style="min-height:100px;">
            @if ($piece->images->where('is_visible', 1)->count() + $piece->literatures->where('is_visible', 1)->count() > 1)
                <div class="image-badge badge-primary">
                    @if ($piece->images->where('is_visible', 1)->count())
                        <abbr data-toggle="tooltip" title="{{ $piece->images->where('is_visible', 1)->count() }} Image{{ $piece->images->where('is_visible', 1)->count() == 1 ? '' : 's' }}">{!! $piece->images->where('is_visible', 1)->count() == 1 ? '<i class="fas fa-image"></i>' : '<i class="fas fa-images"></i>' !!}
                            {{ $piece->images->where('is_visible', 1)->count() }}</abbr>
                    @endif
                    @if ($piece->images->where('is_visible', 1)->count() && $piece->literatures->where('is_visible', 1)->count())
                        /
                    @endif
                    @if ($piece->literatures->where('is_visible', 1)->count())
                        <abbr data-toggle="tooltip" title="{{ $piece->literatures->where('is_visible', 1)->count() }} Literature{{ $piece->literatures->where('is_visible', 1)->count() == 1 ? '' : 's' }}"><i class="fas fa-book"></i>
                            {{ $piece->literatures->where('is_visible', 1)->count() }}</abbr>
                    @endif
                </div>
            @endif
            <a class="align-self-center" href="{{ $piece->url . '?source=' . $source }}">
                <div class="content-overlay"></div>
                <div class="text-center align-self-center my-auto">
                    @if ($piece->thumbnailUrl)
                        <img src="{{ $piece->thumbnailUrl }}"
                            style="{{ config('aldebaran.settings.gallery_arrangement') == 'rows' ? 'width: auto; height: ' . config('aldebaran.settings.thumbnail_height') . 'px' : 'height: auto; max-width: ' . config('aldebaran.settings.thumbnail_width') . 'px' }}"
                            alt="Thumbnail for {{ $piece->name }}" />
                    @elseif($piece->literatures->where('is_visible', 1)->count())
                        <div class="text-secondary justify-content-center pt-2"
                            style="{{ config('aldebaran.settings.gallery_arrangement') == 'rows' ? 'width: auto; height: ' . config('aldebaran.settings.thumbnail_height') . 'px' : 'height: auto; max-width: ' . config('aldebaran.settings.thumbnail_width') . 'px' }}; overflow: hidden;">
                            {{ str_limit(strip_tags($piece->literatures->where('is_visible', 1)->where('is_primary', 1)->count()? $piece->literatures->where('is_visible', 1)->where('is_primary', 1)->first()->text: $piece->literatures->where('is_visible', 1)->random()->text),450) }}
                        </div>
                    @endif
                </div>
                <div class="content-details align-self-center fadeIn-bottom">
                    <h5 style="width:100%;">{{ $piece->name }}</h5>
                    <p style="font-size: 0.8em;">
                        {{ isset($piece->timestamp) ? $piece->timestamp->format('M Y') : $piece->created_at->format('M Y') }}
                        @if (!isset($project) || !$project)
                            ・ In <abbr>{{ $piece->project->name }}</abbr>
                        @endif
                        @if ($piece->tags()->visible()->count())
                            <br />
                            @foreach ($piece->tags()->visible()->get()->sortBy(function ($tags) {
            return $tags->tag->name;
        }) as $tag)
                                {!! $tag->tag->name !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        @endif
                    </p>
                </div>
            </a>
        </div>
    </div>
</div>
