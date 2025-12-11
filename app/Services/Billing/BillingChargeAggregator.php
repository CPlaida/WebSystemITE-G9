<?php
namespace App\Services\Billing;

use App\Services\Billing\Contracts\ChargeProviderInterface;
use App\Services\Billing\Providers\LaboratoryChargeProvider;
use App\Services\Billing\Providers\PharmacyChargeProvider;
use App\Services\Billing\Providers\AppointmentChargeProvider;
use App\Services\Billing\Providers\RoomChargeProvider;

class BillingChargeAggregator
{
    /** @var ChargeProviderInterface[] */
    protected array $providers = [];

    /**
     * @param ChargeProviderInterface[]|null $providers
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?? $this->defaultProviders();
    }

    public function addProvider(ChargeProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Collect all billable charges for the patient.
     *
     * @return array{items: array<int, array<string,mixed>>, breakdown: array<string,int>, errors: array<string,string>}
     */
    public function collect(string $patientId): array
    {
        $items = [];
        $breakdown = [];
        $errors = [];

        foreach ($this->providers as $provider) {
            try {
                $providerItems = $provider->getCharges($patientId);
                if (empty($providerItems)) {
                    continue;
                }
                foreach ($providerItems as $item) {
                    $items[] = $item;
                    $category = (string)($item['category'] ?? get_class($provider));
                    $breakdown[$category] = ($breakdown[$category] ?? 0) + 1;
                }
            } catch (\Throwable $e) {
                $errors[get_class($provider)] = $e->getMessage();
            }
        }

        return [
            'items' => $items,
            'breakdown' => $breakdown,
            'errors' => $errors,
        ];
    }

    /**
     * @return ChargeProviderInterface[]
     */
    protected function defaultProviders(): array
    {
        return [
            new LaboratoryChargeProvider(),
            new PharmacyChargeProvider(),
            new AppointmentChargeProvider(),
            new RoomChargeProvider(),
        ];
    }
}
