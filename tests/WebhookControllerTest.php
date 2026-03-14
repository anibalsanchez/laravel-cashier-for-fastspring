<?php

namespace Photalika\CashierForFastspring\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Events;
use Photalika\CashierForFastspring\Tests\Fixtures\WebhookControllerTestStub;

class WebhookControllerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        configureEnv();
    }

    /**
     * Test HMAC.
     */
    public function test_hmac(): void
    {
        $hmacSecret = 'dontlookiamsecret';
        Config::set('services.fastspring.hmac_secret', $hmacSecret);

        $webhookRequestPayload = [
            'events' => [
                [
                    'id' => 'id-1',
                    'live' => true,
                    'processed' => false,
                    'type' => 'account.created',
                    'created' => 1426560444800,
                    'data' => [],
                ],
            ],
        ];

        $request = Request::create('/', 'POST', [], [], [], [], json_encode($webhookRequestPayload));
        $request->headers->set(
            'X-FS-Signature',
            base64_encode(hash_hmac('sha256', $request->getContent(), $hmacSecret, true))
        );

        $webhookControllerTestStub = new WebhookControllerTestStub;
        $response = $webhookControllerTestStub->handleWebhook($request);

        $this->assertEquals($response->getStatusCode(), 202);
    }

    /**
     * Test HMAC failed.
     *
     * @throws \Exception $exception
     */
    public function test_hmac_failed(): void
    {
        Config::set('services.fastspring.hmac_secret', 'dontlookiamsecret');
        $this->expectException(\Exception::class);

        $webhookRequestPayload = [
            'events' => [
                [
                    'id' => 'id-1',
                    'live' => true,
                    'processed' => false,
                    'type' => 'account.created',
                    'created' => 1426560444800,
                    'data' => [],
                ],
            ],
        ];

        $request = Request::create('/', 'POST', [], [], [], [], json_encode($webhookRequestPayload));
        $webhookControllerTestStub = new WebhookControllerTestStub;
        $webhookControllerTestStub->handleWebhook($request);
    }

    public function test_multiple_webhook_events(): void
    {
        $webhookRequestPayload = [
            'events' => [
                [
                    'id' => 'id-1',
                    'live' => true,
                    'processed' => false,
                    'type' => 'account.created',
                    'created' => 1426560444800,
                    'data' => [],
                ],
                [
                    'id' => 'id-2',
                    'live' => true,
                    'processed' => false,
                    'type' => 'subscription.activated',
                    'created' => 1426560444800,
                    'data' => [],
                ],
            ],
        ];

        $request = Request::create('/', 'POST', [], [], [], [], json_encode($webhookRequestPayload));
        $webhookControllerTestStub = new WebhookControllerTestStub;
        $response = $webhookControllerTestStub->handleWebhook($request);

        $content = $response->getContent();
        $statusCode = $response->getStatusCode();

        $this->assertEquals($statusCode, 202);
        $this->assertEquals($content, "id-1\nid-2");
    }

    /**
     * Test multiple webhook events by failing one.
     */
    public function test_multiple_webhook_events_by_failing_one(): void
    {
        $webhookRequestPayload = [
            'events' => [
                [
                    'id' => 'id-1',
                    'live' => true,
                    'processed' => false,
                    'type' => 'account.created',
                    'created' => 1426560444800,
                    'data' => [],
                ],
                [
                    'id' => 'id-2',
                    'live' => true,
                    'processed' => false,
                    'type' => 'subscription.notexistevent',
                    'created' => 1426560444800,
                    'data' => [],
                ],
            ],
        ];

        // since the second event doesn't exist
        // there will be error and we only see first one handled
        // alson in the content of the response

        $request = Request::create('/', 'POST', [], [], [], [], json_encode($webhookRequestPayload));
        $webhookControllerTestStub = new WebhookControllerTestStub;
        $response = $webhookControllerTestStub->handleWebhook($request);

        $content = $response->getContent();
        $statusCode = $response->getStatusCode();

        $this->assertEquals($statusCode, 202);
        $this->assertEquals($content, 'id-1');
    }

    /**
     * Test 406 Not Acceptable.
     */
    public function test_406_not_acceptable(): void
    {
        $request = Request::create('/', 'POST', [], [], [], [], 'plain text content');
        $request->headers->set('Content-Type', 'text/plain');

        $webhookControllerTestStub = new WebhookControllerTestStub;
        $response = $webhookControllerTestStub->handleWebhook($request);

        $this->assertEquals($response->getStatusCode(), 406);
    }

    /**
     * Webhook test events.
     */
    public function test_webhooks_events(): void
    {
        $webhookEvents = [
            'account.created',
            'fulfillment.failed',
            'mailingListEntry.removed',
            'mailingListEntry.updated',
            'order.approval.pending',
            'order.canceled',
            'order.payment.pending',
            'order.completed',
            'order.failed',
            'payoutEntry.created',
            'return.created',
            'subscription.activated',
            'subscription.canceled',
            'subscription.charge.completed',
            'subscription.charge.failed',
            'subscription.deactivated',
            'subscription.payment.overdue',
            'subscription.payment.reminder',
            'subscription.trial.reminder',
            'subscription.updated',
        ];

        foreach ($webhookEvents as $key => $webhookEvent) {
            $mockEvent = [
                'id' => 'id-'.$key,
                'live' => true,
                'processed' => false,
                'type' => $webhookEvent,
                'created' => 1426560444800,
                'data' => [],
            ];

            // prepare category event class names like OrderAny
            $explodedType = explode('.', $mockEvent['type']);
            $category = array_shift($explodedType);
            $categoryEvent = 'Photalika\CashierForFastspring\Events\\'.Str::studly($category).'Any';

            // prepare category event class names like activity
            $activity = str_replace('.', ' ', $mockEvent['type']);
            $activityEvent = 'Photalika\CashierForFastspring\Events\\'.Str::studly($activity);

            $listenEvents = [
                Events\Any::class,
                $categoryEvent,
                $activityEvent,
            ];

            $this->sendRequestAndListenEvents($mockEvent, $listenEvents);
        }
    }

    /**
     * Sends request and listen for events.
     *
     * @param  array  $mockEvent  The mock event array
     * @param  array  $listenEvents  The listen events
     * @return \Illuminate\Support\Facades\Event
     */
    protected function sendRequestAndListenEvents($mockEvent, $listenEvents)
    {
        Event::fake();

        $webhookRequestPayload = [
            'events' => [
                $mockEvent,
            ],
        ];

        $request = Request::create('/', 'POST', [], [], [], [], json_encode($webhookRequestPayload));
        $webhookControllerTestStub = new WebhookControllerTestStub;
        $webhookControllerTestStub->handleWebhook($request);

        foreach ($listenEvents as $listenEvent) {
            // Assert
            Event::assertDispatched(
                $listenEvent,
                fn ($event): bool => (int) $event->id === (int) $mockEvent['id']
            );
        }
    }
}
