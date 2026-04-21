<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\CreateProductRequest;
use App\Http\Requests\Products\ProductsListRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     * listar produtos
     */
    public function index(ProductsListRequest $request)
    {
        $data = $request->validated();

        $products = Product::query()
            ->active()
            ->when($data->filled('slug'), fn($q) => $q->where('slug', $data->slug))
            ->when($data->filled('price_min'), fn($q) => $q->where('price', '>=', $data->price_min))
            ->when($data->filled('price_max'), fn($q) => $q->where('price', '<=', $data->price_max))
            ->select('slug', 'name', 'description', 'price', 'stock')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->success('Lista de nossos produtos', Response::HTTP_OK, [
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();

        $userId = $request->attributes->get('user_id');

        $isAdmin = User::admin()
            ->where('id', $userId)
            ->exists();

        if (!$isAdmin) {
            return $this->error('Apenas administradores podem criar produto', Response::HTTP_BAD_REQUEST);
        }

        $data['is_active'] = true;

        $product = Product::create($data);

        return $this->success('Produto criado com suecesso', Response::HTTP_CREATED, [
            'product' => $product,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
