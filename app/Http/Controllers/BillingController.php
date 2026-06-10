<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\BillingPortal\Session as PortalSession;

class BillingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $billing = $user->billing;

        Stripe::setApiKey(config('billing.stripe_secret'));

        $invoices = [];

        if ($billing && $billing->stripe_customer_id) {
            $stripeInvoices = Invoice::all([
                'customer' => $billing->stripe_customer_id,
                'limit' => 10,
            ]);

            foreach ($stripeInvoices->data as $invoice) {
                $invoices[] = [
                    'date' => date('d M Y', $invoice->created),
                    'number' => $invoice->number ?? $invoice->id,
                    'status' => $invoice->status,
                    'total' => '$' . number_format(($invoice->total ?? 0) / 100, 2),
                    'url' => $invoice->hosted_invoice_url,
                ];
            }
        }

        return view('studio.subscription', [
            'invoices' => $invoices,
        ]);
    }

    public function checkoutPro()
    {
        $user = Auth::user();
        $billing = $user->billing;

        Stripe::setApiKey(config('billing.stripe_secret'));

        $session = CheckoutSession::create([
            'mode' => 'subscription',
            'customer' => $billing->stripe_customer_id,
            'line_items' => [[
                'price' => config('billing.pro_price_id'),
                'quantity' => 1,
            ]],
            'success_url' => route('billing.index') . '?checkout=success',
            'cancel_url' => route('billing.index') . '?checkout=cancelled',
            'metadata' => [
                'livelatch_user_id' => $user->id,
                'supabase_user_id' => $user->supabase_user_id,
            ],
        ]);

        return redirect($session->url);
    }

    public function portal()
    {
        $user = Auth::user();
        $billing = $user->billing;

        Stripe::setApiKey(config('billing.stripe_secret'));

        $session = PortalSession::create([
            'customer' => $billing->stripe_customer_id,
            'return_url' => route('billing.index'),
        ]);

        return redirect($session->url);
    }
}