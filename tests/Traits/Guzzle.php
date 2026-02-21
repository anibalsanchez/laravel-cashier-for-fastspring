<?php

namespace Photalika\CashierForFastspring\Tests\Traits;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Photalika\CashierForFastspring\Fastspring\Fastspring;

trait Guzzle
{
    /**
     * Set mock responses for guzzle.
     */
    protected function setMockResponsesAndHistory($responses, $history = null)
    {
        // prepare class for testing
        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);

        if ($history) {
            // Add the history middleware to the handler stack.
            $handlerStack->push($history);
        }

        Fastspring::setClientOptions([
            'handler' => $handlerStack,
        ]);
    }
}
