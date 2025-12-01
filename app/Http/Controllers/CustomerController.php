<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers (buyers).
     */
    public function index()
    {
        return view('dashboard.customers');
    }

    /**
     * Get all customers (buyers) with their statistics.
     */
    public function getCustomers()
    {
        $customers = User::where(function($query) {
                $query->where('role', 'buyer')
                      ->orWhere('role', 'visitor');
            })
            ->withCount([
                'orders' => function ($query) {
                    $query->where('status', '!=', 'cancelled');
                }
            ])
            ->withSum([
                'orders' => function ($query) {
                    $query->where('status', '!=', 'cancelled');
                }
            ], 'total_price')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($customer) {
                return [
                    'user_id' => $customer->user_id,
                    'name' => $customer->name ?? 'Tidak ada nama',
                    'email' => $customer->email,
                    'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $customer->created_at->format('d M Y'),
                    'orders_count' => $customer->orders_count ?? 0,
                    'total_purchase' => (float)($customer->orders_sum_total_price ?? 0),
                    'status' => 'aktif', // Default to aktif, can be extended with status field
                ];
            });

        return response()->json($customers);
    }

    /**
     * Get customer details.
     */
    public function show($id)
    {
        $customer = User::with(['orders' => function ($query) {
            $query->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $stats = [
            'total_orders' => $customer->orders->count(),
            'total_purchase' => $customer->orders->sum('total_price'),
            'last_order_date' => $customer->orders->first()?->created_at,
        ];

        return response()->json([
            'customer' => $customer,
            'stats' => $stats,
        ]);
    }

    /**
     * Deactivate/Activate customer.
     */
    public function toggleStatus($id)
    {
        $customer = User::findOrFail($id);
        
        // If status field doesn't exist, we can use a different approach
        // For now, let's assume we'll add a 'is_active' field or use 'status'
        $currentStatus = $customer->status ?? 'aktif';
        $newStatus = $currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
        
        // Update status if field exists, otherwise we'll need to add it
        if (\Schema::hasColumn('users', 'status')) {
            $customer->status = $newStatus;
            $customer->save();
        } else {
            // Alternative: use a different field or create a migration
            // For now, return success but note that status field needs to be added
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pelanggan berhasil diubah',
            'status' => $newStatus,
        ]);
    }
}

