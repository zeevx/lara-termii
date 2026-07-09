<?php

declare(strict_types=1);

use Zeevx\LaraTermii\LaraTermii;
use Illuminate\Support\Facades\Http;
use Zeevx\LaraTermii\LaraTermiiEsim;
use Zeevx\LaraTermii\Exceptions\TermiiException;

function esimFake(array $authResponse = ['token' => 'esim-jwt']): void
{
    Http::fake([
        '*/api/esim/authenticate' => Http::response($authResponse, 200),
        '*' => Http::response(['status' => 'ok'], 200),
    ]);
}

it('exposes the esim sub-client', function () {
    esimFake();
    $termii = $this->app->make(LaraTermii::class);

    expect($termii->esim())->toBeInstanceOf(LaraTermiiEsim::class)
        ->and($termii->esim())->toBe($termii->esim());
});

it('authenticates lazily and sends the bearer token header', function () {
    esimFake();
    $this->app->make(LaraTermii::class)->esim()->dataPlans('NG', 'LOCAL');

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && $r->url() === termiiUrl('esim/authenticate')
        && $r['api_key'] === 'test-api-key');

    Http::assertSent(fn ($r) => $r->method() === 'GET'
        && strpos($r->url(), termiiUrl('esim/data/plan/fetch')) === 0
        && $r['country'] === 'NG'
        && $r['type'] === 'LOCAL'
        && $r->hasHeader('X-Token', 'esim-jwt'));
});

it('reuses the token across esim calls', function () {
    esimFake();
    $esim = $this->app->make(LaraTermii::class)->esim();

    $esim->esims();
    $esim->countries(page: 1, size: 5);

    Http::assertSentCount(3);
    Http::assertSent(fn ($r) => strpos($r->url(), termiiUrl('esim/fetch/esims')) === 0
        && $r['page'] == 0 && $r['size'] == 15);
    Http::assertSent(fn ($r) => strpos($r->url(), termiiUrl('esim/countries/all')) === 0
        && $r['page'] == 1 && $r['size'] == 5);
});

it('creates and tops up an esim', function () {
    esimFake();
    $esim = $this->app->make(LaraTermii::class)->esim();

    $esim->createEsim('prod-1', 'NGA');
    $esim->purchasePlan('8944538532008389253', 'prod-1', 'NGA');

    Http::assertSent(fn ($r) => $r->url() === termiiUrl('esim/create')
        && $r['productId'] === 'prod-1' && $r['iso3'] === 'NGA'
        && $r->hasHeader('X-Token', 'esim-jwt'));
    Http::assertSent(fn ($r) => $r->url() === termiiUrl('esim/data-plan/purchase')
        && $r['iccid'] === '8944538532008389253' && $r['productId'] === 'prod-1');
});

it('fetches per-iccid esim details', function () {
    esimFake();
    $esim = $this->app->make(LaraTermii::class)->esim();

    $esim->qrCode('894453');
    $esim->profile('894453');
    $esim->usage('894453');
    $esim->planStatus('894453');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/api/esim/activate/894453/qr/code'));
    Http::assertSent(fn ($r) => str_contains($r->url(), '/api/esim/euicc/894453'));
    Http::assertSent(fn ($r) => str_contains($r->url(), '/api/esim/subscriptions/usage/894453'));
    Http::assertSent(fn ($r) => str_contains($r->url(), '/api/esim/iccid/894453/details'));
});

it('throws when authentication returns no token', function () {
    esimFake(['status' => 'error']);
    $esim = $this->app->make(LaraTermii::class)->esim();

    expect(fn () => $esim->esims())->toThrow(TermiiException::class);
});
