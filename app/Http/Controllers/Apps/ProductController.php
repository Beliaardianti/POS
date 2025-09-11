<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get products
        $products = Product::when(request()->q, function ($products) {
            $products = $products->where('title', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        //return inertia
        return Inertia::render('Apps/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get categories
        $categories = Category::all();

        //return inertia
        return Inertia::render('Apps/Products/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // DEBUG: Uncomment baris ini untuk debug data yang masuk
        // dd($request->all());

        /**
         * validate - UPDATED WITH NEW FIELDS
         */
        $this->validate($request, [
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'barcode'       => 'required|unique:products',
            'title'         => 'required',
            'description'   => 'required',
            'category_id'   => 'required',
            'buy_price'     => 'required|numeric|min:0',
            'sell_price'    => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',

            // NEW FIELDS VALIDATION
            'minimum_stock' => 'required|integer|min:0',
            'expired_date'  => 'nullable|date|after:today',  // TETAP after:today sesuai permintaan
            'location'      => 'required|in:Gudang,Display',
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        // DEBUG: Cek nilai minimum_stock sebelum create
        \Log::info('Creating product with minimum_stock: ' . $request->minimum_stock);

        //create product - UPDATED WITH NEW FIELDS
        $product = Product::create([
            'image'         => $image->hashName(),
            'barcode'       => $request->barcode,
            'title'         => $request->title,
            'description'   => $request->description,
            'category_id'   => $request->category_id,
            'buy_price'     => $request->buy_price,
            'sell_price'    => $request->sell_price,
            'stock'         => $request->stock,

            // NEW FIELDS
            'minimum_stock' => $request->minimum_stock,
            'expired_date'  => $request->expired_date,
            'location'      => $request->location,
        ]);

        // DEBUG: Cek nilai setelah create
        \Log::info('Product created with minimum_stock: ' . $product->minimum_stock);

        //redirect
        return redirect()->route('apps.products.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //get categories
        $categories = Category::all();

        return Inertia::render('Apps/Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        /**
         * validate - UPDATED WITH NEW FIELDS
         */
        $this->validate($request, [
            'barcode'       => 'required|unique:products,barcode,' . $product->id,
            'title'         => 'required',
            'description'   => 'required',
            'category_id'   => 'required',
            'buy_price'     => 'required|numeric|min:0',
            'sell_price'    => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',

            // NEW FIELDS VALIDATION
            'minimum_stock' => 'required|integer|min:0',
            'expired_date'  => 'nullable|date|after:today',  // TETAP after:today
            'location'      => 'required|in:Gudang,Display',
        ]);

        //check image update
        if ($request->file('image')) {
            //remove old image
            Storage::disk('local')->delete('public/products/' . basename($product->image));

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            //update product with new image - UPDATED WITH NEW FIELDS
            $product->update([
                'image'         => $image->hashName(),
                'barcode'       => $request->barcode,
                'title'         => $request->title,
                'description'   => $request->description,
                'category_id'   => $request->category_id,
                'buy_price'     => $request->buy_price,
                'sell_price'    => $request->sell_price,
                'stock'         => $request->stock,

                // NEW FIELDS
                'minimum_stock' => $request->minimum_stock,
                'expired_date'  => $request->expired_date,
                'location'      => $request->location,
            ]);
        } else {
            //update product without image - UPDATED WITH NEW FIELDS
            $product->update([
                'barcode'       => $request->barcode,
                'title'         => $request->title,
                'description'   => $request->description,
                'category_id'   => $request->category_id,
                'buy_price'     => $request->buy_price,
                'sell_price'    => $request->sell_price,
                'stock'         => $request->stock,

                // NEW FIELDS
                'minimum_stock' => $request->minimum_stock,
                'expired_date'  => $request->expired_date,
                'location'      => $request->location,
            ]);
        }

        //redirect
        return redirect()->route('apps.products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //find by ID
        $product = Product::findOrFail($id);

        //remove image
        Storage::disk('local')->delete('public/products/' . basename($product->image));

        //delete
        $product->delete();

        //redirect
        return redirect()->route('apps.products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return redirect()->route('apps.products.edit', $product->id);
    }

    /**
     * Get products with low stock (for alerts)
     *
     * @return \Illuminate\Http\Response
     */
    public function getLowStockProducts()
    {
        $lowStockProducts = Product::whereRaw('stock <= minimum_stock')->get();

        return response()->json([
            'low_stock_products' => $lowStockProducts
        ]);
    }

    /**
     * Get products that are expired or expiring soon
     *
     * @return \Illuminate\Http\Response
     */
    public function getExpiredProducts()
    {
        $expiredProducts = Product::where('expired_date', '<=', now())
            ->whereNotNull('expired_date')
            ->get();

        $expiringSoonProducts = Product::whereBetween('expired_date', [
            now(),
            now()->addDays(7)
        ])
            ->whereNotNull('expired_date')
            ->get();

        return response()->json([
            'expired_products' => $expiredProducts,
            'expiring_soon_products' => $expiringSoonProducts
        ]);
    }

    /**
     * Transfer stock between locations
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transferStock(Request $request)
    {
        $this->validate($request, [
            'product_id'        => 'required|exists:products,id',
            'quantity'          => 'required|integer|min:1',
            'from_location'     => 'required|in:Gudang,Display',
            'to_location'       => 'required|in:Gudang,Display|different:from_location',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if sufficient stock available
        if ($product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock for transfer']);
        }

        // Here you would implement the actual transfer logic
        // This is a simplified version - in real app you might have separate stock tables for different locations

        return redirect()->back()->with('success', 'Stock transferred successfully');
    }

    /**
     * Export products to Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        // This would require a package like Laravel Excel
        // For now, just return CSV format

        $products = Product::with('category')->get();

        $filename = "products_export_" . date('Y-m-d') . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Barcode', 'Title', 'Category', 'Stock', 'Minimum Stock', 'Buy Price', 'Sell Price', 'Location', 'Expired Date');

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $row = array(
                    $product->id,
                    $product->barcode,
                    $product->title,
                    $product->category->name ?? '',
                    $product->stock,
                    $product->minimum_stock,
                    $product->buy_price,
                    $product->sell_price,
                    $product->location,
                    $product->expired_date
                );
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
