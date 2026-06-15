@extends('layouts.app')

@section('content')
    <x-report.group-landing
        :title="$title"
        :subtitle="$subtitle"
        :reports="$reports"
        :hub-action="$hubAction"
        :range="$range"
        :from="$from->toDateString()"
        :to="$to->toDateString()"
        :range-options="$rangeOptions"
        :period-label="$periodLabel"
    />
@endsection
