{{-- Shown when an admin turns on maintenance mode (Admin → System). --}}
@extends('errors.layout')
@section('title', __('Maintenance'))
@section('code', '503')
@section('icon', 'construction')
@section('heading', __('We will be back soon'))
@section('message', $message)
