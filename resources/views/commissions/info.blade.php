@extends('layouts.app')

@section('title')
    {{ $class->name }} Commissions
@endsection

@section('content')
    {!! breadcrumbs([$class->name . ' Commissions' => 'commissions/' . $class->slug]) !!}

    <div class="borderhr mb-4">
        <x-admin-edit-button name="Commission Class" :object="$class" />
        <h1>{{ $class->name }} Commissions</h1>
    </div>

    {!! $page->text !!}

    @if (Settings::get($class->slug . '_status'))
        <div class="text-center">
            <h3>Commission Status: {{ Settings::get($class->slug . '_status') }}</h3>
        </div>
    @endif

    @if (Settings::get($class->slug . '_comms_open') && Settings::get($class->slug . '_overall_slots') > 0)
        <div class="text-center">
            <h4>
                Slots are currently limited.
                {{ $count->getSlots($class) . '/' . Settings::get($class->slug . '_overall_slots') }} commission
                slot{{ Settings::get($class->slug . '_overall_slots') == 1 ? ' is' : 's are' }} available.<br />
            </h4>
            <p>
                Note that some commission types may also have limited slots.
            </p>
        </div>
    @endif

    @if ($categories->count())
        @if ($categories->count() > 1)
            <div class="text-center mb-2">
                @foreach ($categories as $category)
                    <a href="#{{ $category->name }}" class="btn btn-secondary m-2"> {{ $category->name }}</span></a>
                @endforeach
            </div>
        @endif

        @foreach ($categories as $category)
            <div class="card card-body mb-4">
                <div id="{{ $category->name }}" class="text-center">
                    <x-admin-edit-button name="Commission Category" :object="$category" />
                    <h2>{{ $category->name }} Commissions</h2>
                </div>
                @foreach ($category->types()->with('commissions:id,commission_type,status')->visible()->get() as $type)
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
