<?php

namespace App\Http\Controllers;

use App\Services\Wishlist\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    public function list()
    {
        return view('pages.wishlist.list');
    }

    public function getItems(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $items = $this->wishlistService->getByUserId($userId);
            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting favorites list'
            ], 500);
        }
    }

    public function add(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            $userId = Auth::id();
            $this->wishlistService->createOne($userId, $productId);

            return response()->json([
                'success' => true,
                'message' => 'Product has been added to favorites'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            $userId = Auth::id();
            $this->wishlistService->deleteByUserIdAndProductId($userId, $productId);

            return response()->json([
                'success' => true,
                'message' => 'Product has been removed from favorites'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing product from favorites'
            ], 500);
        }
    }

    public function clear(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->wishlistService->clear($userId);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist has been emptied'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
