<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use Config\Services;

class Locations extends BaseController
{
    protected $http;

    // PSGC base URL
    protected $base = 'https://psgc.gitlab.io/api';

    // Optional overrides to satisfy UX expectations (e.g., General Santos under South Cotabato)
    // Map provinceCode => array of extra city/mun codes to include
    protected $provinceCityOverrides = [
        // South Cotabato provinceCode (1263) adds General Santos City (126803)
        // Note: PSGC codes may change; adjust as needed.
        '1263' => ['126803'],
    ];

    public function __construct()
    {
        $this->http = Services::curlrequest(['timeout' => 10]);
    }

    public function provinces()
    {
        try {
            $resp = $this->http->get($this->base . '/provinces/');
            $data = json_decode($resp->getBody(), true) ?: [];
            $out = array_map(function ($row) {
                return [
                    'code' => $row['code'] ?? '',
                    'name' => $row['name'] ?? '',
                ];
            }, $data);
            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(200)->setJSON([]);
        }
    }

    public function cities($provinceCode = null)
    {
        if (!$provinceCode) {
            return $this->response->setJSON([]);
        }

        try {
            // Get component cities and municipalities under the province
            $resp = $this->http->get($this->base . '/provinces/' . rawurlencode($provinceCode) . '/cities-municipalities/');
            $data = json_decode($resp->getBody(), true) ?: [];

            // Optionally augment with HUC/IC that are commonly expected under the province
            $extra = [];
            if (isset($this->provinceCityOverrides[$provinceCode])) {
                foreach ($this->provinceCityOverrides[$provinceCode] as $cityCode) {
                    try {
                        $cResp = $this->http->get($this->base . '/cities/' . rawurlencode($cityCode) . '/');
                        $city = json_decode($cResp->getBody(), true) ?: null;
                        if ($city) {
                            $extra[] = $city;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }

            $merged = array_merge($data, $extra);

            $out = array_map(function ($row) {
                return [
                    'code' => $row['code'] ?? '',
                    'name' => $row['name'] ?? '',
                ];
            }, $merged);

            // Sort by name
            usort($out, function ($a, $b) { return strcmp($a['name'], $b['name']); });

            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(200)->setJSON([]);
        }
    }

    public function barangays($cityOrMunCode = null)
    {
        if (!$cityOrMunCode) {
            return $this->response->setJSON([]);
        }

        try {
            // PSGC uses cities-municipalities for barangay listing
            $resp = $this->http->get($this->base . '/cities-municipalities/' . rawurlencode($cityOrMunCode) . '/barangays/');
            $data = json_decode($resp->getBody(), true) ?: [];
            $out = array_map(function ($row) {
                return [
                    'code' => $row['code'] ?? '',
                    'name' => $row['name'] ?? '',
                ];
            }, $data);
            // Sort by name
            usort($out, function ($a, $b) { return strcmp($a['name'], $b['name']); });

            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(200)->setJSON([]);
        }
    }
}
