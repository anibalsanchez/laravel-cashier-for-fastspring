<?php

namespace Photalika\CashierForFastspring\Tests;

use Orchestra\Testbench\TestCase;
use Photalika\CashierForFastspring\Events\Models\Order;
use Photalika\CashierForFastspring\Events\OrderCompleted;

class OrderCompletedTest extends TestCase
{
    public function test_order_method_returns_an_order_model_with_payload_data(): void
    {
        $template = file_get_contents(__DIR__.'/Payloads/order_completed.json');
        $payload = json_decode($template, true);

        $orderCompleted = new OrderCompleted('id', 'order.completed', false, true, time(), $payload);

        $order = $orderCompleted->order();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('R6hr8F6USNixHu9fmdyjAw', $order->id);
        $this->assertEquals('FUR170801-8970-10118', $order->reference);
        $this->assertNull($order->buyerReference);
        $this->assertTrue($order->completed);
        $this->assertEquals('en', $order->language);
        $this->assertEquals('USD', $order->currency);
        $this->assertEquals(14.95, $order->total);
        $this->assertEquals(0.0, $order->tax);
        $this->assertEquals(14.95, $order->subtotal);
        $this->assertEquals(0.0, $order->discount);
        $this->assertEquals('FS* fsprg.com', $order->billDescriptor);

        $this->assertIsArray($order->account);
        $this->assertEquals('{accountId}', $order->account['id']);

        $this->assertIsArray($order->payment);
        $this->assertEquals('test', $order->payment['type']);

        $this->assertIsArray($order->customer);
        $this->assertEquals('Leeroy', $order->customer['first']);

        $this->assertIsArray($order->address);
        $this->assertEquals('CA', $order->address['regionCode']);

        $this->assertIsArray($order->notes);
        $this->assertIsArray($order->items);
        $this->assertCount(1, $order->items);
    }
}
