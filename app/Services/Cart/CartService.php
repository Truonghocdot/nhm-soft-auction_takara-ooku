<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Repositories\Cart\CartRepository;
use App\Services\BaseService;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;

class CartService extends BaseService implements CartServiceInterface
{
    protected CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepo)
    {
        parent::__construct([
            'cart' => $cartRepo,
        ]);
        $this->cartRepository = $cartRepo;
    }

    public function addToCart(int $userId, int $productId, int $quantity): array
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);

            if ($product->status != 1) {
                throw new ServiceException('Product not available!');
            }

            if ($product->type_sale != 1) {
                throw new ServiceException('The bid product could not be added to the cart!');
            }

            if ($product->stock < $quantity) {
                throw new ServiceException('Quantity exceeds stock!');
            }

            $existingCart = $this->cartRepository->findByUserAndProduct($userId, $productId);

            if ($existingCart) {
                $newQuantity = $existingCart->quantity + $quantity;
                if ($product->stock < $newQuantity) {
                    throw new ServiceException('Quantity exceeds stock!');
                }

                $this->getRepository('cart')->updateOne($existingCart->id, [
                    'quantity' => $newQuantity,
                    'total' => $newQuantity * $existingCart->price
                ]);
            } else {
                $this->getRepository('cart')->insertOne([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $quantity * $product->price,
                    'status' => 1
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Product has been added to cart!',
                'data' => null
            ];
        } catch (ServiceException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'There was an error adding to cart!',
                'data' => null
            ];
        }
    }

    public function getUserCart(int $userId): array
    {
        try {
            $cartItems = $this->cartRepository->getUserActiveCart($userId);

            $validCartItems = $cartItems->filter(function ($cartItem) {
                return $cartItem->product && $cartItem->product->exists;
            });

            $total = $validCartItems->sum('total');

            return [
                'success' => true,
                'message' => 'Successful shopping cart!',
                'data' => [
                    'cartItems' => $validCartItems,
                    'total' => $total,
                    'count' => $validCartItems->count()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving the cart!',
                'data' => null
            ];
        }
    }

    public function updateQuantity(int $userId, int $productId, int $quantity): array
    {
        try {
            if ($quantity < 1) {
                throw new ServiceException('Quantity must be greater than 0!');
            }

            $product = Product::where('id', $productId)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$product) {
                throw new ServiceException('Product does not exist or is not available!');
            }

            if ($product->stock < $quantity) {
                throw new ServiceException('Quantity exceeds stock!');
            }

            $cartItem = $this->cartRepository->findByUserAndProduct($userId, $productId);
            if (!$cartItem) {
                throw new ServiceException('No products found in cart!');
            }

            $this->cartRepository->updateOne($cartItem->id, [
                'quantity' => $quantity,
                'total' => $quantity * $cartItem->price
            ]);

            $updatedCartItem = $this->cartRepository->find($cartItem->id);
            $cartSummary = $this->getCartSummary($userId);
            return [
                'success' => true,
                'message' => 'Amount updated successfully!',
                'data' => [
                    'quantity' => $updatedCartItem->quantity,
                    'total' => $updatedCartItem->total,
                    'cart_total' => $cartSummary['data']['total'],
                    'cart_count' => $cartSummary['data']['count']
                ]
            ];
        } catch (ServiceException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while updating quantity!',
                'data' => null
            ];
        }
    }

    public function removeItem(int $userId, int $productId): array
    {
        try {
            $cartItem = $this->cartRepository->findByUserAndProduct($userId, $productId);
            if (!$cartItem) {
                throw new ServiceException('The item was not found in the cart!');
            }

            $this->cartRepository->deleteOne($cartItem->id);

            $cartSummary = $this->getCartSummary($userId);
            return [
                'success' => true,
                'message' => 'Product deletion successful!',
                'data' => [
                    'cart_total' => $cartSummary['data']['total'],
                    'cart_count' => $cartSummary['data']['count']
                ]
            ];
        } catch (ServiceException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the product!',
                'data' => null
            ];
        }
    }

    public function clearCart(int $userId): array
    {
        try {
            $this->cartRepository->clearUserCart($userId);

            return [
                'success' => true,
                'message' => 'Cart deleted successfully!',
                'data' => [
                    'cart_total' => 0,
                    'cart_count' => 0
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the cart!',
                'data' => null
            ];
        }
    }

    public function getCartSummary(int $userId): array
    {
        try {
            $cartItems = $this->cartRepository->getUserActiveCart($userId);
            $total = $cartItems->sum('total');
            $count = $cartItems->count();

            return [
                'success' => true,
                'message' => 'Successfully fetched cart information!',
                'data' => [
                    'total' => $total,
                    'count' => $count
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching cart information!',
                'data' => null
            ];
        }
    }
}
