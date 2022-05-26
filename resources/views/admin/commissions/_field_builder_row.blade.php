<div class="card mb-2">
    <div class="card-body">
        <a href="#" class="float-right remove-field btn btn-danger mb-2">×</a>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('field_key[]', 'Field Key') !!}
                    {!! Form::text('field_key[]', null, ['class' => 'form-control', 'placeholder' => 'Internal key. Can\'t be duplicated in a form', 'required']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('field_type[]', 'Field Type') !!}
                    {!! Form::select('field_type[]', $fieldTypes, null, ['class' => 'form-control form-field-type', 'placeholder' => 'Select a Type', 'required']) !!}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('field_label[]', 'Field Label') !!}
                    {!! Form::text('field_label[]', null, ['class' => 'form-control', 'placeholder' => 'Label shown on the commission form', 'required']) !!}
                </div>
            </div>
            <div class="chooseOptions col-md-12">
                <div class="choiceOptions hide">
                    <div class="form-group">
                        {!! Form::label('field_choices[]', 'Field Options') !!}
                        {!! Form::text('field_choices[]', null, ['class' => 'form-control', 'placeholder' => 'Enter options, separated by commas']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('field_rules[]', 'Field Rules (Optional)') !!} (See rules <a href="https://laravel.com/docs/8.x/validation#available-validation-rules">here</a>)
                    {!! Form::text('field_rules[]', null, ['class' => 'form-control', 'placeholder' => 'Any custom validation rules']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('field_value[]', 'Field Value (Optional)') !!}
                    {!! Form::text('field_value[]', null, ['class' => 'form-control', 'placeholder' => 'Default value for the field']) !!}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('field_help[]', 'Field Help (Optional)') !!}
                    {!! Form::text('field_help[]', null, ['class' => 'form-control', 'placeholder' => 'Help tooltip text']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
