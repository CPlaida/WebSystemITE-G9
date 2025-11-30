<?php

namespace App\Models;

use CodeIgniter\Model;

class PrescriptionModel extends Model
{
    protected $table = 'prescriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'date',
        'payment_method',
        'subtotal',
        'tax',
        'total_amount',
        'note',
        'created_at',
        'updated_at'
        // Note: patient_id removed - not needed for prescriptions
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'date' => 'required|valid_date',
        'payment_method' => 'required|in_list[cash,insurance]',
        'subtotal' => 'required|decimal',
        'tax' => 'required|decimal',
        'total_amount' => 'required|decimal',
        'note' => 'permit_empty|string'
    ];

    /**
     * Get prescription statistics
     */
    public function getPrescriptionStatistics(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['start_date'])) {
            $builder->where('date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('date <=', $filters['end_date']);
        }

        $prescriptions = $builder->get()->getResultArray();

        $db = \Config\Database::connect();
        
        // Get prescription items with medicine details for each prescription
        $prescriptionIds = array_column($prescriptions, 'id');
        $prescriptionsWithItems = [];
        
        // If we have prescription IDs, get all items at once for better performance
        $allItemsByPrescription = [];
        if (!empty($prescriptionIds)) {
            try {
                // Get all prescription items for these prescriptions
                $itemsBuilder = $db->table('prescription_items pi');
                $itemsBuilder->select('pi.prescription_id, pi.medication_id, pi.quantity, pi.price, pi.total, m.name as medicine_name, m.category');
                $itemsBuilder->join('medicines m', 'pi.medication_id = m.id', 'left');
                $itemsBuilder->whereIn('pi.prescription_id', $prescriptionIds);
                $itemsBuilder->orderBy('pi.prescription_id', 'ASC');
                
                $allItems = $itemsBuilder->get()->getResultArray();
                
                // Group items by prescription_id
                foreach ($allItems as $item) {
                    $presId = $item['prescription_id'];
                    if (!isset($allItemsByPrescription[$presId])) {
                        $allItemsByPrescription[$presId] = [];
                    }
                    $allItemsByPrescription[$presId][] = $item;
                }
                
                // Also check for items that might not have matching medicines
                // This handles cases where medication_id exists but medicine record doesn't
                $rawItemsBuilder = $db->table('prescription_items');
                $rawItemsBuilder->whereIn('prescription_id', $prescriptionIds);
                $rawItems = $rawItemsBuilder->get()->getResultArray();
                
                // Add any items that weren't included in the join (medicine doesn't exist)
                foreach ($rawItems as $rawItem) {
                    $presId = $rawItem['prescription_id'];
                    $medId = $rawItem['medication_id'];
                    
                    // Check if this item is already in our grouped array
                    $found = false;
                    if (isset($allItemsByPrescription[$presId])) {
                        foreach ($allItemsByPrescription[$presId] as $existingItem) {
                            if (($existingItem['medication_id'] ?? '') == $medId) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    
                    // If not found, add it (medicine doesn't exist in medicines table)
                    if (!$found) {
                        if (!isset($allItemsByPrescription[$presId])) {
                            $allItemsByPrescription[$presId] = [];
                        }
                        $allItemsByPrescription[$presId][] = [
                            'prescription_id' => $rawItem['prescription_id'],
                            'medication_id' => $rawItem['medication_id'],
                            'quantity' => $rawItem['quantity'],
                            'price' => $rawItem['price'],
                            'total' => $rawItem['total'],
                            'medicine_name' => null,
                            'category' => null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // If there's an error, log it but continue
                log_message('error', 'Error fetching prescription items: ' . $e->getMessage());
            }
        }
        
        foreach ($prescriptions as $prescription) {
            $prescriptionId = $prescription['id'];
            
            // Get items for this prescription from the grouped array
            $items = $allItemsByPrescription[$prescriptionId] ?? [];
            
            // Ensure items is always an array
            if (!is_array($items)) {
                $items = [];
            }
            
            $prescription['items'] = $items;
            $prescription['medicines'] = array_map(function($item) {
                $name = $item['medicine_name'] ?? null;
                $id = $item['medication_id'] ?? '';
                return !empty($name) ? $name : (!empty($id) ? 'ID: ' . $id : 'Unknown');
            }, $items);
            
            $prescriptionsWithItems[] = $prescription;
        }

        // Get statistics for most prescribed medicines
        $itemsBuilder = $db->table('prescription_items pi');
        $itemsBuilder->select('pi.*, m.name as medicine_name');
        $itemsBuilder->join('medicines m', 'pi.medication_id = m.id', 'left');
        
        if (!empty($prescriptionIds)) {
            $itemsBuilder->whereIn('pi.prescription_id', $prescriptionIds);
        }

        if (!empty($filters['doctor_id'])) {
            // Assuming prescriptions have doctor_id or we join through another table
        }

        if (!empty($filters['medicine_id'])) {
            $itemsBuilder->where('pi.medication_id', $filters['medicine_id']);
        }

        $allItems = $itemsBuilder->get()->getResultArray();

        $byMedicine = [];
        foreach ($allItems as $item) {
            $medicineId = $item['medication_id'] ?? '';
            if ($medicineId) {
                $byMedicine[$medicineId] = [
                    'name' => $item['medicine_name'] ?? '',
                    'count' => ($byMedicine[$medicineId]['count'] ?? 0) + 1,
                ];
            }
        }

        arsort($byMedicine);

        return [
            'total_prescriptions' => count($prescriptions),
            'most_prescribed' => array_slice($byMedicine, 0, 10, true),
            'prescriptions' => $prescriptionsWithItems,
        ];
    }
}