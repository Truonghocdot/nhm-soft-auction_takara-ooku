<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\CommonConstant;
use App\Enums\Permission\RoleConstant;
use App\Enums\Product\ProductTypeSale;
use App\Filament\Resources\BuyMembershipResource;
use App\Filament\Resources\ProductResource;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Products\ProductServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = "Post Product";

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->hasRole(RoleConstant::ADMIN)) {
            return;
        }
        $user = app(AuthServiceInterface::class)->getInfoAuth();

        if (empty($user)) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('No user information found. Please log in again.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }

        $membershipPlans = collect();
        $membershipUsers = collect();
        $userId = null;

        if (is_array($user)) {
            $membershipPlans = collect($user['membershipPlans'] ?? []);
            $membershipUsers = collect($user['membershipUsers'] ?? []);
            $userId = $user['id'] ?? null;
        } elseif (is_object($user)) {
            $membershipPlans = $user->membershipPlans instanceof \Illuminate\Support\Collection
                ? $user->membershipPlans
                : collect($user->membershipPlans ?? []);
            $membershipUsers = $user->membershipUsers instanceof \Illuminate\Support\Collection
                ? $user->membershipUsers
                : collect($user->membershipUsers ?? []);
            $userId = $user->id ?? null;
        }

        if (empty($userId)) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('Unable to determine user ID.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }

        $productsCount = app(ProductServiceInterface::class)->getCountProductByCreatedByAndNearMonthly($userId);
        if ($membershipPlans->isEmpty()) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('You need to purchase a membership package to create a product. Please select a package to continue.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }

        $planActive = $membershipUsers->first(function ($item) {
            $status = is_array($item) ? ($item['status'] ?? null) : ($item->status ?? null);
            return $status == CommonConstant::ACTIVE;
        });

        if (empty($planActive)) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('You need to upgrade or activate another membership plan. Please select a plan to continue.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }

        $config = null;
        if (is_array($planActive)) {
            $config = $planActive['membershipPlan']['config'] ?? null;
        } elseif (is_object($planActive)) {
            $config = $planActive->membershipPlan->config ?? null;
        }

        if (empty($config) || !is_array($config) && !is_object($config)) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('Invalid package configuration. Please contact administrator.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }

        $cfg = is_array($config) ? $config : (array) $config;

        $freeListing = $cfg['free_product_listing'] ?? false;
        $maxPerMonth = array_key_exists('max_products_per_month', $cfg) ? $cfg['max_products_per_month'] : null;

        if ($freeListing) {
            return;
        }

        if (is_null($maxPerMonth) || $maxPerMonth == 0) {
            return;
        }

        if (($maxPerMonth > 0 && $productsCount >= $maxPerMonth)) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('You have reached the monthly limit of purchasing a membership package to create a product. Please select a package to continue.')
                ->send();

            redirect()->to(BuyMembershipResource::getUrl());
        }
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $typeSale = is_object($data['type_sale']) && method_exists($data['type_sale'], 'value')
            ? $data['type_sale']->value
            : (int) $data['type_sale'];
        if ($typeSale === ProductTypeSale::SALE->value) {
            $data['min_bid_amount'] = 0;
            $data['max_bid_amount'] = 0;
            $data['start_time'] = null;
            $data['end_time'] = null;
        } else if ($typeSale === ProductTypeSale::AUCTION->value) {
            $data['price'] = $data['max_bid_amount'] ?? 0;
        }
        $data['created_by'] = auth()->user()->id;
        return $data;
    }

    protected function beforeCreate(): void
    {
        $user = auth()->user();
        $userId = $user->id;

        $productsCount = app(ProductServiceInterface::class)
            ->getCountProductByCreatedByAndNearMonthly($userId);

        $membershipUsers = $user->membershipUsers ?? collect();
        $planActive = $membershipUsers->first(fn($item) => $item->status == CommonConstant::ACTIVE);

        $config = $planActive?->membershipPlan?->config ?? null;
        $cfg = is_array($config) ? $config : (array) $config;

        $freeListing = $cfg['free_product_listing'] ?? false;
        $maxPerMonth = $cfg['max_products_per_month'] ?? null;

        if (!$freeListing && $maxPerMonth > 0 && $productsCount >= $maxPerMonth) {
            Notification::make()
                ->title('Insufficient permissions')
                ->warning()
                ->body('You have reached your monthly limit, need to purchase a membership package to create more products.')
                ->send();

            $this->halt(); // chặn quá trình tạo
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $typeSale = is_object($data['type_sale']) && method_exists($data['type_sale'], 'value')
            ? $data['type_sale']->value
            : (int) $data['type_sale'];

        if ($typeSale === ProductTypeSale::SALE->value) {
            $data['min_bid_amount'] = 0;
            $data['max_bid_amount'] = 0;
            $data['start_time'] = null;
            $data['end_time'] = null;
        } else if ($typeSale === ProductTypeSale::AUCTION->value) {
            $data['price'] = $data['max_bid_amount'] ?? 0;
        }
        $data['created_by'] = auth()->user()->id;
        return $data;
    }


    protected function handleRecordCreation(array $data): Model
    {
        /** @var ProductServiceInterface $productService */
        $productService = app(ProductServiceInterface::class);
        $data['images'] = $data['images'] ?? [];
        return $productService->createProductWithSideEffects($data, auth()->id());
    }
}
