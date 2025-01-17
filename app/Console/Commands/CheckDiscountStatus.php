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
            $discounts = Discount::all();

            foreach ($discounts as $discount) {
                $product = Product::find($discount->product_id);

                if (!$product) {
                    continue;
                }

                $now = now();

                if ($now->lt($discount->start_date)) {
                    // Before start date, disable discount
                    $discount->status = false;
                    $discount->save();

                    $product->discount = 0;
                } elseif ($now->between($discount->start_date, $discount->end_date)) {
                    // Between start and end date, apply discount
                    $discount->status = true;
                    $discount->save();

                    $product->discount = $discount->discount;
                } else {
                    // After end date, disable discount
                    $discount->status = false;
                    $discount->save();

                    $product->discount = 0;
                }

                $product->save();
            }

            $this->info('Discount status checked and updated successfully.');
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
