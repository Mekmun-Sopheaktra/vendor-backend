<?php

namespace App\Http\Controllers\vendor;

use App\Constants\DiscountConstants;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Exception;
use Illuminate\Http\Request;
class VendorDiscountController extends Controller
{
    use BaseApiResponse;

    /**
     * List all discounts based on the selected tab.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $tab = $request->get('tab');

            switch ($tab) {
                case DiscountConstants::PENDING:
                    return $this->pending($request);

                case DiscountConstants::ACTIVE:
                    return $this->active($request);

                case DiscountConstants::EXPIRED:
                    return $this->expired($request);

                default:
                    return $this->failed(null, 'Error', 'Invalid tab');
            }
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    /**
     * Retrieve pending discounts.
     * Pending discounts have a start date greater than the current date.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function pending(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default items per page is 10
            //search
            $search = $request->get('search');
            $discounts = Discount::where('start_date', '>', now())
                ->where('title', 'like', '%' . $search . '%')
                ->paginate($perPage);

            return $this->success($discounts, 'Discount', 'Pending discount list retrieved successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    /**
     * Retrieve active discounts.
     * Active discounts have a start date less than or equal to the current date,
     * an end date greater than or equal to the current date, and are active.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function active(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default items per page is 10
            $search = $request->get('search');

            $discounts = Discount::where('start_date', '<=', now())
                ->where('title', 'like', '%' . $search . '%')
                ->where('end_date', '>=', now())
//                ->where('status', true)
                ->paginate($perPage);

            return $this->success($discounts, 'Discount', 'Active discount list retrieved successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    /**
     * Retrieve expired discounts.
     * Expired discounts have an end date less than the current date.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function expired(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default items per page is 10
            $search = $request->get('search');

            $discounts = Discount::where('end_date', '<', now())
                ->where('title', 'like', '%' . $search . '%')
                ->paginate($perPage);

            return $this->success($discounts, 'Discount', 'Expired discount list retrieved successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //create discount
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'discount' => 'required|numeric|between:0,100', // Must be a number between 0 and 100
                'start_date' => 'required|date', // Must be today or later
                'end_date' => 'required|date|after:start_date', // Must be after the start date
                'status' => 'required',
                'product_id' => 'required|exists:products,id', // Ensure product exists
            ]);

            // Add vendor and user IDs to the validated data
            $validatedData['vendor_id'] = auth()->user()->vendor->id;
            $validatedData['user_id'] = auth()->id();

            $existingDiscount = Discount::where('product_id', $validatedData['product_id'])
                ->where('status', true)
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_date', [$validatedData['start_date'], $validatedData['end_date']])
                        ->orWhereBetween('end_date', [$validatedData['start_date'], $validatedData['end_date']])
                        ->orWhere(function ($subQuery) use ($validatedData) {
                            $subQuery->where('start_date', '<=', $validatedData['start_date'])
                                ->where('end_date', '>=', $validatedData['end_date']);
                        });
                })
                ->exists();

            if ($existingDiscount) {
                return $this->failed(null, 'Duplicate Discount', 'A discount already exists for this product during the selected date range.');
            }

            // Create the discount
            $discount = Discount::create($validatedData);
            $product = Product::find($validatedData['product_id']);
            $product->discount = $validatedData['discount'];
            $product->save();

            // Get discount with product
            $discount = Discount::with('product')->find($discount->id);

            return $this->success($discount, 'Discount', 'Discount created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->failed($e->errors(), 'Validation Error', 'Invalid data provided.');
        } catch (Exception $e) {
            // Handle other exceptions
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }


    //show
    public function show(Discount $discount)
    {
        try {
            //get discount with product
            $discount = Discount::with('product')->find($discount->id);

            return $this->success($discount, 'Discount', 'Discount retrieved successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //update discount
    public function update(Request $request, Discount $discount)
    {
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'discount' => 'required|numeric|between:0,100', // Must be a number between 0 and 100
                'start_date' => 'required|date', // Must be today or later
                'end_date' => 'required|date|after:start_date', // Must be after the start date
                'status' => 'required',
                'product_id' => 'required|exists:products,id', // Ensure product exists
            ]);

            // Update the discount
            $discount->update($validatedData);

            //get discount with product
            $discount = Discount::with('product')->find($discount->id);

            //also update in products table
            $product = Product::find($validatedData['product_id']);
            $product->discount = $validatedData['discount'];
            $product->save();

            return $this->success($discount, 'Discount', 'Discount updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->failed($e->errors(), 'Validation Error', 'Invalid data provided.');
        } catch (Exception $e) {
            // Handle other exceptions
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //delete discount
    public function destroy(Discount $discount)
    {
        try {
            $discount->delete();

            return $this->success(null, 'Discount', 'Discount deleted successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //check if discount is expired or not if expired then update the status and products table discount
    public function checkDiscountStatus()
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

            return $this->success(null, 'Discount', 'Discount status checked successfully.');
        } catch (Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }
}
