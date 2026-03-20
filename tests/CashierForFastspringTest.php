<?php

namespace Photalika\CashierForFastspring\Tests;

use Exception;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Helpers\SubscriptionBuilder;
use Photalika\CashierForFastspring\Tests\Traits\Database;
use Photalika\CashierForFastspring\Tests\Traits\Guzzle;
use Photalika\CashierForFastspring\Tests\Traits\Model;

/**
 * This class tests general process of cashier over Billable trait.
 */
class CashierForFastspringTest extends TestCase
{
    use Database;
    use Guzzle;
    use Model;

    public static function setUpBeforeClass(): void
    {
        configureEnv();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Eloquent::unguard();

        // create tables
        $this->createUsersTable();
        $this->createAccountsTable();
        $this->createSubscriptionsTable();
        $this->createSubscriptionPeriodsTable();
        $this->createInvoicesTable();
    }

    /**
     * Tests.
     */
    public function test_subscription_builder_can_be_constructed(): void
    {
        $this->assertInstanceOf(SubscriptionBuilder::class, new SubscriptionBuilder('owner', 'name'));
    }

    public function test_create_session(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $session = $user->newSubscription('main')->addItem('starter-plan')->create();

        $this->assertObjectHasProperty('id', $session);
    }

    public function test_create_session_with_coupon(): void
    {
        $transactions = [];
        $history = Middleware::history($transactions);
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ], $history);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $session = $user->newSubscription('main')
            ->addItem('starter-plan', 1)
            ->withCoupon('free-php-coupon')
            ->create();

        $body = (string) $transactions[0]['request']->getBody();
        $requestParameters = json_decode($body, true);

        $this->assertEquals('starter-plan', $requestParameters['items'][0]['product']);
        $this->assertEquals(1, $requestParameters['items'][0]['quantity']);
        $this->assertEquals('main', $requestParameters['tags']['name']);
        $this->assertEquals('free-php-coupon', $requestParameters['coupon']);
        $this->assertObjectHasProperty('id', $session);
    }

    public function test_create_session_with_full_payload(): void
    {
        $transactions = [];
        $history = Middleware::history($transactions);
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ], $history);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $contact = [
            'first' => 'Jane',
            'last' => 'Doe',
            'email' => 'jane.doe@example.com',
            'company' => 'TechCorp',
            'phone' => '1234567890',
            'country' => 'US',
            'language' => 'en',
        ];

        $lookup = [
            'custom' => 'customKey123',
        ];

        $pricing = [
            'price' => [
                'USD' => 399.99,
            ],
        ];

        $session = $user->newSubscription('main')
            ->addItem('starter-plan', 1, $pricing)
            ->withContact($contact)
            ->withLookup($lookup)
            ->create();

        $body = (string) $transactions[0]['request']->getBody();
        $requestParameters = json_decode($body, true);

        $this->assertEquals($contact, $requestParameters['contact']);
        $this->assertEquals($lookup, $requestParameters['lookup']);
        $this->assertEquals($pricing, $requestParameters['items'][0]['pricing']);
        $this->assertObjectHasProperty('id', $session);
    }

    public function test_create_session_with_complex_payload(): void
    {
        $transactions = [];
        $history = Middleware::history($transactions);
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ], $history);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $tags = [
            'TagKey1' => 'TagValue1',
            'TagKey2' => 'TagValue2',
        ];

        $pricing = [
            'trial' => 0,
            'paymentCollected' => true,
            'paidTrial' => false,
            'renew' => 'auto',
            'interval' => 'month',
            'intervalLength' => 1,
            'quantityBehavior' => 'allow',
            'quantityDefault' => 1,
            'price' => [
                'USD' => 399.99,
                'EUR' => 384.36,
            ],
            'discountType' => 'percent',
            'discountDuration' => 2,
            'quantityDiscounts' => [
                '1' => 50,
            ],
        ];

        $attributes1 = [
            'AttributeKey1' => 'AttributeValue1',
            'AttributeKey2' => 'AttributeValue2',
        ];

        $attributes2 = [
            'ATTRIBUTE1' => 'value1',
        ];

        $session = $user->newSubscription('main')
            ->withTags($tags)
            ->addItem('product-path', 1, null, $attributes1)
            ->addItem('some-monthly-subscription', 1, $pricing, $attributes2)
            ->create();

        $body = (string) $transactions[0]['request']->getBody();
        $requestParameters = json_decode($body, true);

        $this->assertEquals($tags['TagKey1'], $requestParameters['tags']['TagKey1']);
        $this->assertEquals('main', $requestParameters['tags']['name']);
        $this->assertEquals('product-path', $requestParameters['items'][0]['product']);
        $this->assertEquals($attributes1, $requestParameters['items'][0]['attributes']);
        $this->assertEquals('some-monthly-subscription', $requestParameters['items'][1]['product']);
        $this->assertEquals($pricing, $requestParameters['items'][1]['pricing']);
        $this->assertEquals($attributes2, $requestParameters['items'][1]['attributes']);
        $this->assertObjectHasProperty('id', $session);
    }

    public function test_create_as_fastspring_billable(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode(['account' => 'fastspring_id'])),
        ]);

        $user = $this->createUser();

        $account = $user->createAsFastspringBillable();

        $this->assertObjectHasProperty('account', $account);
        $this->assertEquals($user->account->fastspring_id, 'fastspring_id');
    }

    public function test_create_session_without_fastspring_id(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode(['account' => 'fastspring_id'])),
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser();

        $session = $user->newSubscription('main')->addItem('starter-plan')->create();

        $this->assertObjectHasProperty('id', $session);
        $this->assertEquals($user->account->fastspring_id, 'fastspring_id');
    }

    public function test_create_session_with_lost_fastspring_id(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(407, [], json_encode([
                'error' => [
                    'email' => 'Email is already in use.',
                ],
            ])),
            new Response(200, [], json_encode(['accounts' => [
                ['id' => 'fastspring_id'],
            ],
            ])),
            new Response(200, [], json_encode([
                'id' => 'gNFgV9ITTbyylJkiIhnmOQ',
                'currency' => 'USD',
                'expires' => 1731469407156,
                'order' => null,
                'account' => 'abCdE1FGH2Hij3KLMnOpqR',
                'subtotal' => 399.99,
                'items' => [
                    [
                        'product' => 'basic-laptop',
                        'quantity' => 1,
                    ],
                ],
            ])),
        ]);

        $user = $this->createUser();

        $session = $user->newSubscription('main')->addItem('starter-plan')->create();

        $this->assertObjectHasProperty('id', $session);
        $this->assertEquals($user->account->fastspring_id, 'fastspring_id');
    }

    public function test_update_as_fastspring_billable(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([['account' => 'fastspring_id']])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $account = $user->updateAsFastspringBillable();

        $this->assertIsArray($account);
        $this->assertObjectHasProperty('account', $account[0]);
    }

    public function test_update_as_fastspring_billable_without_fastspring_id(): void
    {
        $this->expectException(Exception::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([['account' => 'fastspring_id']])),
        ]);

        $user = $this->createUser();

        $user->updateAsFastspringBillable();
    }

    public function test_as_fastspring_billable(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([['account' => 'fastspring_id']])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $account = $user->asFastspringBillable();

        $this->assertIsArray($account);
        $this->assertObjectHasProperty('account', $account[0]);
    }

    public function test_get_account_management_uri(): void
    {
        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode(['accounts' => [['url' => 'url']]])),
        ]);

        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);

        $url = $user->accountManagementURI();

        $this->assertEquals($url, 'url');
    }

    public function test_as_fastspring_billable_without_fastspring_id(): void
    {
        $this->expectException(Exception::class);

        $this->setMockResponsesAndHistory([
            new Response(200, [], json_encode([['account' => 'fastspring_id']])),
        ]);

        $user = $this->createUser();

        $user->asFastspringBillable();
    }

    // improve
    public function test_subscription(): void
    {
        $user = $this->createUser([
            'fastspring_id' => 'fastspring_id',
        ]);
        $this->createSubscription($user);

        $isSubscribedNotExist = $user->subscribed('notexist');
        $isSubscribed = $user->subscribed('main');
        $isSubscribedToPlan = $user->subscribedToPlan(['starter-plan'], 'main');
        $isSubscribedToPlanWithoutSubscription = $user->subscribedToPlan(['starter-plan']);
        $isSubscribedToPlanWithoutPlans = $user->subscribedToPlan([], 'main');
        $isSubscribedWithPlanParameter = $user->subscribed('main', 'starter-plan');
        $isSubscribedWithFakePlanParameter = $user->subscribed('main', 'non-plan');
        $subscription = $user->subscription('main');
        $subscriptions = $user->subscriptions();
        $onTrial = $user->onTrial('main', 'starter-plan');
        $onTrialWithoutPlan = $user->onTrial('main');
        $onPlan = $user->onPlan('starter-plan');

        $this->assertFalse($isSubscribedNotExist);
        $this->assertFalse($isSubscribedWithFakePlanParameter);
        $this->assertTrue($isSubscribed);
        $this->assertTrue($isSubscribedToPlan);
        $this->assertTrue($isSubscribedWithPlanParameter);
        $this->assertIsObject($subscription);
        $this->assertEquals($subscription->plan, 'starter-plan');
        $this->assertEquals($subscriptions->count(), 1);
        $this->assertFalse($onTrial);
        $this->assertFalse($onTrialWithoutPlan);
        $this->assertFalse($isSubscribedToPlanWithoutSubscription);
        $this->assertTrue($onPlan);
        $this->assertFalse($isSubscribedToPlanWithoutPlans);
    }

    public function test_has_fastspring_id(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->hasFastspringId());

        $user->account()->create(['fastspring_id' => 'fastspring_id']);

        // refresh relation
        $user->load('account');

        $this->assertTrue($user->hasFastspringId());
    }

    public function test_get_first_and_last_name(): void
    {
        $user = $this->createUser([
            'email' => 'first.middle@last.com',
            'name' => 'First Middle Last',
        ]);

        $user2 = $this->createUser([
            'email' => 'first@last.com',
            'name' => 'First Last',
        ]);

        $user3 = $this->createUser([
            'email' => 'first@last.com',
            'name' => 'First',
        ]);

        $user4 = $this->createUser([
            'email' => 'first.space.middle.space@last.com',
            'name' => 'First  Middle  Last',
        ]);

        $this->assertEquals($user->extractFirstName(), 'First Middle');
        $this->assertEquals($user2->extractFirstName(), 'First');
        $this->assertEquals($user3->extractFirstName(), 'First');
        $this->assertEquals($user4->extractFirstName(), 'First Middle');

        $this->assertEquals($user->extractLastName(), 'Last');
        $this->assertEquals($user2->extractLastName(), 'Last');
        $this->assertEquals($user3->extractLastName(), 'Unknown');
        $this->assertEquals($user4->extractLastName(), 'Last');
    }
}
