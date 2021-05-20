@extends('layouts.app')

@section('title') {{ ucfirst($type) }} Commissions @endsection

@section('content')
{!! breadcrumbs([ucfirst($type).' Commissions' => 'commissions/'.$type]) !!}

<div class="borderhr mb-4">
<h1>{{ ucfirst($type) }} Commissions</h1>
</div>

{!! $page->text !!}

@if(Settings::get($type.'_comms_open') && Settings::get('overall_'.$type.'_slots') > 0)
    <div class="text-center">
        <h4>
            Slots are currently limited. {{ $count->getSlots().'/'.Settings::get('overall_'.$type.'_slots') }} commission slot{{ Settings::get('overall_'.$type.'_slots') == 1 ? ' is' : 's are'}} available.<br/>
        </h4>
        <p>
            Some commission types may also have limited slots; these types will display to the best of their ability how many slots are available accounting for both commissions of the type as well as commissions of other types.
        </p>
    </div>
@endif

@if($categories->count())
    @if($categories->count() > 1)
        <div class="text-center mb-2">
            @foreach($categories as $category)
                <a href="#{{ $category->name }}" class="btn btn-secondary m-2"> {{ $category->name }}</span></a>
            @endforeach
        </div>
    @endif

    @foreach($categories as $category)
    <div class="card card-body mb-4">
        <div id="{{ $category->name }}" class="text-center"><h2>{{ $category->name }} Commissions</h2></div>
        @foreach($category->types->where('is_visible', 1) as $type)
            @include('commissions._type_info', ['type' => $type])
        @endforeach
    </div>
    @endforeach
@else
<div class="card card-body text-center mb-4">
    <p>There don't seem to be any available commission types right now! Check back later?</p>
</div>
@endif

@endsection
