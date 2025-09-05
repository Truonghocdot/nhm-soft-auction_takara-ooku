@extends('layouts.app')

@section('title', '403 - Không có quyền truy cập')
@section('content')
<div class="min-h-screen flex items-center justify-center">
  <div class="text-center p-6">
    <h1 class="text-6xl font-bold mb-4">404</h1>
    <p class="text-xl mb-4">Current page is not available!</p>
    <a href="{{ url()->previous() ?? url('/') }}" class="inline-block px-4 py-2 border rounded">Come back</a>
  </div>
</div>
@endsection
