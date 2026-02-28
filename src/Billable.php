<?php

declare(strict_types=1);

namespace Photalika\CashierForFastspring;

use Photalika\CashierForFastspring\Concerns\ManagesAccount;
use Photalika\CashierForFastspring\Concerns\ManagesInvoices;
use Photalika\CashierForFastspring\Concerns\ManagesSubscriptions;

/**
 * Billable trait.
 *
 * {@inheritdoc}
 */
trait Billable
{
    use ManagesAccount;
    use ManagesInvoices;
    use ManagesSubscriptions;
}
