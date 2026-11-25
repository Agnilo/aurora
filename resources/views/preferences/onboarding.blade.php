@extends('layouts.onboarding')

@php
    $motivationOnly = $motivationOnly ?? false;
    $questions = $motivationOnly ? $profileQuestions : $settingsQuestions;
@endphp

@section('title', t('preferences.onboarding.ui.title') . ' | Aurora')

@section('content')
    @include('preferences.partials.onboarding-form')
@endsection