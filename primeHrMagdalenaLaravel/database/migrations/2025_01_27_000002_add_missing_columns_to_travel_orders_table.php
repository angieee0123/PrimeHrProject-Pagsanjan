<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            // Add order_number column if it doesn't exist
            if (!Schema::hasColumn('travel_orders', 'order_number')) {
                $table->string('order_number')->unique()->after('id');
            }
            
            // Add other missing columns if they don't exist
            if (!Schema::hasColumn('travel_orders', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('travel_orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('travel_orders', 'disapproved_by')) {
                $table->foreignId('disapproved_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('travel_orders', 'disapproved_at')) {
                $table->timestamp('disapproved_at')->nullable();
            }
            
            if (!Schema::hasColumn('travel_orders', 'disapproval_reason')) {
                $table->text('disapproval_reason')->nullable();
            }
            
            if (!Schema::hasColumn('travel_orders', 'filed_by')) {
                $table->foreignId('filed_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
        
        // Update existing records to have order numbers
        $this->generateOrderNumbersForExistingRecords();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'approved_by',
                'approved_at', 
                'disapproved_by',
                'disapproved_at',
                'disapproval_reason',
                'filed_by'
            ]);
        });
    }
    
    /**
     * Generate order numbers for existing records
     */
    private function generateOrderNumbersForExistingRecords()
    {
        $travelOrders = DB::table('travel_orders')->whereNull('order_number')->get();
        
        foreach ($travelOrders as $index => $order) {
            $year = date('Y', strtotime($order->travel_date ?? $order->created_at));
            $month = date('m', strtotime($order->travel_date ?? $order->created_at));
            $number = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            
            $orderNumber = "TO-{$year}{$month}-{$number}";
            
            DB::table('travel_orders')
                ->where('id', $order->id)
                ->update(['order_number' => $orderNumber]);
        }
    }
};