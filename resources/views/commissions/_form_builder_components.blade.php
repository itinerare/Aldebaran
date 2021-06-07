<div class="form-group">
    @if($field['type'] == 'checkbox')
        <input class="form-check-input ml-0 pr-4" name="{{ $key }}" type="checkbox" value="{{ old($key) != null ? old($key) : (isset($field['value']) ? $field['value'] : 1) }}">
    @endif
    @if(isset($field['label']))
        {!! Form::label((isset($field['multiple']) && $field['multiple'] ? $key.'[]' : $key), $field['label'], ['class' => 'label-class'.($field['type'] == 'checkbox' ? ' ml-3' : '').(isset($field['rules']) && $field['rules'] ? ' '.$field['rules'] : '' )]) !!} @if(isset($field['help'])) {!! add_help($field['help']) !!} @endif
    @endif
    @if($field['type'] == 'choice' && isset($field['choices']))
        @foreach($field['choices'] as $value=>$choice)
            <div class="choice-wrapper">
                <input class="form-check-input ml-0 pr-4" name="{{ isset($field['multiple']) && $field['multiple'] ? $key.'[]' : $key }}" id="{{ isset($field['multiple']) && $field['multiple'] ? $key.'[]' : $key.'_'.$value }}" type="{{ isset($field['multiple']) && $field['multiple'] ? 'checkbox' : 'radio' }}" value="{{ isset($field['multiple']) && $field['multiple'] ? (old($key.'[]') != null ? old($key.'[]') : $value) : (old($key.'_'.$value) != null ? old($key.'_'.$value) : $value) }}">
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
                <input class="form-control" name="{{ $key }}" type="{{ $field['type'] }}" id="{{ $key }}">
        @endswitch
    @endif
</div>
