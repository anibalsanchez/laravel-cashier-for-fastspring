<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring;

use GuzzleHttp\Exception\ClientException;
use Photalika\CashierForFastspring\Fastspring\Fastspring;

/**
 * Front-end to create subscription objects step by step.
 */
class SubscriptionBuilder
{
    /**
     * The quantity of the subscription.
     *
     * @var int
     */
    protected $quantity = 1;

    /**
     * The coupon code being applied to the billable.
     *
     * @var string|null
     */
    protected $coupon;

    /**
     * Create a new subscription builder instance.
     *
     * @param  mixed  $owner  Owner details
     * @param  string  $name  Plan name
     * @param  string  $plan  Plan
     * @return void
     */
    public function __construct(
        /**
         * The model that is subscribing.
         */
        protected $owner,
        /**
         * The name of the subscription.
         */
        protected $name,
        /**
         * The name of the plan being subscribed to.
         */
        protected $plan
    ) {}

    /**
     * Specify the quantity of the subscription.
     *
     * @param  int  $quantity  Number of items
     * @return $this
     */
    public function quantity($quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * The coupon to apply to a new subscription.
     *
     * @param  string  $coupon  Coupon string to use
     * @return $this
     */
    public function withCoupon($coupon): static
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * Create a new Fastspring session and return it as object.
     *
     * @return \Photalika\CashierForFastspring\Fastspring\Fastspring
     */
    public function create()
    {
        $fastspringId = $this->getFastspringIdOfBillable();

        return Fastspring::createSession($this->buildPayload($fastspringId));
    }

    /**
     * Get the fastspring id for the current user.
     *
     * If an email key exists in error node then we assume this error is related
     * to the fact there is already an account with this email in
     * fastspring-side error message. It will also returns account link but
     * messages are easily changable so we can't rely on that.
     *
     *
     * @return int|string
     *
     * @throws Exception
     */
    protected function getFastspringIdOfBillable()
    {
        if (! $this->owner->hasFastspringId()) {
            try {
                $billable = $this->owner->createAsFastspringBillable();
                $this->owner->load('account');
            } catch (ClientException $e) {
                // we should get its id and save it
                $response = $e->getResponse();
                $content = json_decode($response->getBody()->getContents());

                if (isset($content->error->email)) {
                    $response = Fastspring::getAccounts(['email' => $this->owner->email]);

                    if ($response->accounts) {
                        $account = $response->accounts[0];

                        // save it to eloquent model
                        $this->owner->account()->create([
                            'fastspring_id' => $account->id,
                        ]);
                        $this->owner->load('account');
                    }
                } else {
                    throw $e; // @codeCoverageIgnore
                }
            }
        }

        return $this->owner->account->fastspring_id;
    }

    /**
     * Build the payload for session creation.
     *
     * @param  int  $fastspringId  The fastspring identifier
     */
    protected function buildPayload($fastspringId): array
    {
        return array_filter([
            'account' => $fastspringId,
            'items' => [
                [
                    'product' => $this->plan,
                    'quantity' => $this->quantity,
                ],
            ],
            'tags' => [
                'name' => $this->name,
            ],
            'coupon' => $this->coupon,
        ]);
    }
}
