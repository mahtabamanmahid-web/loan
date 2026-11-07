@extends('layouts.app')

@section('content')
    @include('frontend.home.hero')

    {{-- Sections in the order chosen in Admin → Website → Homepage Sections --}}
    @foreach($sections as $section)
        @includeIf('frontend.home.'.$section)
    @endforeach
@endsection
