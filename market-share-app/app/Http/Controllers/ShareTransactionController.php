<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;
use App\Models\OpenTransactions;
use App\Models\ClosedTransactions;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ListingsController;

class ShareTransactionController extends Controller
{
    /*public function balance()
    {
        
        $this->buyShares('test','35',100, false);
        $balance = \Auth::user()->account_balance;
        return view('/pages/testing/buy-shares')->with('balance', $balance);
    }*/

    public static function updateEquity(){
        /*Equity update routine*/
        /*Set equity to account balance*/
        db::table('users')->update(['equity' => db::raw('account_balance')]);
        /*Loop over open_transactions table and update equity in users table*/
        $transactions = db::table('open_transactions')->get();
        foreach ($transactions as $line) {
            $currentId = ($line->user_id);
            $currentPurchase = ($line->purchase_price);
            $currentQuantity = ($line->quantity);
            $currentEquity = $currentPurchase * $currentQuantity;
            $currentBalance = db::table('users')
            ->where('id', $currentId)
            ->value('account_balance');
            $newEquity = $currentBalance + $currentEquity;
            db::table('users')
            ->where('id', $currentId)
            ->update(['equity' => $newEquity]);
        }
    }

    public static function buyShares($stockCode,$price, $quantity)
    {
        $error = null;
        $user = Users::find(\Auth::user()->id);
        if($user == null)
        {
            //TODO: user not logged in
        }
        $commission = ShareTransactionController::buyingCommission($price, $quantity);
        $totalPrice = ($price * $quantity) + $commission;
        
        if($totalPrice > \Auth::user()->account_balance)
        {
            return false;
        }
        ShareTransactionController::adjustBalance($user->id,$totalPrice*-1);
        $user->save();
        //$openTransaction = 
        DB::table('open_transactions')->insert([
            'user_id' => $user->id,
            'is_short_selling' => 0,
            'asx_code' => $stockCode,
            'date_opened' => Carbon::now(),
            'purchase_price' => $price,
            'quantity' => $quantity,
            'buying_commission' => $commission 
        ]);
        ShareTransactionController::updateEquity();
        return true;
    }

    public static function sellShares($asxcode) {
        $price = ListingsController::getCurrentPrice($asxcode);
        $user = Users::find(\Auth::user()->id);
        # calculate sell price
        
        $json = DB::table('open_transactions')
            ->where('user_id', '=', $user->id)
            ->where('asx_code', '=', $asxcode)
            ->sum('quantity');
            
        $quantity = json_decode($json);
        if (empty($quantity)) {
            return false;
        }
        $commission = ShareTransactionController::sellingCommission($price, $quantity);
        $sellprice = ($price*$quantity)-$commission;
        
        # delete from open transactions
        DB::table('open_transactions')
            ->where('user_id', '=', $user->id)
            ->where('asx_code', '=', $asxcode)
            ->delete();
        
        # add to closed transactions
        DB::table('closed_transactions')->insert([
            'user_id' => $user->id,
            'asx_code' => $asxcode,
            'date_closed' => Carbon::now(),
            'sold_price' => $sellprice,
            'quantity' => $quantity,
            'selling_commission' => $commission
        ]);
        
        # update account balance
        ShareTransactionController::adjustBalance($user->id, $sellprice);
        ShareTransactionController::updateEquity();
        return true;

    }

    public static function buyingCommission($price, $quantity)
    {
        $fixed = 50;
        $percentage = 0.01; //1% commission on buying
        return ($fixed + ($percentage*$price*$quantity));
    }

    public static function sellingCommission($price, $quantity) {
        $fixed = 50;
        $percentage = 0.0025; //0.25% commission on selling
        return ($fixed + ($percentage*$price*$quantity));
    }

    public static function adjustBalance($userid, $amount) {
        // must pass through a negative amount for a deduction (buying)
        // positive amount for a credit (selling)
        $currbalance = \Auth::user()->account_balance;
        $newbalance = $currbalance + $amount;
        DB::table('users')
            ->where('id',$userid)
            ->update(['account_balance' => $newbalance]);
    }

    public function retListing($fid){
        return view('/pages/listing')->with('code', $code);
    }

    public function getrecenttransactions($id) {
        //List of last 5 Closed Transaactions
        $closed=DB::table('closed_transactions')->where('user_id', $id)->get()->sortByDesc('date_closed')->take(5);
        foreach ($closed as $line) {
            $code=($line->asx_code);
            $quantity=($line->quantity);
            $sold=($line->sold_price);
            $commission=($line->selling_commission);
            $date=($line->date_closed);
            echo "<tr>";
            echo "<td><a href='/listing/".$code."' onclick='retListing(".$code.")'>".$code."</a></td>";
            echo "<td>".$quantity."</td>";
            echo "<td>"."$".number_format($sold,2,'.',',')."</td>";
            echo "<td>"."$".number_format($commission,2,'.',',')."</td>";
            echo "<td>".date('d-m-Y', strtotime($date))."</td>";
            echo "</tr>";
        }
    }

}