<?php

use Illuminate\Http\Request;

Route::group(['prefix'=>'mark'],function(){

Route::post('/create','MarkController@store')->name('mark.create');
Route::post('/show','MarkController@read')->name('mark.read');

Route::post('/delete','MarkController@delete_mark')->name('mark.delete');

Route::post('/edit','MarkController@edit_mark')->name('mark.edit');

});
Route::group(['prefix'=>'stock'],function(){

Route::post('/create','StockController@store')->name('stock.create');
Route::post('/read','StockController@read')->name('stock.read');

Route::post('/delete','StockController@delete_stock')->name('stock.delete');

Route::post('/edit','StockController@edit_stock')->name('stock.edit');

Route::post('/get-stock','StockController@get_stock')->name('getstock');
});
Route::group(['prefix'=>'bill'],function(){


  Route::post('/create','BillController@store')->name('bill.store');
  Route::post('/create-current','BillController@store_for_current_custoemr')->name('bill.current');
  Route::post('/delete','BillController@delete_bill')->name('bill.delete');
  Route::get('/edit','BillController@edit_bill')->name('bill.edit');
  //fetch customer_id
  Route::post('/search-customer','BillController@search_customer')->name('customer.read');
  //update bills
  Route::post('/edit-bill','BillController@edit_bill')->name('bill.edit');

  Route::post('/show-details','BillController@show_bill_details')->name('details.show');

  Route::post('/edit-details','BillController@edit_bill_details')->name('details.edit');

  Route::post('/delete-details','BillController@delete_bill_details')->name('details.delete');
  Route::post('/stock','BillController@get_stock')->name('details.stock');
  Route::post('/add-to-bill','BillController@add_to_bill')->name('details.add');


});
