<?php

namespace Photalika\CashierForFastspring\Fastspring;

use GuzzleHttp\Client;

/**
 * This class describes an api client.
 */
class ApiClient
{
    /**
     * The Fastspring API Username.
     *
     * @var string
     */
    public $username;

    /**
     * The Fastspring API password.
     *
     * @var string
     */
    public $password;

    /**
     * The Fastspring API Base Url.
     *
     * @var string
     */
    public $apiBase = 'https://api.fastspring.com';

    /**
     * Global queries to apply every requests.
     *
     * @var array
     */
    public $globalQuery = [];

    /**
     * Guzzle client options.
     * Can be used to test class or process.
     *
     * @var array
     */
    public $clientOptions = [];

    /**
     * Create a new Fastspring API interface instance.
     *
     * @param  string  $username  Fastspring API username
     * @param  string  $password  Fastspring API password
     * @return void
     */
    public function __construct($username = null, $password = null)
    {
        $this->username = $username ?: (getenv('FASTSPRING_USERNAME') ?: config('services.fastspring.username'));
        $this->password = $password ?: (getenv('FASTSPRING_PASSWORD') ?: config('services.fastspring.password'));
    }

    /**
     * Send a request to Fastspring API with given parameters.
     *
     * @param  string  $method  Method of HTTP request like PUT, GET, POST
     * @param  string  $path  Path of API
     * @param  array  $query  Query parameters array
     * @param  array  $formParameters  Form parameters
     * @param  array  $jsonPayload  Json payload
     * @return \GuzzleHttp\Psr7\Response
     */
    public function apiRequest(string $method, $path, $query = [], $formParameters = [], $jsonPayload = []): mixed
    {
        // prepare guzzle options
        $clientOptions = $this->clientOptions ?: ['base_uri' => $this->apiBase];

        // create guzzle instance
        $client = new Client($clientOptions);

        // delete first slash character
        $path = ltrim($path, '/');

        // prepare options
        $options = [
            'auth' => [$this->username, $this->password],
            'query' => $this->globalQuery,
        ];

        // set parameters
        $options['query'] = array_merge($options['query'], $query);

        if ($formParameters) {
            $options['form_params'] = $formParameters;
        }

        if ($jsonPayload) {
            $options['json'] = $jsonPayload;
        }

        // send request and get response
        $response = $client->request($method, $path, $options);

        // convert it to object
        return $this->handleResponse($response);
    }

    /**
     * Set guzzle client options.
     *
     * @param  array  $options  Guzzle client options
     *
     * @see http://docs.guzzlephp.org/en/latest/quickstart.html Quickstart
     * @see http://docs.guzzlephp.org/en/latest/testing.html Testing
     *
     * @return void
     */
    public function setClientOptions($options)
    {
        return $this->clientOptions = $options;
    }

    /**
     * Set global query items.
     *
     * @param  array  $query  Queries like ['mode' => 'test']
     * @return void
     */
    public function setGlobalQuery($query)
    {
        return $this->globalQuery = $query;
    }

    /**
     * Handle JSON response and convert it to array.
     *
     * @param  \GuzzleHttp\Psr7\Response  $response  Guzzle response
     * @return object
     */
    protected function handleResponse($response): mixed
    {
        $message = $response->getBody()->getContents();

        // json decode
        // we assume fastspring sends always json
        return json_decode($message);
    }

    /**
     * Create account.
     *
     * @param  array  $account  Account details
     *
     * @see https://developer.fastspring.com/reference/accounts Account details
     *
     * @return object Response of fastspring
     */
    public function createAccount($account): mixed
    {
        return $this->apiRequest('POST', 'accounts', [], [], $account);
    }

    /**
     * Update account.
     *
     * @param  string  $fastspringId  Fastspring ID of related account
     * @param  array  $account  Account details
     *
     * @see https://developer.fastspring.com/reference/accounts Account details
     *
     * @return object Response of fastspring
     */
    public function updateAccount($fastspringId, $account): mixed
    {
        return $this->apiRequest('POST', implode('/', ['accounts', $fastspringId]), [], [], $account);
    }

    /**
     * Get account list.
     *
     * @param  array  $parameters  Query parameters
     * @return object Response of fastspring
     */
    public function getAccounts($parameters = []): mixed
    {
        return $this->apiRequest('GET', 'accounts', $parameters, [], []);
    }

