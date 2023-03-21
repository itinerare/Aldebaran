@extends('admin.layout')

@section('admin-title')
    New Quote
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        $class->name . ' Quote Queue' => 'admin/commissions/quotes/' . $class->slug . '/pending',
        'New Quote' => 'admin/commissions/quotes/new',
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            New Quote
            <div class="float-right ml-2">
                <a class="btn btn-secondary" href="{{ url('admin/commissions/quotes/' . $class->slug) }}">Back to {{ $class->name }} Quote Queue</a>
            </div>
        </h1>
    </div>

    <p>
        This form may be used to manually add a quote to the queue.
    </p>

    {!! Form::open(['url' => 'admin/commissions/quotes/new', 'action' => 'admin/commissions/quotes/new']) !!}

    @honeypot

    <h3>Basic Information</h3>
    <p>Either select an existing commissioner or enter contact information.</p>

    <div class="row">
        <div class="col-md">
            <h5>Existing</h5>
            <div class="form-group">
                {!! Form::label('commissioner_id', 'Commissioner') !!}
                {!! Form::select('commissioner_id', $commissioners, old('commissioner_id'), [
                    'class' => 'form-control selectize',
                    'placeholder' => 'Select a Commissioner',
                ]) !!}
            </div>
        </div>
        <div class="col-md">
            <h5>New</h5>
            <div class="form-group">
                {!! Form::label('name', 'Name (Optional)') !!}
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('email', 'Email') !!}
                {!! Form::text('email', null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('contact', 'Preferred Method of Contact') !!} {!! Settings::get('contact_info') ? add_help(Settings::get('contact_info')) : '' !!}
                {!! Form::text('contact', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('commission_type_id', 'Commission Type') !!}
        {!! Form::select('commission_type_id', $types, old('subject'), ['class' => 'form-control', 'placeholder' => 'Select a Commission Type']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('subject', 'Subject (Optional)') !!}
        {!! Form::text('subject', old('subject'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description') !!}
        {!! Form::textarea('description', old('description'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('amount', 'Amount (Optional)') !!}
        {!! Form::number('amount', old('amount') ?? 0.00, ['class' => 'form-control']) !!}
    </div>

    <div class="text-right">
        <input onclick="this.disabled=true;this.value='Submiting...';this.form.submit();" class="btn btn-primary" type="submit" value="Submit"></input>
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
