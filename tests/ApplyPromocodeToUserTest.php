<?php

namespace Gabievi\Promocodes\Tests;

use Gabievi\Promocodes\Models\Promocode;
use Gabievi\Promocodes\Tests\Models\User;
use Gabievi\Promocodes\Facades\Promocodes;
use Gabievi\Promocodes\Exceptions\AlreadyUsedException;
use Gabievi\Promocodes\Exceptions\UnauthenticatedException;

class ApplyPromocodeToUserTest extends TestCase
{
    /** @test */
    public function it_throws_exception_if_user_is_not_authenticated()
    {
        $this->expectException(UnauthenticatedException::class);

        $promocodes = Promocodes::create();
        $promocode = $promocodes->first();

        $this->assertCount(1, $promocodes);

        Promocodes::apply($promocode['code']);
    }

    /** @test */
    public function it_returns_false_if_promocode_doesnt_exist()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $appliedPromocode = Promocodes::apply('INVALID-CODE');

        $this->assertFalse($appliedPromocode);
    }

    /** @test */
    public function it_returns_false_if_user_tries_to_apply_code_twice()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $promocodes = Promocodes::setDisposable()->create();
        $promocode = $promocodes->first();

        $this->assertCount(1, $promocodes);

        $this->assertInstanceOf(Promocode::class, Promocodes::apply($promocode['code']));
        $this->assertFalse(Promocodes::apply($promocode['code']));
    }

    /** @test */
    public function it_attaches_authenticated_user_as_applied_to_promocode()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $promocodes = Promocodes::create();
        $promocode = $promocodes->first();

        $this->assertCount(1, $promocodes);

        Promocodes::apply($promocode['code']);

        $this->assertCount(1, $user->promocodes);

        $userPromocode = $user->promocodes()->first();

        $this->assertNotNull($userPromocode->pivot->used_at);
    }

    /** @test */
    public function is_returns_promocode_with_user_if_applied_successfuly()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $promocodes = Promocodes::create();
        $promocode = $promocodes->first();

        $this->assertCount(1, $promocodes);

        $appliedPromocode = Promocodes::apply($promocode['code']);

        $this->assertTrue($appliedPromocode instanceof Promocode);

        $this->assertCount(1, $appliedPromocode->users);
    }

    /** @test */
    public function it_has_alias_named_reedem()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $promocodes = Promocodes::create();
        $promocode = $promocodes->first();

        $this->assertCount(1, $promocodes);

        Promocodes::redeem($promocode['code']);

        $this->assertCount(1, $user->promocodes);
    }
}