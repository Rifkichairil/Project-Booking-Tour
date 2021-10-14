<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TravelPackage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psy\Readline\Transient;

use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    //
    public function index(Request $req, $id)
    {
        $item = Transaction::with('details', 'travel_package', 'user')->findOrFail($id);
        return view('pages.checkout', compact('item'));
    }

    public function process(Request $req, $id)
    {
        $travel_package = TravelPackage::findOrFail($id);
        $transaction    = Transaction::create([
            'travel_packages_id'    => $id,
            'users_id'              => Auth::user()->id,
            'additional_visa'       => 0,
            'transactional_total'   => $travel_package->price,
            'transactional_status'  => 'IN_CART'
        ]);

        TransactionDetail::create([
            'transactions_id'    => $transaction->id,
            'username'          => Auth::user()->username,
            'nationality'       => 'ID',
            'is_visa'           => false,
            'doe_passport'      => Carbon::now()->addYears(5),
        ]);

        return redirect()->route('checkout', $transaction->id);
    }

    public function create(Request $req, $id)
    {
        $req->validate([
            'username'  => 'required|string|exists:users,username',
            'is_visa'   => 'required|boolean',
            'doe_passport'  => 'required'
        ]);

        $data = $req->all();

        $data['transactions_id'] = $id;

        TransactionDetail::create($data);

        $transaction = Transaction::with('travel_package')->find($id);

        if ($req->is_visa) {
            # code...
            $transaction->transactional_total   += 190;
            $transaction->additional_visa       += 190;
        }
        $transaction->transactional_total += $transaction->travel_package->price;
        $transaction->save();

        return redirect()->route('checkout', $id);
    }

    public function remove(Request $req, $detail_id)
    {
        $item = TransactionDetail::findOrFail($detail_id);
        $transaction = Transaction::with('details', 'travel_package')
            ->findOrFail($item->transactions_id);

        if ($item->is_visa) {
            # code...
            $transaction->transactional_total   -= 190;
            $transaction->additional_visa       -= 100;
        }
        $transaction->transactional_total -= $transaction->travel_package->price;
        $transaction->save();

        $item->delete();

        return redirect()->route('checkout', $item->transactions_id);
    }

    public function success(Request $req, $id)
    {
        $transaction = Transaction::with('details', 'travel_package.galleries', 'user')->findOrFail($id);
        $transaction->transactional_status = 'PENDING';

        $transaction->save();

        // Set coonfigurasi midtrans
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // midtrans array data
        $midtrans_params = [
            'transaction_details' => [
                'order_id' => 'TEST-' . $transaction->id,
                'gross_amount' => (int) $transaction->transactional_total,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            "enabled_payments" => [
                "indomaret", "bca_klikbca", "bank_transfer", "gopay"
            ],
            'vtweb' => []
        ];
        // dd($midtrans_params);



        try {
            //Success
            // mengambil halaman payment midtrans

            $paymentUrl = Snap::createTransaction($midtrans_params)->redirect_url;
            // dd($paymentUrl);

            // redirect ke halaman selanjutanya
            header('Location: ' . $paymentUrl);
        } catch (Exception $e) {
            //throw $e;
            echo $e->getMessage();
        }

        // mail send
        // Mail::to($transaction->user)->send(
        //     new TransactionSuccess($transaction)
        // );

        // return view('pages.sukses');
    }
}
