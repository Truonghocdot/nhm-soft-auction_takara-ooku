<?php

namespace App\Services\Wishlist;

use App\Exceptions\ServiceException;
use App\Repositories\Wishlist\WishlistRepository;
use App\Services\BaseService;

class WishlistService extends BaseService implements WishlistServiceInterface
{
    protected $wishlistRepository;

    public function __construct(WishlistRepository $wishlistRepository)
    {
        parent::__construct([
            'wishlist' => $wishlistRepository
        ]);
        $this->wishlistRepository = $wishlistRepository;
    }

    public function getByUserId($userId)
    {
        return $this->wishlistRepository->getByUserId($userId);
    }

    public function insert($userId, $productId)
    {
        return $this->wishlistRepository->insert($userId, $productId);
    }

    public function deleteByUserIdAndProductId($userId, $productId)
    {
        return $this->getRepository('wishlist')->query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clear($userId)
    {
        return $this->getRepository('wishlist')->deleteMany(['user_id' => $userId]);
    }

    public function createOne($userId, $productId)
    {
        $repo = $this->getRepository('wishlist');

        $exists = $repo->query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            throw new ServiceException('Product already in wishlist');
        }

        return $repo->insertOne([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }

    public function getSummary(int $userId): array
    {
        try {
            $count = $this->getRepository('wishlist')
                ->query()
                ->where('user_id', $userId)
                ->count();

            return [
                'success' => true,
                'message' => 'Get wishlist information successfully!',
                'data' => [
                    'count' => (int) $count,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while getting wishlist information!',
                'data' => null,
            ];
        }
    }
}
