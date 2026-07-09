<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Concerns\Sotel;

use Illuminate\Http\Client\Response;

trait ManagesPlans
{
    /**
     * Retrieve available data plans, optionally filtered by country and type
     * ("LOCAL" or "REGION").
     */
    public function dataPlans(?string $country = null, ?string $type = null): Response
    {
        return $this->get('esim/data/plan/fetch', array_filter([
            'country' => $country,
            'type' => $type,
        ], fn ($value) => $value !== null));
    }

    /**
     * Purchase or top up a data plan for an existing eSIM.
     */
    public function purchasePlan(string $iccid, string $productId, string $iso3): Response
    {
        return $this->post('esim/data-plan/purchase', [
            'iccid' => $iccid,
            'productId' => $productId,
            'iso3' => $iso3,
        ]);
    }

    /**
     * Retrieve the data plan status and validity dates of an eSIM.
     */
    public function planStatus(string $iccid): Response
    {
        return $this->get("esim/iccid/{$iccid}/details");
    }

    /**
     * Retrieve a paginated list of supported countries.
     */
    public function countries(int $page = 0, int $size = 15): Response
    {
        return $this->get('esim/countries/all', [
            'page' => $page,
            'size' => $size,
        ]);
    }
}
