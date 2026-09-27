@extends('layouts.dashboard')

@section('header')
    {{ $milestone->template->name }}
@endsection

@section('content')
@include('milestones.partials.details')

