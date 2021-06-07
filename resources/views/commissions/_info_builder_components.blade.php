<div class="row mb-2">
    <div class="col-md-4"><h5>{{ $field['name'] }}</h5></div>
    <div class="col-md">
        @if($field['type'] == 'checkbox')
            {!! isset($commission->data[$key]) ? ($commission->data[$key] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>') : '-' !!}
        @endif
        @if($field['type'] == 'choice' && isset($field['choices']))
            @foreach($commission->data[$key] as $answer)
                {{ isset($field[$key]['choices'][$answer]) ? $field[$key]['choices'][$answer] : $answer }}<br/>
            @endforeach
        @elseif($field['type'] != 'checkbox')
            {!! isset($commission->data[$key]) ? nl2br(htmlentities($commission->data[$key])) : '-' !!}
        @endif
    </div>
</div>
