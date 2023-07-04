<?php

namespace App\Http\Controllers;


use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class CatalogController extends Controller
{
    public function index()
    {
        // корневые категории
        $roots = Category::where('parent_id', 0)->get();
        // популярные бренды
        $brands = Brand::popular();
        return view('catalog.index', compact('roots', 'brands'));
    }

    public function category(Category $category)
    {
        // получаем всех потомков этой категории
        $descendants = $category->getAllChildren($category->id);
        $descendants[] = $category->id;
        // товары этой категории и всех потомков
        $products = Product::whereIn('category_id', $descendants)->paginate(6);
        return view('catalog.category', compact('category', 'products'));
    }

    public function brand(Brand $brand)
    {
        $products = $brand->products()->paginate(6);
        return view('catalog.brand', compact('brand', 'products'));
    }

    public function product(Product $product)
    {
        return view('catalog.product', compact('product'));
    }
}
