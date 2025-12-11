<?php
namespace App\Services\Billing\Providers;

use App\Services\Billing\Contracts\ChargeProviderInterface;
use Config\Database;
use CodeIgniter\Database\ConnectionInterface;

abstract class AbstractChargeProvider implements ChargeProviderInterface
{
    protected ConnectionInterface $db;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    protected function tableExists(string $table): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            try {
                $cache[$table] = $this->db->tableExists($table);
            } catch (\Throwable $e) {
                $cache[$table] = false;
            }
        }
        return $cache[$table];
    }

    protected function fieldExists(string $table, string $field): bool
    {
        static $cache = [];
        $key = $table . ':' . $field;
        if (!array_key_exists($key, $cache)) {
            try {
                $fields = array_map('strtolower', $this->db->getFieldNames($table));
                $cache[$key] = in_array(strtolower($field), $fields, true);
            } catch (\Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    protected function defaultItem(): array
    {
        return [
            'service' => '',
            'qty' => 1,
            'price' => 0.0,
            'amount' => 0.0,
            'lab_id' => null,
            'category' => 'general',
            'source_table' => null,
            'source_id' => null,
            'locked' => true,
        ];
    }

    /**
     * Remove rows that are already linked to billing_items via source_table/source_id.
     *
     * @param array<int, array<string,mixed>> $rows
     * @param string $sourceTable
     * @param string $idKey
     * @return array<int, array<string,mixed>>
     */
    protected function filterOutAlreadyLinked(array $rows, string $sourceTable, string $idKey = 'id'): array
    {
        if (empty($rows) || !$this->tableExists('billing_items')) {
            return $rows;
        }
        $ids = array_values(array_filter(array_map(function ($row) use ($idKey) {
            return $row[$idKey] ?? null;
        }, $rows)));
        if (empty($ids)) {
            return $rows;
        }
        try {
            $existing = $this->db->table('billing_items')
                ->select('source_id')
                ->where('source_table', $sourceTable)
                ->whereIn('source_id', $ids)
                ->get()->getResultArray();
            if (empty($existing)) {
                return $rows;
            }
            $existingMap = [];
            foreach ($existing as $ex) {
                if (!empty($ex['source_id'])) {
                    $existingMap[(string)$ex['source_id']] = true;
                }
            }
            if (empty($existingMap)) {
                return $rows;
            }
            return array_values(array_filter($rows, function ($row) use ($existingMap, $idKey) {
                $id = $row[$idKey] ?? null;
                return !$id || !isset($existingMap[(string)$id]);
            }));
        } catch (\Throwable $e) {
            return $rows;
        }
    }
}
