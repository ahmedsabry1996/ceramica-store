<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bill;
use App\Stock;
use App\Customer;
use App\BillDetails;

class BillController extends Controller
{
    //

    public function store(Request $request)
    {
      $validate_datac = $request->validate([
        'stock'=>'required',
        'total'=>'required',
        'paid'=>'required',
        'remain'=>'required',

      ]);




      // Customer data
      $customer = Customer::create([
        'name'=>$request->name,
        'address'=>$request->address,
        'phone'=>$request->phone,

      ]);


      $remain = ($request->total) - ($request->paid) ;
      //Bill data
      $bill = Bill::create([
        'customer_id'=>$customer->id,
        'paid'=>$request->paid,
        'total'=>$request->total,
        'remain'=>$request->remain,
      ]);

      $b_id = $bill->id;

            /* LOOP
            'remain'=>$request->remain,
                   sotck_ids,
                    quantity,
            */
            for ($i=0; $i < count($request->stock); $i++) {

              //Bill details
              $bill = BillDetails::create([
                'bill_id'=>$b_id,
                'stock_id'=>$request->stock[$i],
                'quantity'=>$request->quantity[$i],
              ]);
              $stock = Stock::findOrFail($request->stock[$i]);
              $stock->quantity -=$request->quantity[$i];
              $stock->save();

            }

      return response()->json(['new_bill'=>$bill],201);
}

  public function store_for_current_custoemr(Request $request)
  {

    $validate_datac = $request->validate([
      'stock'=>'required',
      'total'=>'required',
      'paid'=>'required',
      'remain'=>'required',
      'customer_id'=>'required'

    ]);

    $customer_id = $request->customer_id;

    $remain = ($request->total) - ($request->paid) ;
    //Bill data
    $bill = Bill::create([
      'customer_id'=>$customer_id,
      'paid'=>$request->paid,
      'total'=>$request->total,
      'remain'=>$request->remain,
    ]);

    $b_id = $bill->id;

          for ($i=0; $i < count($request->stock); $i++) {

            //Bill details
            $bill = BillDetails::create([
              'bill_id'=>$b_id,
              'stock_id'=>$request->stock[$i],
              'quantity'=>$request->quantity[$i],
            ]);

            $stock = Stock::findOrFail($request->stock[$i]);
            $stock->quantity -=$request->quantity[$i];
            $stock->save();

          }

  }


public function search_customer(Request $request)
{
  $customer = Customer::where('name','like',"%$request->search%")
                      ->orWhere('phone','like',"%$request->search%")
                      ->with('bills')
                      ->limit(5)
                      ->get();
                  return response()->json(['customer'=>$customer],201);

}

  public function edit_bill(Request $request)
  {

      $id = $request->id;

      $current_bill = Bill::findOrFail($id);

      $current_bill->paid += $request->paid;

      $current_bill->remain = $request->remain;

      $current_bill->save();


      return response()->json(['current_bill'=>$current_bill],200);

  }


  public function show_bill_details(Request $request)
  {

      $bill_id = $request->bill_id;

      $bill = Bill::findOrFail($bill_id);
      $bill_details = $bill->details;
      $bill_customer = $bill->customer;


      return response()->json(['bill'=>$bill,'data'=>$bill_details,'customer'=>$bill_customer,$bill_id],200);


  }
  public function edit_bill_details(Request $request)
  {

    $detail_id = $request->detail_id;
    $bill_id = $request->bill_id;

    $quantiy = $request->quantity;


    $bill = Bill::findOrFail($bill_id);

    $detail = BillDetails::findOrFail($detail_id);

    if ($quantiy > $detail->quantity) {

      //edit details
      $q_diff =  abs($quantiy - $detail->quantity)  + $detail->quantity;
      $detail->quantity = $q_diff;

      $stock_id = $detail->stock_id;
        $stock = Stock::findOrFail($stock_id);

              if ($q_diff > $stock->quantity ) {
                return response()->json(['t'=>'big','msg'=>'الكمية المعطاة اكبر من الموجودة في المخزن'],422);

              }

      $detail->save();


      //edit bill

      $stock = Stock::findOrFail($stock_id);


      $edit_total = $stock->price * $q_diff * $stock->size;

      $bill->total += $edit_total;

      $bill->remain = $bill->total - $bill->paid;
      $bill->save();

      //apply in stock
      $stock->quantity -= $q_diff;
      $stock->save();
    }

    elseif ($quantiy < $detail->quantity) {
      //edit details
      $q_diff =  $detail->quantity - $quantiy ;
      $detail->quantity -= $q_diff;
      $stock_id = $detail->stock_id;
      $stock = Stock::findOrFail($stock_id);

            if ($q_diff > $stock->quantity ) {
              return response()->json(['dee'=>$stock->quantity,'msg'=>'الكمية المعطاة اكبر من الموجودة في المخزن'],422);
            }

      $detail->save();


      //edit bill
      $stock = Stock::findOrFail($stock_id);

      $edit_total = -($stock->price * $q_diff * $stock->size);

      $bill->total += $edit_total;

      $bill->remain = $bill->total - $bill->paid;

      $bill->save();

      //apply in stock
      $stock->quantity += $q_diff;
      $stock->save();

    }
    return response()->json(['bill'=>$bill,'detail'=>$quantiy],200);
  }

  public function delete_bill_details(Request $request)
  {
        $detail_id = $request->id;
        $stock_id = $request->stock_id;
        $bill_id = $request->bill_id;

        //get it
        $current_detail = BillDetails::findOrFail($detail_id);


        //stock

        $stock = Stock::findOrFail($stock_id);

        //back quantity
        $stock->quantity += $current_detail->quantity;


        //back money
        $edit_total = $current_detail->quantity * $stock->price * $stock->size;


        $bill = Bill::findOrFail($bill_id);

        $bill->total -= $edit_total;

        $bill->remain = $bill->total - $bill->paid;
        //done
        $bill->save();

        $stock->save();

        $current_detail->delete();

        return response()->json(['msg'=>'deleed'],201);
  }


  public function get_stock(Request $request)
  {

      $bill_id = $request->bill_id;

      $stock_ids = BillDetails::where('bill_id',$bill_id)
                                ->pluck('stock_id');


      $stock = Stock::with('mark')->whereNotIn('id',$stock_ids)->where('quantity','>',0)->get();
        return response()->json(['stock'=>$stock],200);

  }

  public function add_to_bill(Request $request)
  {
    $bill_id = $request->bill_id;

    $total = $request->total;
    for ($i=0; $i < count($request->stock); $i++) {

      //Bill details
      $bill = BillDetails::create([
        'bill_id'=>$bill_id,
        'stock_id'=>$request->stock[$i],
        'quantity'=>$request->quantity[$i],
      ]);
      $stock = Stock::findOrFail($request->stock[$i]);
      $stock->quantity -=$request->quantity[$i];
      $stock->save();

    }
    $bill = Bill::findOrFail($bill_id);

    $bill->total += $total;
    $bill->remain = $bill->total - $bill->paid;

    $bill->save();

    return response()->json(['msg'=>$bill->total],201);

  }


}
