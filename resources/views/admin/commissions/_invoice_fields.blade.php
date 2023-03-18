<div class="form-group">
    {!! Form::label('product_name', 'Product Name' . (!isset($requireName) || !$requireName ? ' (Optional)' : '')) !!} {!! add_help('A succinct name for the good and/or service you are providing, e.g. \'Commissioned Art\'.') !!}
    {!! Form::text('product_name', $object->invoice_data ? $object->invoice_data['product_name'] : null, ['class' => 'form-control', 'placeholder' => isset($parent) && $parent ? $object->parentInvoiceData['product_name'] ?? null : '']) !!}
</div>
@if (config('aldebaran.commissions.payment_processors.paypal.integration.enabled'))
    <div class="form-group">
        {!! Form::label('product_description', 'Product Description (Optional)') !!}
        @if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled'))
            {!! add_help('Note that this is not shown on Stripe invoices.') !!}
        @endif
        {!! Form::text('product_description', $object->invoice_data ? $object->invoice_data['product_description'] : null, [
            'class' => 'form-control',
            'placeholder' => isset($parent) && $parent ? $object->parentInvoiceData['product_description'] ?? null : '',
        ]) !!}
    </div>
@endif
@if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled'))
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
