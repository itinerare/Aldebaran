@extends('admin.layout')

@section('admin-title')
    New Commission
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Commission Types' => 'admin/data/commission-types',
        $type->name => 'admin/data/commission-types/edit/' . $type->id,
        'New Commission' => 'admin/commissions/new/' . $type->id,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            New Commmission
            <div class="float-right ml-2">
                <a class="btn btn-secondary" href="{{ url('admin/data/commission-types') }}">Back to Commission Type
                    Index</a>
            </div>
        </h1>
    </div>

    <p>
        This form may be used to manually add a commission or other work to the queue.
    </p>

    <div class="card card-body mb-4">
        <h2>Selected Commission Type: {{ $type->name }}</h2>
        <h3>{{ $type->pricing }}</h3>
        <p>{!! $type->description !!}</p>
        @if ($type->extras)
            <h5>Extras:</h5>
            <p>{!! $type->extras !!}</p>
        @endif
    </div>

    {!! Form::open(['url' => 'admin/commissions/new', 'action' => 'admin/commissions/new']) !!}

    <h3>Basic Information</h3>
    <p>Either select an existing commissioner or enter contact information.</p>

    <div class="row">
        <div class="col-md">
            <h5>Existing</h5>
            <div class="form-group">
                {!! Form::label('commissioner_id', 'Commissioner') !!}
                {!! Form::select('commissioner_id', $commissioners, null, [
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

            <div class="form-group">
                {!! Form::label('payment_email', 'Payment Address') !!} {!! add_help('If different from the email address provided above. If this field is left blank, the above email address will automatically be used instead.') !!}
                {!! Form::text('payment_email', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    @if ($commission->paymentProcessors()->count() > 1)
        <div class="form-group">
            {!! Form::label('payment_processor', 'Payment Processor') !!}
            @foreach ($commission->paymentProcessors() as $key => $label)
                <div class="choice-wrapper">
                    <input class="form-check-input ml-0 pr-4" name="payment_processor" id="{{ 'payment_processor_' . $key }}" type="radio" value="{{ old('payment_processor_' . $key) != null ? old('payment_processor_' . $key) : $key }}">
                    <label for="{{ 'payment_processor_' . $key }}" class="label-class ml-3">{{ $label }}</label>
                </div>
            @endforeach
        </div>
    @elseif($commission->paymentProcessors()->first())
        {!! Form::hidden(
            'payment_processor',
            $commission->paymentProcessors()->keys()->first(),
        ) !!}
    @endif

    <h3>Commission-Specific Information</h3>

    {!! Form::hidden('type', $type->id, ['class' => 'form-control']) !!}

    @include('commissions._form_builder', ['type' => $type, 'form' => true])

    <div class="form-group">
        {!! Form::label('additional_information', 'Anything Else? (Optional)') !!}
        {!! Form::textarea('additional_information', null, ['class' => 'form-control']) !!}
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
