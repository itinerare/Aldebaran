@extends('admin.layout')

@section('admin-title')
    Commission Types
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Commission Types' => 'admin/data/commission-types',
        ($type->id ? 'Edit' : 'Create') . ' Type' => $type->id ? 'admin/data/commission-types/edit/' . $type->id : 'admin/data/commission-types/create',
    ]) !!}

    <h1>{{ $type->id ? 'Edit' : 'Create' }} Commission Type
        @if ($type->id)
            <a href="#" class="btn btn-outline-danger float-right delete-type-button">Delete Type</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $type->id ? 'admin/data/commission-types/edit/' . $type->id : 'admin/data/commission-types/create',
    ]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $type->name, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('category_id', 'Category') !!}
        {!! Form::select('category_id', $categories, $type->category_id, [
            'class' => 'form-control',
            'placeholder' => 'Select a Category',
            'required',
        ]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $type->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h3>Pricing</h3>

    <div class="form-group">
        {!! Form::label('price_type', 'Price Type') !!} {!! add_help('This determines how the cost is displayed to potential commissioners.') !!}
        {!! Form::select('price_type', ['flat' => 'Flat Cost', 'range' => 'Range', 'min' => 'Minimum', 'rate' => 'Hourly Rate'], $type->id ? $type->data['pricing']['type'] : null, [
            'class' => 'form-control',
            'id' => 'price_type',
            'placeholder' => 'Select a Pricing Type',
            'required',
        ]) !!}
    </div>

    <div class="card mb-3 hide" id="flatOptions">
        <div class="card-body">
            {!! Form::label('flat_cost', 'Flat Cost') !!}
            {!! Form::number('flat_cost', $type->id && isset($type->data['pricing']['cost']) ? $type->data['pricing']['cost'] : null, ['class' => 'form-control', 'placeholder' => 'Enter a Cost']) !!}
        </div>
    </div>

    <div class="card mb-3 hide" id="rangeOptions">
        <div class="card-body">
            {!! Form::label('cost_min', 'Cost Range') !!}
            <div class="d-flex">
                {!! Form::number('cost_min', $type->id && isset($type->data['pricing']['range']['min']) ? $type->data['pricing']['range']['min'] : null, ['class' => 'form-control', 'placeholder' => 'Enter a Minimum Cost']) !!}
                {!! Form::number('cost_max', $type->id && isset($type->data['pricing']['range']['max']) ? $type->data['pricing']['range']['max'] : null, ['class' => 'form-control', 'placeholder' => 'Enter a Maximum Cost']) !!}
            </div>
        </div>
    </div>

    <div class="card mb-3 hide" id="minOptions">
        <div class="card-body">
            {!! Form::label('minimum_cost', 'Minimum Cost') !!}
            {!! Form::number('minimum_cost', $type->id && isset($type->data['pricing']['cost']) ? $type->data['pricing']['cost'] : null, ['class' => 'form-control', 'placeholder' => 'Enter a Minimum Cost']) !!}
        </div>
    </div>

    <div class="card mb-3 hide" id="rateOptions">
        <div class="card-body">
            {!! Form::label('rate', 'Rate') !!}
            {!! Form::number('rate', $type->id && isset($type->data['pricing']['cost']) ? $type->data['pricing']['cost'] : null, ['class' => 'form-control', 'placeholder' => 'Enter a Rate']) !!}
        </div>
    </div>

    <h4>Extras</h4>
    <div class="form-group">
        {!! Form::label('extras', 'Extras (Optional)') !!} {!! add_help('Information about any extras and associated cost(s).') !!}
        {!! Form::textarea('extras', $type->id && isset($type->data['extras']) ? $type->data['extras'] : null, [
            'class' => 'form-control',
        ]) !!}
    </div>

    <h3>Availability</h3>

    <p>If both of these switches are disabled, the type will be neither visible nor available for request.</p>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('is_active', 1, $type->id ? $type->is_active : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will be able to see but not request this commission type.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $type->id ? $type->is_visible : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this commission type. If the type is still active, they may still request it if they have a direct link to it.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('availability', 'Available Slots (Optional)') !!} {!! add_help('Number of slots available for this type at once. Filled by accepted commissions, at which point no more requests will be accepted. Set to 0 to disable.') !!}
        {!! Form::number('availability', $type->availability, ['class' => 'form-control']) !!}
    </div>

    <h3>Other</h3>

    <div class="form-group">
        {!! Form::label('tags[]', 'Associated Tags (Optional)') !!} {!! add_help('You can select up to 10 tags at once. Works with these tag(s) will be used to populate the examples gallery for this commission type, if one is displayed.') !!}
        {!! Form::select('tags[]', $tags, $type->id && isset($type->data['tags']) ? $type->data['tags'] : '', [
            'id' => 'tagsList',
            'class' => 'form-control',
            'multiple',
        ]) !!}
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('show_examples', 1, $type->id ? $type->show_examples : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('show_examples', 'Show Examples', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Whether or not a gallery of examples should be displayed for this type.') !!}
        </div>
        @if ($type->id)
            <div class="col-md form-group">
                {!! Form::checkbox('regenerate_key', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('regenerate_key', 'Regenerate Key', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If checked, this will regenerate the key used for direct links to this type. Will, for good or ill, break previous links.') !!}
            </div>
        @endif
        <div class="w-100"></div>
        <div class="col-md form-group">
            {!! Form::checkbox('quotes_open', 1, $type->quotes_open, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('quotes_open', 'Quotes Open', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                'If checked, this will allow visitors to request quotes for this type. Note that this ignores whether commissions are open, the type is active, etc. Note that the type\'s info must still be visible or the quote request form linked directly.',
            ) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('quote_required', 1, $type->quote_required, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('quote_required', 'Require a Quote', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If checked, this will require potential commissioners to provide the key of a quote when requesting a commission of this type.') !!}
        </div>
    </div>

    @if ($type->id)
        <div class="form-group">
            {!! Form::label('link', 'Link') !!} {!! add_help('URL to link directly to the commission type\'s information. Can be used to link when the type is active but not visible.') !!}
            {!! Form::text('link', $type->url, ['class' => 'form-control', 'disabled']) !!}
        </div>

        @if ($type->quotes_open)
            <div class="form-group">
                {!! Form::label('quote_link', 'Quote Request Form') !!} {!! add_help('URL to link directly to the commission type\'s quote request form.') !!}
                {!! Form::text('quote_link', $type->quoteUrl, ['class' => 'form-control', 'disabled']) !!}
            </div>
        @endif

        <h2>Form Fields</h2>

        <p>This section is optional; if no fields are provided and the toggles are left off, the corresponding settings from
            this type's category will be used instead. If the category's settings are also empty, the settings from that
            category's class will be used instead. It's recommended to make smart use of this to minimize redundancy!</p>

        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('include_class', 1, isset($type->data['include']['class']) ? $type->data['include']['class'] : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('include_class', 'Include Class Form Fields', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the form fields from this type\'s class will be included in this type\'s forms.') !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('include_category', 1, isset($type->data['include']['category']) ? $type->data['include']['category'] : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('include_category', 'Include Category Form Fields', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the form fields from this type\'s category will be included in this type\'s forms.') !!}
                </div>
            </div>
        </div>

        <p>These fields will be used to populate the commission request form for this type.</p>

        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="add-field">Add Field</a>
        </div>
        <div id="fieldList">
            @if (isset($type->data['fields']))
                @foreach ($type->data['fields'] as $key => $field)
                    @include('admin.commissions._field_builder_entry', ['key' => $key, 'field' => $field])
                @endforeach
            @endif
        </div>
    @endif

    @if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') || config('aldebaran.commissions.payment_processors.paypal.integration.enabled'))
        <h2>Invoice Information</h2>
        <p>
            This will be used to populate product information when creating invoices for this commission type. If not set, this uses the next most specific information (this type's category's if set, its class' if not); that is, if those values are still
            applicable to this type, you do not need to set them here. For convenience, the currently relevant values are displayed as placeholder information in the fields below if they are unset.
        </p>
        <p>
            You can also specify more specific information for any of these fields per individual commission; however, if a field is unset for a commission, the information here will likewise be used instead.
        </p>
        @include('admin.commissions._invoice_fields', ['object' => $type, 'parent' => true])
    @endif

    <div class="text-right">
        {!! Form::submit($type->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div class="field-row hide mb-2">
        @include('admin.commissions._field_builder_row')
    </div>

@endsection

@section('scripts')
    @parent
    @include('admin.commissions._field_builder_js')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('#tagsList').selectize({
                maxItems: 10
            });

            $('.delete-type-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/commission-types/delete') }}/{{ $type->id }}",
                    'Delete Commission Type');
            });

            var priceType = $('#price_type');
            var e = document.getElementById("price_type");
            var result = e.options[e.selectedIndex].value;

            var flat = result === 'flat';
            var range = result === 'range';
            var min = result === 'min';
            var rate = result === 'rate';

            updateOptions();

            priceType.on('change', function(e) {
                var e = document.getElementById("price_type");
                var result = e.options[e.selectedIndex].value;

                flat = result === 'flat';
                range = result === 'range';
                min = result === 'min';
                rate = result === 'rate';

                updateOptions();
            });

            function updateOptions() {
                if (flat) $('#flatOptions').removeClass('hide');
                else $('#flatOptions').addClass('hide');

                if (range) $('#rangeOptions').removeClass('hide');
                else $('#rangeOptions').addClass('hide');

                if (min) $('#minOptions').removeClass('hide');
                else $('#minOptions').addClass('hide');

                if (rate) $('#rateOptions').removeClass('hide');
                else $('#rateOptions').addClass('hide');
            }
        });
    </script>
@endsection
