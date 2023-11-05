<?php

namespace App\Console\Commands\Payment;

use App\Enum\Payment\PaymentStatusEnum;
use App\Models\Subscription\SubscriptionList;
use App\Models\Subscriptions;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\User\Premuim;
use App\Services\Payment\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;

class StripeGetPayments extends Command
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stripe-get-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Stripe Payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/payments/stripe/stripe.log')
        ]);

        $log->info('Getting Updates...');

        foreach (Payment::query()->where('status', PaymentStatusEnum::WAITING_PAYMENT)->get() as $payment) {
            $log->info('Prepare payment: ' . $payment->id);
            if (Carbon::now()->diffInHours($payment->created_at) > 1) {
                $payment->status = PaymentStatusEnum::CANCEL;
                $payment->save();
                $log->info('Payment expired: ' . $payment->id);
                continue;
            }

            /** @var Session $stripePayment */
            $stripePayment = $this->stripeService->getPayment($payment);
            if ($stripePayment->status == 'complete' && $stripePayment->payment_status == 'paid') {
                $payment->status = PaymentStatusEnum::SUCCESS;
                $payment->save();
                $this->stripeService->prepare($payment, $log);
            }
        }
    }
}
