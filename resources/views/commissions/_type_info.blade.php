<div class="row">
    <div class="col-md align-self-center mt-2">
        <div class="text-center">
            <x-admin-edit-button name="Commission Type" :object="$type" />
            <h2>{{ $type->name }}</h2>
            <h3>{{ $type->pricing }}</h3>
        </div>
        <p>{!! $type->description !!}</p>
        @if ($type->extras)
            <h5>Extras:</h5>
            <p>{!! $type->extras !!}</p>
        @endif
        <div class="text-right mb-4">
            @if ($type->quote_required)
                <p class="text-muted"><i>This commission type requires having a pre-existing quote.</i></p>
            @endif
            @if ($type->canCommission)
                @if ($type->availability > 0)
                    <h5>Available Slots: {{ $type->displaySlots }}</h5>
                @endif
                <a class="btn btn-success mb-2" href="{{ url('/commissions/' . $type->category->class->slug . '/new?type=' . $type->id . (isset($source) && $source == $type->key ? '&key=' . $type->key : '')) }}">Request a Commission</a>
            @else
                <p>
                    {{ Settings::get($category->class->slug . '_comms_open') ? 'This commission type is currently unavailable!' : 'Commissions are currently closed!' }}
                </p>
            @endif
            @if ($type->quotes_open)
                <a class="btn btn-primary mb-2" href="{{ url('/commissions/' . $type->category->class->slug . '/quotes/new?type=' . $type->id) }}">Request a Quote</a>
            @endif
        </div>
    </div>
    @if ($type->show_examples && $type->getExamples(Auth::check() ? Auth::user() : null) && $type->getExamples(Auth::check() ? Auth::user() : null)->count())
        <div class="col-md-7 align-self-center borderleft my-4">
            <div class="mobile-hide d-flex justify-content-center align-content-around flex-wrap mb-4">
                @include('gallery._flex_columns', [
                    'pieces' => $type->getExamples(Auth::check() ? Auth::user() : null),
                    'source' => isset($source) && $source == $type->key ? '/commissions/types/' . $type->key : 'commissions/' . $type->category->class->slug,
                    'split' => 2,
                ])
            </div>
            <div class="mobile-show d-flex justify-content-center align-content-around flex-wrap mb-4">
                @include('gallery._flex_columns', [
                    'pieces' => $type->getExamples(Auth::check() ? Auth::user() : null),
                    'source' => isset($source) && $source == $type->key ? '/commissions/types/' . $type->key : 'commissions/' . $type->category->class->slug,
                    'split' => 1,
                ])
            </div>
            @if ($type->getExamples(Auth::check() ? Auth::user() : null, true)->count() > 4)
                <div class="text-center mt-auto">
                    <a class="btn btn-primary" href="{{ url('/commissions/types/' . (isset($source) && $source == $type->key ? $type->key : $type->id) . '/gallery') }}">More
                        Examples</a>
                </div>
            @endif
        </div>
    @endif
</div>
