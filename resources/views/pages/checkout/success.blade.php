@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Order successful!</h1>

                    <p class="text-gray-600">Thank you for shopping with us</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Order information</h2>
                    <div class="space-y-2 text-left">
                        <div class="flex justify-between">
                            <span class="font-medium">Order code:</span>
                            <span>{{ $orderDetail->code_orders }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Total:</span>
                            <span>{{ number_format($orderDetail->total, 0, ',', '.') }} ₫</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Status:</span>
                            <span class="badge badge-success">Paid</span>
                        </div>
                    </div>
                </div>
                <div class="space-x-4">
                    <a href="{{ url('/admin/orders') }}" class="btn btn-neutral">
                        Manage orders
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline">
                        View cart
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
