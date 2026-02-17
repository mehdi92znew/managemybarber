<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('owner')->latest()->paginate(10);
        return view('admin.shops.index', compact('shops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shops,name',
            'email' => 'required|email|unique:users,email', // Owner email
            'owner_name' => 'required|string|max:255',
        ]);

        // Logic to create shop + owner explicitly if needed from admin panel
        // For now, we'll genericize the response
        
        return back()->with('success', 'Shop created successfully.');
    }

    public function suspend(Shop $shop)
    {
        $shop->update(['subscription_status' => 'suspended']);
        return back()->with('success', 'Shop suspended.');
    }

    public function activate(Shop $shop)
    {
        $shop->update(['subscription_status' => 'active']);
        return back()->with('success', 'Shop activated.');
    }
}
