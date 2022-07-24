@foreach ($type->formFields as $key => $field)
    @if ($form)
        <div class="form-group">
            @if ($field['type'] == 'checkbox')
                {!! Form::checkbox($key, 1, old($key) != null ? old($key) : (isset($field['value']) ? $field['value'] : 1), [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
            @endif
            @if (isset($field['label']))
                {!! Form::label($field['type'] == 'multiple' ? $key . '[]' : $key, $field['label'], [
                    'class' =>
                        'label-class' .
                        ($field['type'] == 'checkbox' ? ' ml-3' : '') .
                        (isset($field['rules']) && $field['rules'] ? ' ' . $field['rules'] : ''),
                ]) !!} @if (isset($field['help']))
                    {!! add_help($field['help']) !!}
                @endif
            @endif
            @if (($field['type'] == 'choice' || $field['type'] == 'multiple') && isset($field['choices']))
                @foreach ($field['choices'] as $value => $choice)
                    <div class="choice-wrapper">
                        <input class="form-check-input ml-0 pr-4"
                            name="{{ $field['type'] == 'multiple' ? $key . '[]' : $key }}"
                            id="{{ $field['type'] == 'multiple' ? $key . '[]' : $key . '_' . $value }}"
                            type="{{ $field['type'] == 'multiple' ? 'checkbox' : 'radio' }}"
                            value="{{ $field['type'] == 'multiple' ? (old($key . '[]') != null ? old($key . '[]') : $value) : (old($key . '_' . $value) != null ? old($key . '_' . $value) : $value) }}">
                        <label for="{{ $key }}[]" class="label-class ml-3">{{ $choice }}</label>
                    </div>
                @endforeach
            @elseif($field['type'] != 'checkbox')
                @switch($field['type'])
                    @case('text')
                        {!! Form::text($key, old($key), ['class' => 'form-control']) !!}
                    @break

                    @case('textarea')
                        {!! Form::textarea($key, old($key), ['class' => 'form-control']) !!}
                    @break

                    @case('number')
                        {!! Form::number($key, old($key), ['class' => 'form-control']) !!}
                    @break

                    @default
                        <input class="form-control" name="{{ $key }}" type="{{ $field['type'] }}"
                            id="{{ $key }}">
                @endswitch
            @endif
        </div>
    @elseif(!$form)
        <div class="row mb-2">
            <div class="col-md-4">
                <h5>{{ $field['label'] }}</h5>
            </div>
            <div class="col-md">
                @if ($field['type'] == 'checkbox')
                    {!! isset($commission->data[$key])
                        ? ($commission->data[$key]
                            ? '<i class="fas fa-check text-success"></i>'
                            : '<i class="fas fa-times text-danger"></i>')
                        : '-' !!}
                @elseif(($field['type'] == 'multiple' || $field['type'] == 'choice') && isset($field['choices']))
                    @if ($field['type'] == 'multiple')
                        @if (isset($commission->data[$key]))
                            @foreach ($commission->data[$key] as $answer)
                                {{ isset($field['choices'][$answer]) ? $field['choices'][$answer] : $answer }}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        @else
                            -
                        @endif
                    @else
                        {{ isset($commission->data[$key]) ? $field['choices'][$commission->data[$key]] : '-' }}
                    @endif
                @elseif($field['type'] != 'checkbox')
                    {!! isset($commission->data[$key]) ? nl2br(htmlentities($commission->data[$key])) : '-' !!}
                @endif
            </div>
        </div>
    @endif
@endforeach
