<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discount;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;

class CheckDiscountStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update discount status for expired discounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $discounts = Discount::where('end_date', '<', now())->get();

            foreach ($discounts as $discount) {
                $discount->status = false;
                $discount->save();

                $product = Product::find($discount->product_id);
                $product->discount = 0;
                $product->save();
            }

            $this->info('Discount status checked and updated successfully.');
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
