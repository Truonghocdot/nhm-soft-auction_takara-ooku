@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center">
                    <h1 class="text-2xl font-bold mb-6">QR Code Payment</h1>
                    <div class="mb-6"><h3 class="text-lg font-semibold mb-2">Orders: {{ $orderDetail->code_orders }}</h3>
                        <p class="text-gray-600">Total amount: {{ number_format($payment->amount, 0, ',', '.') }} VND</p>
                    </div>

                    <div class="inline-block p-6 bg-white border-2 border-gray-200 rounded-lg mb-6">
                        <img src="{{ $vietqrUrl }}" alt="VietQR Code" class="w-80 h-80 object-contain">
                    </div>

                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Payment instructions:</h4>
                        <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                            <li>Open the banking application on your phone</li>
                            <li>Select the "Scan QR code" feature</li>
                            <li>Scan the QR code above</li>
                            <li>Check the information and confirm the payment</li>
                            <li>Save the payment receipt</li>
                        </ol>
                    </div>

                    <div class="flex justify-center space-x-4">
                        <form action="{{ route('payment.confirm', $orderDetail->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                Confirm payment
                            </button>
                        </form>

                        <a href="{{ route('cart.index') }}" class="btn btn-outline">
                            Return to cart
                        </a>
                    </div>

                    <div class="mt-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> After payment, please click "Confirm payment" to update
                            the status.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
