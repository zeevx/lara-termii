<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Sotel;

use Illuminate\Http\Client\Response;

trait ManagesEsims
{
    /**
     * Create a new eSIM for a product and ISO3 country code.
     */
    public function createEsim(string $productId, string $iso3): Response
    {
        return $this->post('esim/create', [
            'productId' => $productId,
            'iso3' => $iso3,
        ]);
    }

    /**
     * Retrieve a paginated list of all eSIMs on your account.
     */
    public function esims(int $page = 0, int $size = 15): Response
    {
        return $this->get('esim/fetch/esims', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Retrieve the activation QR code for an eSIM.
     */
    public function qrCode(string $iccid): Response
    {
        return $this->get("esim/activate/{$iccid}/qr/code");
    }

    /**
     * Retrieve the eUICC profile of an eSIM.
     */
    public function profile(string $iccid): Response
    {
        return $this->get("esim/euicc/{$iccid}");
    }

    /**
     * Retrieve the data usage of an eSIM.
     */
    public function usage(string $iccid): Response
    {
        return $this->get("esim/subscriptions/usage/{$iccid}");
    }
}
