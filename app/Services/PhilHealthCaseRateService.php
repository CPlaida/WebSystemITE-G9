<?php

namespace App\Services;

use App\Models\Financial\PhilHealthCaseRateModel;

class PhilHealthCaseRateService
{
    protected PhilHealthCaseRateModel $rates;

    public function __construct()
    {
        $this->rates = new PhilHealthCaseRateModel();
    }

    /**
     * Suggest PhilHealth amount given codes and admission date.
     * Rules: RVS first then ICD; Case B preferred else A; within effectivity dates; pick highest rate_total if multiple.
     * Returns: [suggested_amount, rate_ids, codes_used]
     */
    public function suggest(?string $primaryRvs, ?string $primaryIcd, ?string $admissionDate): array
    {
        $admissionDate = $admissionDate ?: date('Y-m-d');
        $candidates = [];
        $picked = null;
        $rateIds = [];
        $codesUsed = [
            'primary_rvs_code' => $primaryRvs,
            'primary_icd10_code' => $primaryIcd,
            'admission_date' => $admissionDate,
            'selected' => null,
        ];

        // Helper to query by code and case preference
        $queryBy = function(string $codeType, string $code) use ($admissionDate) {
            return $this->rates->builder()
                ->where('code_type', $codeType)
                ->where('code', $code)
                ->where('active', 1)
                ->where('effective_from <=', $admissionDate)
                ->groupStart()
                    ->where('effective_to IS NULL')
                    ->orWhere('effective_to >=', $admissionDate)
                ->groupEnd()
                ->orderBy("FIELD(case_type, 'B','A')", 'ASC', false) // prefer B then A
                ->orderBy('rate_total', 'DESC')
                ->get()->getResultArray();
        };

        // Try RVS first
        if ($primaryRvs) {
            $candidates = $queryBy('RVS', $primaryRvs);
        }
        // Fallback to ICD
        if (empty($candidates) && $primaryIcd) {
            $candidates = $queryBy('ICD', $primaryIcd);
        }

        if (!empty($candidates)) {
            // Already ordered by case_type pref and rate_total desc
            $picked = $candidates[0];
            $rateIds = array_map(fn($r) => (int)($r['id'] ?? 0), $candidates);
            $codesUsed['selected'] = [
                'code_type' => $picked['code_type'] ?? null,
                'code' => $picked['code'] ?? null,
                'case_type' => $picked['case_type'] ?? null,
            ];
        }

        $suggested = $picked ? (float)($picked['rate_total'] ?? 0) : 0.0;

        return [
            'suggested_amount' => $suggested,
            'rate_ids' => $rateIds,
            'codes_used' => $codesUsed,
        ];
    }
}
