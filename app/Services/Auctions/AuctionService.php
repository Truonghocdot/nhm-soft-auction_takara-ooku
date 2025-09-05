<?php

namespace App\Services\Auctions;

use App\Repositories\Auctions\AuctionRepository;
use App\Repositories\AuctionBids\AuctionBidRepository;
use App\Repositories\TransactionPoint\TransactionPointRepository;
use App\Services\Config\ConfigService;
use App\Services\BaseService;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Users\UserRepository;
use Carbon\Carbon;

class AuctionService extends BaseService implements AuctionServiceInterface
{
    protected $auctionRepo;
    protected $bidRepo;
    protected $transactionPointRepo;
    protected $configService;
    protected $userRepo;
    public function __construct(
        AuctionRepository $auctionRepo,
        AuctionBidRepository $bidRepo,
        TransactionPointRepository $transactionPointRepo,
        ConfigService $configService,
        UserRepository $userRepo
    ) {
        $this->auctionRepo = $auctionRepo;
        $this->bidRepo = $bidRepo;
        $this->transactionPointRepo = $transactionPointRepo;
        $this->configService = $configService;
        $this->userRepo = $userRepo;
    }

    public function getAuctionDetails($productId)
    {
        try {
            $auction = $this->auctionRepo->getAuctionByProductId($productId);

            if (!$auction) {
                throw new ServiceException('No online bids found for this product!');
            }

            $highestBid = $this->bidRepo->query()
                ->where('auction_id', $auction->id)
                ->with('user')
                ->orderBy('bid_price', 'desc')
                ->first();
            $totalBids = $this->bidRepo->query()
                ->where('auction_id', $auction->id)
                ->count();

            return [
                'success' => true,
                'data' => [
                    'auction' => $auction,
                    'highest_bid' => $highestBid,
                    'total_bids' => $totalBids,
                    'current_price' => $highestBid ? $highestBid->bid_price : $auction->start_price,
                    'min_next_bid' => $highestBid ? $highestBid->bid_price + $auction->step_price : $auction->start_price + $auction->step_price
                ]
            ];
        } catch (ServiceException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving online bid information!'
            ];
        }
    }

    public function placeBid($auctionId, $userId, $bidPrice)
    {

        try {
            DB::beginTransaction();
            // $bidCoin = $this->configService->getConfigValue('COIN_BIND_PRODUCT_AUCTION', 0);
            $validation = $this->validateBid($auctionId, $bidPrice, $userId);
            if (!$validation['success']) {
                throw new ServiceException($validation['message']);
            }
            $memberplanActives = auth()->user()['activeMemberships'];
            if (count($memberplanActives) == 0) {
                throw new ServiceException('You need to purchase or activate another membership plan to use this feature!');
            } else if ($memberplanActives[0]['config']['free_auction_participation'] == false) {
                throw new ServiceException('You need to upgrade or activate another membership plan to use this feature!');
            }
            $auction = $validation['auction'];

            // $userHasBidded = $this->bidRepo->query()
            //     ->where('auction_id', $auction->id)
            //     ->where('user_id', $userId)
            //     ->exists();

            // if (!$userHasBidded) {
            //     $user = $this->userRepo->find($userId);
            //     if ($user->current_balance < $bidCoin) {
            //         throw new ServiceException('Số dư của bạn không đủ để tham gia Trả giá.');
            //     }
            //     $user->current_balance -= $bidCoin;
            //     $user->save();
            // }
            // if (!$userHasBidded) {
            //     $coinToDeduct = (int) ($this->configService->getConfigValue('COIN_BIND_PRODUCT_AUCTION', 0));
            //     if ($coinToDeduct > 0) {
            //         $this->transactionPointRepo->insertOne([
            //             'point' => -$coinToDeduct,
            //             'description' => 'Phí tham gia Trả giá phiên #' . $auction->id,
            //             'user_id' => $userId,
            //         ]);
            //     }
            // }

            $bidData = [
                'auction_id' => $auction->id,
                'user_id' => $userId,
                'bid_price' => $bidPrice,
                'bid_time' => now(),
            ];

            $bid = $this->bidRepo->insertOne($bidData);
            DB::commit();

            return [
                'success' => true,
                'message' => 'Bid successful!',
                'data' => $bid
            ];
        } catch (ServiceException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAuctionHistory($auctionId)
    {
        try {
            $bids = $this->getRepository('bid')->query()
                ->where('auction_id', $auctionId)
                ->with('user')
                ->orderBy('bid_price', 'desc')
                ->limit(5)
                ->get();

            return [
                'success' => true,
                'data' => $bids
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving bid history!'
            ];
        }
    }

    public function getUserBidHistory($auctionId, $userId)
    {
        try {
            $userBids = $this->bidRepo->query()
                ->where('auction_id', $auctionId)
                ->where('user_id', $userId)
                ->orderBy('bid_price', 'desc')
                ->limit(3)
                ->get();

            $timeDelay = (int) $this->configService->getConfigValue('TIME_DELAY_AUCTION_BIND', 10);

            $latestUserBid = $this->bidRepo->query()
                ->where('auction_id', $auctionId)
                ->where('user_id', $userId)
                ->orderBy('bid_time', 'desc')
                ->first();

            $nextBidTime = null;
            $canBidNow = true;

            if ($latestUserBid) {
                $nextBidTime = \Carbon\Carbon::parse($latestUserBid->bid_time)->addMinutes($timeDelay);
                $canBidNow = now()->gte($nextBidTime);
            }

            return [
                'success' => true,
                'data' => $userBids,
                'time_delay' => $timeDelay,
                'latest_bid_time' => $latestUserBid ? $latestUserBid->bid_time : null,
                'next_bid_time' => $nextBidTime,
                'can_bid_now' => $canBidNow
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving the user bid history!'
            ];
        }
    }

    public function updateStepPriceByProductId(int $productId, float $stepPrice): bool
    {
        try {
            $auction = $this->auctionRepo->getAuctionByProductId($productId);
            if (!$auction) {
                throw new ServiceException('No bids found for this product!');
            }
            $auction->step_price = $stepPrice;
            return (bool) $auction->save();
        } catch (ServiceException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function validateBid($productId, $bidPrice, $userId)
    {
        try {
            $auction = $this->auctionRepo->getAuctionByProductId($productId);
            if (!$auction) {
                return [
                    'success' => false,
                    'message' => 'There is no bid for this product yet!',
                ];
            }

            if (($auction->end_time && now()->gte(\Carbon\Carbon::parse($auction->end_time)))
                || (isset($auction->status) && $auction->status !== 'active')
            ) {
                return [
                    'success' => false,
                    'message' => 'The bid has ended, no more bids!',
                ];
            }

            $latestUserBid = $this->bidRepo->query()
                ->where('auction_id', $auction->id)
                ->where('user_id', $userId)
                ->orderBy('bid_time', 'desc')
                ->first();

            if ($latestUserBid) {
                $timeDelay = (int) $this->configService->getConfigValue('TIME_DELAY_AUCTION_BIND', 10);
                $nextBidTime = Carbon::parse($latestUserBid->bid_time)->addMinutes($timeDelay);

                if (now()->lt($nextBidTime)) {
                    $remainingTime = now()->diffInSeconds($nextBidTime);
                    $remainingMinutes = ceil($remainingTime / 60);

                    return [
                        'success' => false,
                        'message' => "You need to wait {$remainingMinutes} more minutes before you can bid again!",
                    ];
                }
            }

            return [
                'success' => true,
                'auction' => $auction,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while checking the bidding session!'
            ];
        }
    }

    public function getActiveAuctions()
    {
        try {
            $auctions = $this->auctionRepo->getActiveAuctions();

            return [
                'success' => true,
                'data' => $auctions
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving the bid list!'
            ];
        }
    }

    public function getUserParticipatingAuctions($userId)
    {
        try {
            $auctions = $this->auctionRepo->getUserParticipatingAuctions($userId);

            $auctions->each(function ($auction) {
                $this->processAuctionData($auction);
            });

            return [
                'success' => true,
                'data' => $auctions
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving the user bid list!'
            ];
        }
    }

    private function processAuctionData($auction)
    {
        $highestBid = $auction->bids()->orderBy('bid_price', 'desc')->first();
        $currentPrice = $highestBid ? $highestBid->bid_price : $auction->starting_price;

        $auction->current_price_display = number_format($currentPrice, 0, ',', '.') . ' ₫';


        $auction->starting_price_display = number_format($auction->starting_price, 0, ',', '.') . ' ₫';

        $auction->highest_bid_display = $highestBid ? number_format($highestBid->bid_price, 0, ',', '.') . ' ₫' : $auction->starting_price_display;

        $userBid = $auction->bids()->where('user_id', auth()->id())->orderBy('bid_time', 'desc')->first();
        $auction->user_bid_display = $userBid ? number_format($userBid->bid_price, 0, ',', '.') . ' ₫' : 'Chưa đặt giá';
    }
}
