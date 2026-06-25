<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var \App\Models\User */
    protected $admin;

    /** @var \App\Models\User */
    protected $cashier;

    /** @var \App\Models\Shop */
    protected $shop;

    /** @var \App\Models\Shop */
    protected $shop1;

    /** @var \App\Models\Shop */
    protected $shop2;

    /** @var string */
    protected $adminToken;

    /** @var string */
    protected $cashierToken;
}
