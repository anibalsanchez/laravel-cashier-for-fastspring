<?php

namespace Photalika\CashierForFastspring\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Fastspring\ApiClient;
use Photalika\CashierForFastspring\Fastspring\Fastspring;

/**
 * This class just tests if fastspring class works as php code and receive mocked responses.
 * It does not test compability of requests to fastspring API.
 */
class FastspringTest extends TestCase
{
    public $fastspring;

    public static function setUpBeforeClass(): void
    {
        configureEnv();
    }

    protected function setUp(): void
    {
        // prepare class for testing
        $mockHandler = new MockHandler(array_fill(
            0,
            20,
            new Response(200, [], json_encode(['hello' => 'world']))
        ));

        $handlerStack = HandlerStack::create($mockHandler);

        // create instance
        $fastspring = new ApiClient;

        $this->fastspring = $fastspring;
        $this->fastspring->setClientOptions([
            'handler' => $handlerStack,
        ]);
    }

    public function test_api_client_builder_can_be_constructed(): void
    {
        $this->assertInstanceOf(ApiClient::class, new ApiClient('username', 'password'));
    }

    public function test_fastspring_facade(): void
    {
        // lets call it
        Fastspring::getAccounts();

        // check to instance type
        $this->assertInstanceOf(ApiClient::class, Fastspring::$instance);
    }

    public function test_global_query(): void
    {
        $apiClient = new ApiClient('username', 'password');
        $apiClient->setGlobalQuery(['cats' => 'areBetter']);

        // actually we should check it in the requests
        // but for now it is ok
        $this->assertArrayHasKey('cats', $apiClient->globalQuery);
        $this->assertEquals($apiClient->globalQuery['cats'], 'areBetter');
    }

    public function test_api_request(): void
    {
        $response = $this->fastspring->apiRequest(
            'POST',
            'something',
            ['query' => 'parameters'],
            ['form' => 'parameters'],
            ['json' => 'payload']
        );

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_create_account(): void
    {
        $response = $this->fastspring->createAccount([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_update_account(): void
    {
        $response = $this->fastspring->updateAccount('id', []);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_get_subscriptions(): void
    {
        $response = $this->fastspring->getSubscriptions([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_get_accounts(): void
    {
        $response = $this->fastspring->getAccounts([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_get_orders(): void
    {
        $response = $this->fastspring->getOrders([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_get_account(): void
    {
        $response = $this->fastspring->getAccount('id');

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_create_session(): void
    {
        $response = $this->fastspring->createSession([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_update_subscriptions(): void
    {
        $response = $this->fastspring->updateSubscriptions([]);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_cancel_subscription(): void
    {
        $response = $this->fastspring->cancelSubscription('id');

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_uncancel_subscription(): void
    {
        $response = $this->fastspring->uncancelSubscription('id');

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_swap_subscription(): void
    {
        $response = $this->fastspring->swapSubscription('id', 'new_plan', true);

        $this->assertObjectHasProperty('hello', $response);
    }

    public function test_get_account_management_uri(): void
    {
        $response = $this->fastspring->getAccountManagementURI('id');

        $this->assertObjectHasProperty('hello', $response);
    }
}
