<?php

namespace Photalika\CashierForFastspring\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Photalika\CashierForFastspring\Billable;

class User extends Eloquent
{
    use Billable;
}
