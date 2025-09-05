<?php

namespace App\Console\Commands;

use App\Services\Products\ProductServiceInterface;
use Illuminate\Console\Command;

class CloseExpiredListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:close-expired-listings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close products after the display time set in DISPLAY_TIME_AFTER_AUCTION';

    /**
     * Execute the console command.
     */
    public function handle(ProductServiceInterface $productService)
    {
        $closedCount = $productService->closeExpiredListings();
        $this->info("Closed {$closedCount} expired products.");
        $this->info('All expired products processed.');

        return Command::SUCCESS;
    }
}
