<?php
namespace App\Services\Billing\Contracts;

/**
 * Represents a module-specific provider that can emit billable line items
 * for a given patient.
 */
interface ChargeProviderInterface
{
    /**
     * Collect billable charges for the given patient.
     *
     * Each item should include at least: service, qty, price, amount, category,
     * source_table, source_id, and optional flags such as locked or lab_id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCharges(string $patientId): array;
}
