<?php

namespace App\Observers;

use App\Models\Bill;

class BillObserver
{
    /**
     * Handle the Bill "creating" event.
     */
    public function creating(Bill $bill): void
    {
        // Lock the row to prevent race conditions ideally in a transaction,
        // but since this is observer, the service creating it will use a transaction.
        $lastBill = Bill::where('shop_id', $bill->shop_id)
            ->orderByDesc('bill_number')
            ->lockForUpdate()
            ->first();

        $maxBillNumber = $lastBill ? $lastBill->bill_number : 0;
        
        $bill->bill_number = $maxBillNumber + 1;
    }
}
