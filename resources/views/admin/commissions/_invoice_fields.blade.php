<div class="form-group">
    {!! Form::label('product_name', 'Product Name' . (!isset($require) || !$require ? ' (Semi-optional)' : '')) !!} {!! add_help('A succinct name for the good and/or service you are providing, e.g. \'Commissioned Art\'.' . (!isset($require) || !$require ? ' Required to customize product information.' : '')) !!}
    {!! Form::text('product_name', $object->invoice_data ? $object->invoice_data['product_name'] : null, ['class' => 'form-control', 'placeholder' => isset($parent) && $parent ? $object->parentInvoiceData['product_name'] ?? null : '']) !!}
</div>
@if ($object->id && isset($parent) && $parent)
    <div class="form-group">
        {!! Form::checkbox('unset_product_info', 1, 0, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
            'data-onstyle' => 'danger',
        ]) !!}
        {!! Form::label('unset_product_info', 'Unset Product Information', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Removes the product information set here.') !!}
    </div>
@endif
@if (config('aldebaran.commissions.payment_processors.paypal.integration.enabled') && (isset($object->payment_processor) ? $object->payment_processor == 'paypal' : true))
    <h4>PayPal Information</h4>
    <div class="form-group">
        {!! Form::label('product_description', 'Product Description (Optional)') !!}
        {!! Form::text('product_description', $object->invoice_data ? $object->invoice_data['product_description'] : null, [
            'class' => 'form-control',
            'placeholder' => isset($parent) && $parent ? $object->parentInvoiceData['product_description'] ?? null : '',
        ]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('product_category', 'Product Category' . (!isset($require) || !$require ? ' (Optional)' : '')) !!} {!! add_help('This impacts how invoices sent behave, i.e. whether they attempt to collect a shipping address or not.') !!}
        {!! Form::select(
            'product_category',
            [
                'SERVICES' => 'Services',
                'SHIPPABLE' => 'Shippable Goods',
            ],
            $object->invoice_data ? $object->invoice_data['product_category'] ?? null : null,
            [
                'class' => 'form-control',
                'placeholder' => isset($parent) && $parent ? (isset($object->parentInvoiceData['product_category']) ? ucfirst(strtolower($object->parentInvoiceData['product_category'])) : null) : '',
            ],
        ) !!}
    </div>
@endif
@if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && (isset($object->payment_processor) ? $object->payment_processor == 'stripe' : true))
    <h4>Stripe Information</h4>
    <div class="form-group">
        {!! Form::label('product_tax_code', 'Tax Category Code (Optional)') !!} {!! add_help('The tax code for the relevant category. If unset, this defaults to your Stripe account\'s default tax category.') !!}
        {!! Form::text('product_tax_code', $object->invoice_data ? $object->invoice_data['product_tax_code'] : null, [
            'class' => 'form-control',
            'placeholder' => isset($parent) && $parent ? $object->parentInvoiceData['product_tax_code'] ?? null : '',
        ]) !!}
        <small>
            @if ($taxCode)
                {{ $taxCode['name'] }} ・
            @endif
            <a href="https://stripe.com/docs/tax/tax-categories">Tax Category Reference</a>
        </small>
    </div>
@endif
