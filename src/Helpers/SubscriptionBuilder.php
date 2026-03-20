<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Helpers;

use GuzzleHttp\Exception\ClientException;
use Photalika\CashierForFastspring\Fastspring\Fastspring;
use Photalika\CashierForFastspring\Models\Session;

/**
 * Front-end to create subscription objects step by step.
 */
class SubscriptionBuilder
{
    /**
     * The coupon code being applied to the billable.
     *
     * @var string|null
     */
    protected $coupon;

    /**
     * The contact details for the session.
     *
     * @var array|null
     */
    protected $contact;

    /**
     * The lookup details for the session.
     *
     * @var array|null
     */
    protected $lookup;

    /**
     * The items for the session.
     *
     * @var array
     */
    protected $items = [];

    /**
     * The tags for the session.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * Create a new subscription builder instance.
     *
     * @param  mixed  $owner  Owner details
     * @param  string  $name  Plan name
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
        protected $name
    ) {}

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
     * The contact to apply to a new subscription.
     *
     * @param  array  $contact
     * @return $this
     */
    public function withContact($contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * The lookup to apply to a new subscription.
     *
     * @param  array  $lookup
     * @return $this
     */
    public function withLookup($lookup): static
    {
        $this->lookup = $lookup;

        return $this;
    }

    /**
     * Add an item to the session.
     *
     * @param  string  $product
     * @param  int  $quantity
     * @param  array|null  $pricing
     * @param  array|null  $attributes
     * @return $this
     */
    public function addItem($product, $quantity = 1, $pricing = null, $attributes = null): static
    {
        $this->items[] = array_filter([
            'product' => $product,
            'quantity' => $quantity,
            'pricing' => $pricing,
            'attributes' => $attributes,
        ]);

        return $this;
    }

    /**
     * The tags to apply to a new subscription.
     *
     * @param  array  $tags
     * @return $this
     */
    public function withTags($tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Create a new Fastspring session and return it as object.
     *
     * @return \Photalika\CashierForFastspring\Fastspring\Fastspring
     */
    public function create(): Session
    {
        $fastspringId = $this->getFastspringIdOfBillable();

        return new Session(Fastspring::createSession($this->buildPayload($fastspringId)));
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
            'contact' => $this->contact,
            'lookup' => $this->lookup,
            'items' => $this->items,
            'tags' => array_merge($this->tags, ['name' => $this->name]),
            'coupon' => $this->coupon,
        ]);
    }
}
