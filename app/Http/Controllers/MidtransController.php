<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use App\Mail\TransactionSuccess;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Midtrans\Notification as MidtransNotification;

class MidtransController extends Controller
{
    //
    public function nitificationHandler(Request $request)
    {
        // set config midtrans

        // Set coonfigurasi midtrans
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // buat instance midtrans notification
        $notification = new MidtransNotification();

        // Peah order id
        $order  = explode('-', $notification->order_id);

        $status     =  $notification->transaction_status;
        $type       =  $notification->payment_type;
        $fraud      =  $notification->fraud_status;
        $order_id   =  $order[1];


        // finding
        $transaction = Transaction::findOrFail($order_id);

        // handle
        if ($status == 'capture') {
            # code...
            if ($type == 'credit_card') {
                # code...
                if ($fraud == 'challenge') {
                    # code...
                    $transaction->transactional_status = 'CHALLENGE';
                } else {
                    $transaction->transactional_status = 'SUCCESS';
                }
            }
        } elseif ($status == 'settlement') {
            $transaction->transactional_status = 'SUCCESS';
        } elseif ($status == 'pending') {
            $transaction->transactional_status = 'PENDING';
        } elseif ($status == 'deny') {
            $transaction->transactional_status = 'FAILED';
        } elseif ($status == 'expire') {
            $transaction->transactional_status = 'EXPIRED';
        } elseif ($status == 'cancel') {
            $transaction->transactional_status = 'FAILED';
        }

        // mengubah payment satatus
        $transaction->save();

        // kirim email
        if ($transaction) {
            # code...
            if ($status == 'capture' && $fraud == 'accept') {
                # code...
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            } else if ($status == 'settlement') {
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            } else if ($status == 'success') {
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            } else if ($status == 'capture' && $fraud == 'challenge') {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'message' => 'midtrans payemtn challange'
                    ]
                ]);
            } else {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'message' => 'midtrans payemtn not settle'
                    ]
                ]);
            }

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'message' => 'midtrans Success'
                ]
            ]);
        }
    }

    public function finishRedirect(Request $request)
    {
        return view('pages.sukses');
    }
    public function unfinishRedirect(Request $request)
    {
        return view('pages.unfinish');
    }
    public function errorRedirect(Request $request)
    {
        return view('pages.failed');
    }
}
