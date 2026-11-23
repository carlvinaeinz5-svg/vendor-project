<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartContents;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartContents $cartContents, Request $request)
    {
        $cart = $cartContents->refresh($request->session()->get('cart', []));
        $request->session()->put('cart', $cart);
        $total=collect($cart)->sum(fn($line)=>$line['product']['price']*$line['quantity']);
        $count=collect($cart)->sum('quantity');
        $canCheckout = collect($cart)->every(
            fn ($line) => !empty($line['product']['available']) && $line['quantity'] <= ($line['stock'] ?? 0),
        );
        return view('cart',compact('cart','total','count','canCheckout'));
    }
    public function add(Request $request)
    {
        $data=$request->validate(['product_id'=>'required|integer','quantity'=>'required|integer|min:1|max:99']);
        $product=Product::where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->with('vendor')
            ->findOrFail($data['product_id']);
        $cart=session('cart',[]);
        $line=$cart[$product->id]??['product'=>['id'=>$product->id,'name'=>$product->name,'unit'=>$product->unit,'price'=>$product->price,'image'=>$product->image,'vendor'=>$product->vendor->store_name],'quantity'=>0];
        if ($line['quantity'] + $data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'Only '.$product->stock.' units are available.']);
        }
        $line['product']['image_url'] = $product->image_url;
        $line['product']['available'] = true;
        $line['stock'] = $product->stock;
        $line['quantity']+=$data['quantity']; $cart[$product->id]=$line; session(['cart'=>$cart]);
        return redirect()->route('cart')->with('success',$product->name.' added to your cart.');
    }
    public function update(Request $request)
    {
        $data=$request->validate(['product_id'=>'required|integer','quantity'=>'required|integer|min:1|max:99']);
        $product=Product::where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->findOrFail($data['product_id']);
        if ($data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'Only '.$product->stock.' units are available.']);
        }
        $cart=session('cart',[]);
        if(isset($cart[$data['product_id']])){$cart[$data['product_id']]['quantity']=$data['quantity'];session(['cart'=>$cart]);}
        return back();
    }
    public function remove(Request $request)
    {
        $cart=session('cart',[]); unset($cart[$request->integer('product_id')]); session(['cart'=>$cart]);
        return back()->with('success','Item removed.');
    }
}
