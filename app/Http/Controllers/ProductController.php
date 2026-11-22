<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const ALLOWED_PER_PAGE = [10, 15, 25, 50];

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
            $perPage = 10;
        }

        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->filled('status') && in_array($request->input('status'), ['disponible', 'agotado'], true)) {
            $query->where('status', $request->input('status'));
        }

        $sort = (string) $request->input('sort', 'created_at_desc');

        match ($sort) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'stock_asc' => $query->orderBy('stock', 'asc'),
            'stock_desc' => $query->orderBy('stock', 'desc'),
            'created_at_asc' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $isAdmin = $request->user()?->role === 'admin';

        $buyerBalance = (float) session('buyer_balance', 1000.00);
        if (! session()->has('buyer_balance')) {
            session(['buyer_balance' => $buyerBalance]);
        }

        $totalSold = (int) Product::sum('sold_count');
        $totalEarned = (float) Product::sum('total_earned');

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'perPage' => $perPage,
            'allowedPerPage' => self::ALLOWED_PER_PAGE,
            'sort' => $sort,
            'isAdmin' => $isAdmin,
            'buyerBalance' => $buyerBalance,
            'totalSold' => $totalSold,
            'totalEarned' => $totalEarned,
            'filters' => [
                'search' => $request->input('search', ''),
                'category_id' => $request->input('category_id', ''),
                'min_price' => $request->input('min_price', ''),
                'max_price' => $request->input('max_price', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorizeAdmin($request);

        Product::create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorizeAdmin($request);

        $product->update($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeAdmin($request);

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function buy(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $qty = (int) $data['quantity'];

        if ($product->status !== 'disponible' || $product->stock < $qty) {
            return back()->with('error', 'No hay suficientes existencias para completar la compra.');
        }

        $totalPrice = round((float) $product->price * $qty, 2);
        $wallet = (float) session('buyer_balance', 1000.00);

        if ($wallet < $totalPrice) {
            return back()->with('error', 'Saldo insuficiente para comprar este producto.');
        }

        $newStock = $product->stock - $qty;

        $product->update([
            'stock' => $newStock,
            'sold_count' => (int) $product->sold_count + $qty,
            'total_earned' => round((float) $product->total_earned + $totalPrice, 2),
            'status' => $newStock > 0 ? 'disponible' : 'agotado',
        ]);

        session(['buyer_balance' => round($wallet - $totalPrice, 2)]);

        return back()->with('success', "Compraste {$qty} x {$product->name} por $" . number_format($totalPrice, 2) . '.');
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Solo el administrador puede gestionar productos.');
        }
    }
}