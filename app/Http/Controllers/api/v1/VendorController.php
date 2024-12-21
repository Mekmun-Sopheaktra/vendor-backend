<?php

namespace App\Http\Controllers\api\v1;

use App\Mail\VendorPasswordMail;
use App\Models\User;
use App\Traits\BaseApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $vendors = Vendor::paginate($perPage);

        return $this->success($vendors, 'Vendors retrieved successfully');
    }

    //requestVendor create vendor data
    public function requestVendor(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string',
            'purpose' => 'required|string',
            'logo' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email',
        ]);

        $validatedData['slug'] = $validatedData['slug'] ?? Str::slug($validatedData['name']);
        $validatedData['status'] = false;

        $vendor = Vendor::create($validatedData);

        return $this->success($vendor, 'Vendor created successfully', 201);
    }

    //create vendor data and send email to vendor for verification
    public function createVendor($id)
    {
        // Find the vendor by ID
        $vendor = Vendor::find($id);

        // Ensure the vendor exists
        if (!$vendor) {
            return $this->failed('Vendor not found.', 'Error', 404);
        }

        // Set the vendor's status to true
        $vendor->status = true;

        try {
            // Update the vendor record
            $vendor->save();

            // Generate a random password for the associated user
            $password = Str::random(12);

            // Create or update the user based on the vendor's email
            $user = User::updateOrCreate(
                ['email' => $vendor->email], // Use the vendor's email
                [
                    'name' => $vendor->name,
                    'is_vendor' => true,
                    'password' => $password, // Save the hashed password
                ]
            );

            // If the user was recently created or doesn't have a verified email, send email verification
            if ($user->wasRecentlyCreated || !$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            //get user id and update vendor table user_id
            $vendor->user_id = $user->id;
            $vendor->save();

            // Send the first password to the vendor's email
            Mail::to($vendor->email)->send(new VendorPasswordMail($vendor->name, $password));

            return $this->success([
                'vendor' => $vendor,
                'user' => $user,
            ], 'Vendor and User created or updated successfully. Password sent to the vendor\'s email.', 201);

        } catch (\Exception $exception) {
            logger($exception->getMessage());
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string',
            'purpose' => 'required|string',
            'logo' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email',
            'banner' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $validatedData['slug'] = $validatedData['slug'] ?? Str::slug($validatedData['name']);

        $vendor = Vendor::create($validatedData);

        return $this->success($vendor, 'Vendor created successfully', 201);
    }

    public function show($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        return $this->success($vendor);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'sometimes|integer',
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'address' => 'sometimes|string|max:500',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'email' => 'sometimes|email|max:255|unique:vendors,email,' . $id,
            'banner' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
        ]);

        if (isset($validatedData['name']) && empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }

        $vendor->update($validatedData);

        return $this->success($vendor, 'Vendor updated successfully');
    }

    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        $vendor->delete();

        return $this->success(null, 'Vendor deleted successfully', 204);
    }
}
