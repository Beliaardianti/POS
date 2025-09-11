<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Approval;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get cart
        $carts = Cart::with('product')->where('cashier_id', auth()->user()->id)->latest()->get();

        //get all customers
        $customers = Customer::latest()->get();

        return Inertia::render('Apps/Transactions/Index', [
            'carts'         => $carts,
            'carts_total'   => $carts->sum('price'),
            'customers'     => $customers
        ]);
    }

    /**
     * searchProduct
     *
     * @param  mixed $request
     * @return void
     */
    public function searchProduct(Request $request)
    {
        //find product by barcode
        $product = Product::where('barcode', $request->barcode)->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'data'    => $product
            ]);
        }

        return response()->json([
            'success' => false,
            'data'    => null
        ]);
    }

    /**
     * addToCart
     *
     * @param  mixed $request
     * @return void
     */
    public function addToCart(Request $request)
    {
        //check stock product
        if (Product::whereId($request->product_id)->first()->stock < $request->qty) {

            //redirect
            return redirect()->back()->with('error', 'Out of Stock Product!.');
        }

        //check cart
        $cart = Cart::with('product')
            ->where('product_id', $request->product_id)
            ->where('cashier_id', auth()->user()->id)
            ->first();

        if ($cart) {

            //increment qty
            $cart->increment('qty', $request->qty);

            //sum price * quantity
            $cart->price  = $cart->product->sell_price * $cart->qty;

            $cart->save();
        } else {

            //insert cart
            Cart::create([
                'cashier_id'    => auth()->user()->id,
                'product_id'    => $request->product_id,
                'qty'           => $request->qty,
                'price'         => $request->sell_price * $request->qty,
            ]);
        }

        return redirect()->route('apps.transactions.index')->with('success', 'Product Added Successfully!.');
    }

    /**
     * destroyCart
     *
     * @param  mixed $request
     * @return void
     */
    public function destroyCart(Request $request)
    {
        //find cart by ID
        $cart = Cart::with('product')
            ->whereId($request->cart_id)
            ->first();

        //delete cart
        $cart->delete();

        return redirect()->back()->with('success', 'Product Removed Successfully!.');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        /**
         * algorithm generate no invoice
         */
        $length = 10;
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        //generate no invoice
        $invoice = 'TRX-' . Str::upper($random);

        //insert transaction
        $transaction = Transaction::create([
            'cashier_id'    => auth()->user()->id,
            'customer_id'   => $request->customer_id,
            'invoice'       => $invoice,
            'cash'          => $request->cash,
            'change'        => $request->change,
            'discount'      => $request->discount,
            'grand_total'   => $request->grand_total,
            'status'        => 'completed', // Set default status
        ]);

        //get carts
        $carts = Cart::where('cashier_id', auth()->user()->id)->get();

        //insert transaction detail
        foreach ($carts as $cart) {

            //insert transaction detail
            $transaction->details()->create([
                'transaction_id'    => $transaction->id,
                'product_id'        => $cart->product_id,
                'qty'               => $cart->qty,
                'price'             => $cart->price,
            ]);

            //get price
            $total_buy_price  = $cart->product->buy_price * $cart->qty;
            $total_sell_price = $cart->product->sell_price * $cart->qty;

            //get profits
            $profits = $total_sell_price - $total_buy_price;

            //insert provits
            $transaction->profits()->create([
                'transaction_id'    => $transaction->id,
                'total'             => $profits,
            ]);

            //update stock product
            $product = Product::find($cart->product_id);
            $product->stock = $product->stock - $cart->qty;
            $product->save();
        }

        //delete carts
        Cart::where('cashier_id', auth()->user()->id)->delete();

        return response()->json([
            'success' => true,
            'data'    => $transaction
        ]);
    }

    /**
     * print
     *
     * @param  mixed $request
     * @return void
     */
    public function print(Request $request)
    {
        //get transaction
        $transaction = Transaction::with('details.product', 'cashier', 'customer')->where('invoice', $request->invoice)->firstOrFail();

        //return view
        return view('print.nota', compact('transaction'));
    }

    /**
     * Get transaction list for staff to request approval
     */
    public function transactionList()
    {
        // Hanya admin dan cashier yang bisa akses transaction list
        if (!auth()->user()->hasAnyRole(['admin', 'cashier'])) {
            abort(403, 'Unauthorized access to transactions');
        }

        $query = Transaction::with(['customer', 'details.product', 'cashier']);

        // Jika cashier, hanya tampilkan transaksi sendiri
        if (auth()->user()->hasRole('cashier')) {
            $query->where('cashier_id', auth()->id());
        }

        // Jika admin, tampilkan semua transaksi
        // (untuk handle customer complaint dari transaksi cashier manapun)

        $transactions = $query->latest()->paginate(20);

        return Inertia::render('Apps/Transactions/List', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Request refund approval
     */
    public function requestRefund(Request $request)
    {
        // Validate permission
        if (!auth()->user()->can('approvals.request')) {
            return back()->with('error', 'You do not have permission to request approval.');
        }

        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Check if user owns this transaction or is admin
        if ($transaction->cashier_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return back()->with('error', 'You can only request approval for your own transactions.');
        }

        // Check if already has pending approval
        $existingApproval = Approval::where('type', 'refund')
            ->where('reference_id', $transaction->id)
            ->where('status', 'pending')
            ->first();

        if ($existingApproval) {
            return back()->with('error', 'Refund approval request already exists for this transaction.');
        }

        // Create refund approval request
        Approval::create([
            'type' => 'refund',
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
            'amount' => $request->amount ?? $transaction->grand_total,
            'reason' => $request->reason,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'status' => 'pending',
            'data' => []
        ]);

        // Update transaction status
        $transaction->update(['status' => 'pending_approval']);

        return back()->with('success', 'Refund request sent to owner for approval.');
    }

    /**
     * Request void approval
     */
    public function requestVoid(Request $request)
    {
        // Validate permission
        if (!auth()->user()->can('approvals.request')) {
            return back()->with('error', 'You do not have permission to request approval.');
        }

        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reason' => 'required|string|max:500'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Check if user owns this transaction or is admin
        if ($transaction->cashier_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return back()->with('error', 'You can only request approval for your own transactions.');
        }

        // Check if already has pending approval
        $existingApproval = Approval::where('type', 'void')
            ->where('reference_id', $transaction->id)
            ->where('status', 'pending')
            ->first();

        if ($existingApproval) {
            return back()->with('error', 'Void approval request already exists for this transaction.');
        }

        // Create void approval request
        Approval::create([
            'type' => 'void',
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
            'amount' => $transaction->grand_total,
            'reason' => $request->reason,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'status' => 'pending',
            'data' => []
        ]);

        // Update transaction status
        $transaction->update(['status' => 'pending_approval']);

        return back()->with('success', 'Void request sent to owner for approval.');
    }

    /**
     * Process approval (approve/reject) - FUNGSI YANG HILANG
     */
    public function processApproval(Request $request)
    {
        // Validate permission - hanya owner yang bisa approve
        if (!auth()->user()->can('approvals.approve')) {
            return back()->with('error', 'You do not have permission to approve requests.');
        }

        $request->validate([
            'approval_id' => 'required|exists:approvals,id',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);

        $approval = Approval::with('transaction')->findOrFail($request->approval_id);
        $transaction = $approval->transaction;

        if (!$transaction) {
            return back()->with('error', 'Transaction not found.');
        }

        if ($request->action === 'approve') {
            // Update approval status
            $approval->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->notes
            ]);

            // Update transaction status berdasarkan tipe approval
            if ($approval->type === 'refund') {
                $transaction->update(['status' => 'refunded']);

                // Optional: Kembalikan stok jika refund (tergantung business rule)
                foreach ($transaction->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->stock += $detail->qty;
                        $product->save();
                    }
                }
            } elseif ($approval->type === 'void') {
                $transaction->update(['status' => 'voided']);

                // Kembalikan stok karena transaksi dibatalkan
                foreach ($transaction->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->stock += $detail->qty;
                        $product->save();
                    }
                }
            }

            return back()->with('success', ucfirst($approval->type) . ' request has been approved.');
        } else {
            // Reject approval
            $approval->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->notes
            ]);

            // Kembalikan status transaksi ke completed
            $transaction->update(['status' => 'completed']);

            return back()->with('success', ucfirst($approval->type) . ' request has been rejected.');
        }
    }

    /**
     * Show approval list for owner/admin
     */
    public function approvalList()
    {
        // Hanya owner/admin yang bisa melihat approval list
        if (!auth()->user()->can('approvals.view')) {
            abort(403, 'Unauthorized access to approvals');
        }

        $approvals = Approval::with(['transaction.cashier', 'transaction.customer', 'requester', 'approver'])
            ->where('reference_type', 'transaction')
            ->latest()
            ->paginate(20);

        return Inertia::render('Apps/Approvals/Index', [
            'approvals' => $approvals
        ]);
    }

    /**
     * Show specific approval detailss
     */
    public function showApproval($id)
    {
        if (!auth()->user()->can('approvals.view')) {
            abort(403, 'Unauthorized access to approval details');
        }

        $approval = Approval::with([
            'transaction.details.product',
            'transaction.cashier',
            'transaction.customer',
            'requester',
            'approver'
        ])->findOrFail($id);

        return Inertia::render('Apps/Approvals/Show', [
            'approval' => $approval
        ]);
    }
}
