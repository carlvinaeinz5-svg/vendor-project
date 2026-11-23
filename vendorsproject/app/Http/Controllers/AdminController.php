<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $this->authorizeAdmin();
        $stats=['vendors'=>Vendor::count(),'pending'=>Vendor::where('status','pending')->count(),'orders'=>Order::count(),'products'=>Product::count()];
        $applications=Vendor::with(['user','category'])->latest()->get();
        return view('dashboards.admin',compact('stats','applications'));
    }
    public function decideVendor(Request $request, Vendor $vendor)
    {
        $this->authorizeAdmin();
        $data=$request->validate([
            'status'=>'required|in:approved,suspended,pending',
            'stall_location'=>['nullable','string','max:255',Rule::unique('vendors','stall_location')->ignore($vendor->id)],
        ]);
        $updates = [
            'status'=>$data['status'],
            'approved_at'=>$data['status']==='approved'?now():null,
        ];
        if ($request->has('stall_location')) {
            $updates['stall_location'] = $data['stall_location'] ?? null;
        }
        $vendor->update($updates);
        return back()->with('success','Vendor status and stall allocation updated.');
    }

    public function categories()
    {
        $this->authorizeAdmin();
        $categories = Category::withCount(['vendors', 'products'])->orderBy('name')->get();

        return view('dashboards.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('categories', 'slug')],
        ]);

        Category::create($data);

        return back()->with('success', 'Goods category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('categories', 'slug')->ignore($category->id)],
        ]);

        $category->update($data);

        return back()->with('success', 'Goods category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $this->authorizeAdmin();
        if ($category->vendors()->exists() || $category->products()->exists()) {
            return back()->with('error', 'This category is in use by vendors or products and cannot be deleted.');
        }

        $category->delete();

        return back()->with('success', 'Goods category deleted.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }
}
