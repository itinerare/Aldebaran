<div style="width:100%;" class="d-md-flex justify-content-center flex-row flex-md-wrap mb-2">
    @foreach ($pieces->split(isset($split) ? $split : 4) as $group)
        <div class="d-flex flex-column text-center" style="max-width: {{ 100 / (isset($split) ? $split : 4) }}%">
            @foreach ($group as $piece)
                @include('gallery._gallery_thumbnail', [
                    'piece' => $piece,
                    'source' => isset($source) ? $source : 'gallery',
                    'project' => isset($project) ? $project : false,
                ])
            @endforeach
        </div>
    @endforeach
</div>