    /**
     * Get the account with the given id.
     *
     * @param  string|int  $accountId  ID of the account
     * @param  array  $parameters  Query Parameters
     * @return object Response of fastspring
     */
    public function getAccount($accountId, $parameters = []): mixed
    {
        return $this->apiRequest('GET', implode('/', ['accounts', $accountId]), $parameters, [], []);
    }

    /**
     * Create session.
     *
     * @param  array  $session  Sessions details
     *
     * @see https://developer.fastspring.com/reference/sessions Session details
     *
     * @return object Response of fastspring
     */
    public function createSession($session): mixed
    {
        return $this->apiRequest('POST', 'sessions', [], [], $session);
    }

    /**
     * Get orders.
     *
     * @param  array  $parameters  Query parameters
     * @return object Response of fastspring
     */
    public function getOrders($parameters = []): mixed
    {
        return $this->apiRequest('GET', 'accounts', $parameters, [], []);
    }

    /**
     * Get subscriptions.
     *
     * @param  array  $subscriptionIds  Fastspring ids of subscriptions
     *
     * @see https://developer.fastspring.com/reference/subscriptions
     *
     * @return object Response of fastspring
     */
    public function getSubscriptions($subscriptionIds): mixed
    {
        return $this->apiRequest('GET', implode(
            '/',
            ['subscriptions', implode(',', $subscriptionIds)]
        ), [], [], []);
    }

    /**
     * Get subscription, returns one instance.
     *
     * @param  array  $subscriptionId  Fastspring id of subscriptions
     *
     * @see https://developer.fastspring.com/reference/subscriptions
     *
     * @return object Response of fastspring
     */
    public function getSubscriptionsEntries($subscriptionIds): mixed
    {
        return $this->apiRequest('GET', implode(
            '/',
            ['subscriptions', implode(',', $subscriptionIds), 'entries']
        ), [], [], []);
    }

    /**
     * Update subscriptions.
     *
     * @param  array  $subscriptions  Data of all subscriptions wanted to be
     *                                updated (should include subscription => $id)
     *
     * @see https://developer.fastspring.com/reference/subscriptions
     *
     * @return object Response of fastspring
     */
    public function updateSubscriptions($subscriptions): mixed
    {
        return $this->apiRequest('POST', 'subscriptions', [], [], [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Cancel subscription.
     *
     * @param  string|int  $subscriptionId  ID of the subscription
     * @param  array  $parameters  Query Parameters for example to delete
     *                             immediately pass ['billingPeriod' => 0]
     * @return object Response of fastspring
     */
    public function cancelSubscription($subscriptionId, $parameters = []): mixed
    {
        return $this->apiRequest('DELETE', implode('/', ['subscriptions', $subscriptionId]), $parameters, [], []);
    }

    /**
     * Uncancel subscription.
     *
     * @param  string|int  $subscriptionId  ID of the subscription
     * @return object Response of fastspring
     */
    public function uncancelSubscription($subscriptionId): mixed
    {
        return $this->updateSubscriptions([
            [
                'subscription' => $subscriptionId,
                'deactivation' => null,
            ],
        ]);
    }

    /**
     * Get authenticated url of fastspring account management panel.
     *
     * @param  string|int  $accountId  ID of the account
     * @return object Response of fastspring
     */
    public function getAccountManagementURI($accountId): mixed
    {
        return $this->apiRequest('GET', implode('/', ['accounts', $accountId, 'authenticate']), [], [], []);
    }

    /**
     * Swap subscription to another plan.
     *
     * @param  string|int  $subscriptionId  ID of the subscription
     * @param  string  $newPlan  Name of the new plan
     * @param  bool  $prorate  Prorate parameter
     * @param  int  $quantity  Quantity of the product
     * @param  array  $coupons  Coupons wanted to be applied
     * @return object Returns JSON object from the updateSubscriptions method.
     */
    public function swapSubscription($subscriptionId, $newPlan, $prorate, $quantity = 1, $coupons = []): mixed
    {
        return $this->updateSubscriptions([
            [
                'subscription' => $subscriptionId,
                'product' => $newPlan,
                'quantity' => $quantity,
                'coupons' => $coupons,
                'prorate' => $prorate,
            ],
        ]);
    }
}
