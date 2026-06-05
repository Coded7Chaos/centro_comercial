<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use ReflectionMethod;
use Tests\TestCase;

class SystemDateOverrideTest extends TestCase
{
    protected function tearDown(): void
    {
        Date::setTestNow();
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_system_date_uses_real_system_date(): void
    {
        Carbon::setTestNow(Carbon::create(1999, 1, 1, 0, 0, 0, config('app.timezone')));
        Date::setTestNow(Carbon::create(1999, 1, 1, 0, 0, 0, config('app.timezone')));

        config(['app.fecha_sistema' => '']);

        $this->configureSystemDate();

        $this->assertNotSame('01/01/1999', now()->format('d/m/Y'));
    }

    public function test_null_string_system_date_uses_real_system_date(): void
    {
        Carbon::setTestNow(Carbon::create(1999, 1, 1, 0, 0, 0, config('app.timezone')));
        Date::setTestNow(Carbon::create(1999, 1, 1, 0, 0, 0, config('app.timezone')));

        config(['app.fecha_sistema' => 'NULL']);

        $this->configureSystemDate();

        $this->assertNotSame('01/01/1999', now()->format('d/m/Y'));
    }

    public function test_configured_system_date_overrides_laravel_and_carbon_now(): void
    {
        config(['app.fecha_sistema' => '15/07/2027']);

        $this->configureSystemDate();

        $this->assertSame('15/07/2027', now()->format('d/m/Y'));
        $this->assertSame('15/07/2027', Carbon::now()->format('d/m/Y'));
    }

    public function test_invalid_system_date_format_is_rejected(): void
    {
        config(['app.fecha_sistema' => '2027-07-15']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FECHA_SISTEMA debe estar vacía o tener formato DD/MM/YYYY.');

        $this->configureSystemDate();
    }

    private function configureSystemDate(): void
    {
        $provider = new AppServiceProvider($this->app);
        $method = new ReflectionMethod($provider, 'configureSystemDate');
        $method->setAccessible(true);
        $method->invoke($provider);
    }
}
