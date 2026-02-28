<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring\Concerns;

use Exception;
use Photalika\CashierForFastspring\Fastspring\Fastspring;

trait ManagesAccount
{
    /**
     * Get the Fastspring account associated with the billable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function account()
    {
        return $this->morphOne(\Photalika\CashierForFastspring\Cashier::$accountModel, 'billable');
    }

    /**
     * Determine if the entity has a Fastspring billable ID.
     */
    public function hasFastspringId(): bool
    {
        return ! is_null($this->account?->fastspring_id);
    }

    /**
     * Generate authenticated url of fastspring account management panel.
     *
     * @return string
     */
    public function accountManagementURI()
    {
        $response = Fastspring::getAccountManagementURI($this->account->fastspring_id);

        return $response->accounts[0]->url;
    }

    /**
     * Create a Fastspring billable for the given billable model.
     *
     * @param  array  $options  Options array of billable information
     * @return object
     */
    public function createAsFastspringBillable(array $options = [])
    {
        $options = $options === [] ? [
            'contact' => [
                'first' => $this->extractFirstName(),
                'last' => $this->extractLastName(),
                'email' => $this->email,
                'company' => $this->company,
                'phone' => $this->phone,
            ],
            'language' => $this->language,
            'country' => $this->country,
        ] : $options;

        // Here we will create the billable instance on Fastspring and store the ID of the
        // billable from Fastspring. This ID will correspond with the Fastspring billable instances
        // and allow us to retrieve billables from Fastspring later when we need to work.
        $account = Fastspring::createAccount($options);

        $this->account()->create([
            'fastspring_id' => $account->account,
            'company' => $this->company,
            'phone' => $this->phone,
            'language' => $this->language,
            'country' => $this->country,
        ]);

        return $account;
    }

    /**
     * Update the related account on Fastspring, given billable-model.
     *
     * @param  array  $options  array of billable information
     * @return object
     *
     * @throws Exception No valid Fastspring ID
     */
    public function updateAsFastspringBillable(array $options = [])
    {
        if (! $this->hasFastspringId()) {
            throw new Exception('Billable has no fastspring_id');
        }

        $options = $options === [] ? [
            'contact' => [
                'first' => $this->extractFirstName(),
                'last' => $this->extractLastName(),
                'email' => $this->email,
                'company' => $this->company,
                'phone' => $this->phone,
            ],
            'language' => $this->language,
            'country' => $this->country,
        ] : $options;

        // update
        $response = Fastspring::updateAccount($this->account->fastspring_id, $options);

        return $response;
    }

    /**
     * Get the Fastspring billable for the model.
     *
     *
     * @return object
     *
     * @throws Exception No valid Fastspring ID
     */
    public function asFastspringBillable()
    {
        // check the fastspring_id first
        // if there is non, no need to try
        if (! $this->hasFastspringId()) {
            throw new Exception('Billable has no fastspring_id');
        }

        return Fastspring::getAccount($this->account->fastspring_id);
    }

    /**
     * Get the first name of the billable for the Fastspring API.
     */
    public function extractFirstName(): string
    {
        $parted = explode(' ', $this->name);
        $parted = array_filter($parted);

        if (count($parted) == 1) {
            return $parted[0] ?? '';
        }

        // get rid of the lastname
        array_pop($parted);

        // implode rest of it, so there may be more than one name
        return implode(' ', $parted);
    }

    /**
     * Get the last name of the billable for the Fastspring API.
     *
     * @return string
     */
    public function extractLastName()
    {
        $parted = explode(' ', $this->name);
        $parted = array_filter($parted);

        if (count($parted) == 1) {
            // unfortunately we should do this
            // because Fastspring create account API doesn't work without last name
            return 'Unknown';
        }

        // return last element
        return array_pop($parted);
    }
}
