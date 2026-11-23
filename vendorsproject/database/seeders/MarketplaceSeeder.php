<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produce = Category::create(['name'=>'Fresh produce','slug'=>'fresh-produce']);
        $household = Category::create(['name'=>'Household goods','slug'=>'household-goods']);
        $textiles = Category::create(['name'=>'Clothing & textiles','slug'=>'clothing-textiles']);

        $admin = User::create(['name'=>'Marketplace Admin','email'=>'admin@nakawa.test','phone'=>'0700000001','role'=>'admin','password'=>Hash::make('password')]);
        $customer = User::create(['name'=>'Amina Nakimbugwe','email'=>'customer@nakawa.test','phone'=>'0700000002','role'=>'customer','password'=>Hash::make('password')]);
        $tom = User::create(['name'=>'Tom Samula','email'=>'tom@nakawa.test','phone'=>'0700000003','role'=>'vendor','password'=>Hash::make('password')]);
        $sarah = User::create(['name'=>'Sarah Nakato','email'=>'sarah@nakawa.test','phone'=>'0700000004','role'=>'vendor','password'=>Hash::make('password')]);
        $dickson = User::create(['name'=>'Dickson Lubongo','email'=>'dickson@nakawa.test','phone'=>'0700000005','role'=>'vendor','password'=>Hash::make('password')]);

        $tomVendor = Vendor::create(['user_id'=>$tom->id,'category_id'=>$produce->id,'store_name'=>'Tom Samula Fresh Produce','stall_location'=>'Lane A, Stall 12','phone'=>'0700000003','status'=>'approved','approved_at'=>now()]);
        $sarahVendor = Vendor::create(['user_id'=>$sarah->id,'category_id'=>$household->id,'store_name'=>'Sarah Household Store','stall_location'=>'Lane B, Stall 08','phone'=>'0700000004','status'=>'approved','approved_at'=>now()]);
        $dicksonVendor = Vendor::create(['user_id'=>$dickson->id,'category_id'=>$textiles->id,'store_name'=>'Dickson Textiles','stall_location'=>'Lane C, Stall 21','phone'=>'0700000005','status'=>'approved','approved_at'=>now()]);

        $tomatoes = Product::create(['vendor_id'=>$tomVendor->id,'category_id'=>$produce->id,'name'=>'Fresh Tomatoes','unit'=>'1kg','description'=>'Fresh, locally sourced tomatoes from Nakawa Market.','price'=>5000,'stock'=>40,'image'=>'tomatoes.jpg']);
        $spinach = Product::create(['vendor_id'=>$tomVendor->id,'category_id'=>$produce->id,'name'=>'Fresh Spinach','unit'=>'1 bunch','description'=>'Fresh green spinach, ready for cooking.','price'=>2000,'stock'=>30,'image'=>'spinach.jpg']);
        $bucket = Product::create(['vendor_id'=>$sarahVendor->id,'category_id'=>$household->id,'name'=>'Plastic Bucket','unit'=>'10L','description'=>'Strong, easy-clean 10 litre household bucket.','price'=>12000,'stock'=>18,'image'=>'bucket.jpg']);
        $pot = Product::create(['vendor_id'=>$sarahVendor->id,'category_id'=>$household->id,'name'=>'Cooking Pot','unit'=>'5L','description'=>'Durable aluminium cooking pot for everyday use.','price'=>28000,'stock'=>9,'image'=>'cooking pot.jpg']);
        $shirt = Product::create(['vendor_id'=>$dicksonVendor->id,'category_id'=>$textiles->id,'name'=>'Cotton Shirt','unit'=>'Medium','description'=>'Comfortable cotton shirt available in medium size.','price'=>25000,'stock'=>12,'image'=>'shirts.jpg']);
        $fabric = Product::create(['vendor_id'=>$dicksonVendor->id,'category_id'=>$textiles->id,'name'=>'Kitenge Fabric','unit'=>'2 metres','description'=>'Colourful two-metre kitenge fabric.','price'=>22000,'stock'=>15,'image'=>'shirts.jpg']);

        $order = Order::create(['number'=>'ORD-2026-0147','customer_id'=>$customer->id,'customer_name'=>'Amina Nakimbugwe','phone'=>'0700000002','delivery_note'=>'Collect from the market','payment_method'=>'Cash on delivery / collection','total'=>47000,'status'=>'in_progress']);
        $items = [
            [$tomVendor,$tomatoes,2,5000,'confirmed'],
            [$sarahVendor,$bucket,1,12000,'pending'],
            [$dicksonVendor,$shirt,1,25000,'cancelled'],
        ];
        foreach ($items as [$vendor,$product,$quantity,$unitPrice,$status]) {
            OrderItem::create(['order_id'=>$order->id,'vendor_id'=>$vendor->id,'product_id'=>$product->id,'quantity'=>$quantity,'unit_price'=>$unitPrice,'subtotal'=>$quantity*$unitPrice,'status'=>$status]);
            Payment::create(['order_id'=>$order->id,'vendor_id'=>$vendor->id,'method'=>'Cash on delivery / collection','amount'=>$quantity*$unitPrice,'status'=>$status==='cancelled'?'cancelled':'pending']);
        }
    }
}
