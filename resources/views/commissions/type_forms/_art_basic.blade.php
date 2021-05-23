<div class="form-group">
    {!! Form::label('Reference(s)') !!} {!! add_help('Please provide the URL(s) of clear reference(s) for each character.') !!}
    {!! Form::textarea('references', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Desired pose(s), attitude(s)/Expression(s), and the like') !!} {!! add_help('Consult the information for the commission type you\'ve selected for more details.') !!}
    {!! Form::textarea('details', null, ['class' => 'form-control']) !!}
</div>
