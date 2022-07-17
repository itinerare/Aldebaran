<div class="d-flex align-content-around flex-wrap flex-md-row mb-2">
    @foreach ($pieces->split(4) as $group)
        @foreach ($group as $piece)
            @include('gallery._gallery_thumbnail', [
                'piece' => $piece,
                'source' =>
                    (isset($source) ? $source : 'gallery') .
                    (Request::get('page') ? '&page=' . Request::get('page') : ''),
                'project' => isset($project) ? $project : false,
            ])
        @endforeach
    @endforeach
</div>
