<?php namespace App\Controllers;

use App\Models\MedicineModel;
use CodeIgniter\Controller;

class Medicine extends Controller
{
    public function index()
    {
        $model = new MedicineModel();
        $cutoff = date('Y-m-d', strtotime('+3 months')); // strict 3-month rule
        
        // Show medicines whose expiry is beyond the 3-month cutoff (or no expiry)
        // AND stock is greater than 0 (exclude out of stock items)
        $data['medicines'] = $model->groupStart()
                                   ->where('expiry_date >', $cutoff)
                                   ->orWhere('expiry_date IS NULL', null, false)
                                   ->groupEnd()
                                   ->where('stock >', 0)
                                   ->orderBy('id', 'DESC')
                                   ->findAll();

        // counts using fresh builders to avoid state leakage (excluding anything at or within 3 months AND out of stock)
        $totalModel = new MedicineModel();
        $data['total'] = $totalModel->groupStart()
                                    ->where('expiry_date >', $cutoff)
                                    ->orWhere('expiry_date IS NULL', null, false)
                                    ->groupEnd()
                                    ->where('stock >', 0)
                                    ->countAllResults();
        
        $lowStockModel = new MedicineModel();
        $data['low_stock'] = $lowStockModel->where('stock >', 0)
                                           ->where('stock <=', 5)
                                           ->groupStart()
                                           ->where('expiry_date >', $cutoff)
                                           ->orWhere('expiry_date IS NULL', null, false)
                                           ->groupEnd()
                                           ->countAllResults();
        
        $outStockModel = new MedicineModel();
        $data['out_stock'] = $outStockModel->where('stock', 0)
                                           ->groupStart()
                                           ->where('expiry_date >', $cutoff)
                                           ->orWhere('expiry_date IS NULL', null, false)
                                           ->groupEnd()
                                           ->countAllResults();

        // if coming from edit link, load the record to prefill modal
        $editId = $this->request->getGet('edit');
        if ($editId) {
            $data['edit_medicine'] = $model->find($editId);
        }
        // request to open add modal directly (ensures fresh add mode)
        if ($this->request->getGet('add')) {
            $data['open_add_modal'] = true;
        }

        echo view('Roles/admin/inventory/Medicine', $data);
    }

    // store single or multiple medicines. Accepts arrays from form.
    public function store()
    {
        $model = new MedicineModel();

        $barcodes = $this->request->getPost('barcode');
        $names = $this->request->getPost('name');
        $brands = $this->request->getPost('brand');
        $categories = $this->request->getPost('category');
        $stocks = $this->request->getPost('stock');
        $unitPrices = $this->request->getPost('unit_price');
        $retailPrices = $this->request->getPost('retail_price');
        $manufacturedDates = $this->request->getPost('manufactured_date');
        $expiries = $this->request->getPost('expiry_date');
        $descriptions = $this->request->getPost('description');

        if (!is_array($names)) {
            $barcodes = [$barcodes];
            $names = [$names];
            $brands = [$brands];
            $categories = [$categories];
            $stocks = [$stocks];
            $unitPrices = [$unitPrices];
            $retailPrices = [$retailPrices];
            $manufacturedDates = [$manufacturedDates];
            $expiries = [$expiries];
            $descriptions = [$descriptions];
        }

        // No blocking — near-expiry items will be auto-routed to Stock Out by the 3-month rule

        // Ensure uploads directory exists
        $uploadPath = FCPATH . 'uploads/medicines/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Check if image column exists in database
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('medicines');
        $hasImageColumn = in_array('image', $fields);

        foreach ($names as $index => $name) {
            if (trim((string)$name) === '') continue;

            $imageName = null;
            // Handle image upload - support both single file and array
            if ($hasImageColumn) {
                $imageFile = $this->request->getFile('image');
                if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                    // Single file upload
                    $newName = $imageFile->getRandomName();
                    $imageFile->move($uploadPath, $newName);
                    $imageName = $newName;
                } elseif (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
                    // Multiple file uploads (array)
                    if (isset($_FILES['image']['name'][$index]) && $_FILES['image']['error'][$index] === UPLOAD_ERR_OK) {
                        $file = $this->request->getFile("image.{$index}");
                        if ($file && $file->isValid() && !$file->hasMoved()) {
                            $newName = $file->getRandomName();
                            $file->move($uploadPath, $newName);
                            $imageName = $newName;
                        }
                    }
                }
            }

            $data = [
                'barcode' => !empty($barcodes[$index]) ? trim($barcodes[$index]) : null,
                'name' => $name,
                'brand' => $brands[$index] ?? null,
                'category' => $categories[$index] ?? null,
                'stock' => intval($stocks[$index] ?? 0),
                'unit_price' => !empty($unitPrices[$index]) ? (float)$unitPrices[$index] : null,
                'retail_price' => !empty($retailPrices[$index]) ? (float)$retailPrices[$index] : null,
                'manufactured_date' => !empty($manufacturedDates[$index]) ? $manufacturedDates[$index] : null,
                'expiry_date' => $expiries[$index] ?? null,
                'description' => !empty($descriptions[$index]) ? trim($descriptions[$index]) : null,
            ];
            
            // Only include image if column exists
            if ($hasImageColumn) {
                $data['image'] = $imageName;
            }
            
            $model->insert($data);
        }

        return redirect()->to('/medicines')->with('success', 'Medicine(s) added successfully!');
    }

    public function edit($id = null)
    {
        // Redirect to index and open the modal pre-filled via query param
        return redirect()->to('/medicines?edit=' . $id);
    }

    public function update($id = null)
    {
        $model = new MedicineModel();

        // Skip blocking; near-expiry items will automatically be treated as Stock Out by the 3-month rule

        // Check if image column exists in database
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('medicines');
        $hasImageColumn = in_array('image', $fields);

        // Ensure uploads directory exists
        $uploadPath = FCPATH . 'uploads/medicines/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $data = [
            'barcode' => $this->request->getPost('barcode') ? trim($this->request->getPost('barcode')) : null,
            'name' => $this->request->getPost('name'),
            'brand' => $this->request->getPost('brand'),
            'category' => $this->request->getPost('category'),
            'stock' => intval($this->request->getPost('stock')),
            'unit_price' => $this->request->getPost('unit_price') ? floatval($this->request->getPost('unit_price')) : null,
            'retail_price' => $this->request->getPost('retail_price') ? floatval($this->request->getPost('retail_price')) : null,
            'manufactured_date' => $this->request->getPost('manufactured_date') ? $this->request->getPost('manufactured_date') : null,
            'expiry_date' => $this->request->getPost('expiry_date') ? $this->request->getPost('expiry_date') : null,
            'description' => $this->request->getPost('description') ? trim($this->request->getPost('description')) : null,
        ];

        // Handle image upload only if column exists
        if ($hasImageColumn) {
            $removeImage = $this->request->getPost('remove_image') === '1';
            $imageFile = $this->request->getFile('image');
            
            if ($removeImage) {
                // Remove existing image
                $existing = $model->find($id);
                if ($existing && !empty($existing['image'])) {
                    $oldImagePath = $uploadPath . $existing['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $data['image'] = null;
            } elseif ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                // Delete old image if exists
                $existing = $model->find($id);
                if ($existing && !empty($existing['image'])) {
                    $oldImagePath = $uploadPath . $existing['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                // Upload new image
                $newName = $imageFile->getRandomName();
                $imageFile->move($uploadPath, $newName);
                $data['image'] = $newName;
            }
        }

        $model->update($id, $data);
        return redirect()->to('/medicines')->with('success', 'Medicine updated successfully!');
    }

    /**
     * Stock Out - Display expired medicines
     */
    public function stockOut()
    {
        $model = new MedicineModel();
        $cutoff = date('Y-m-d', strtotime('+3 months'));

        // Get medicines whose expiry is at or within 3 months (or already past)
        $data['expired_medicines'] = $model->where('expiry_date <=', $cutoff)
                                          ->where('expiry_date IS NOT NULL', null, false)
                                          ->orderBy('expiry_date', 'ASC')
                                          ->findAll();

        // Summary statistics
        $data['total_expired'] = count($data['expired_medicines']);
        $data['total_expired_stock'] = 0;
        $data['total_expired_value'] = 0;

        foreach ($data['expired_medicines'] as $med) {
            $stock = (int)($med['stock'] ?? 0);
            $price = (float)($med['unit_price'] ?? $med['retail_price'] ?? $med['price'] ?? 0);
            $data['total_expired_stock'] += $stock;
            $data['total_expired_value'] += $stock * $price;
        }
        
        echo view('Roles/admin/inventory/StockOut', $data);
    }

    /**
     * Out of Stock - Display medicines with stock = 0
     */
    public function outOfStock()
    {
        $model = new MedicineModel();
        $cutoff = date('Y-m-d', strtotime('+3 months'));

        // Get medicines with stock = 0 that are not expired (expiry > 3 months)
        $data['out_of_stock_medicines'] = $model->where('stock', 0)
                                                ->groupStart()
                                                ->where('expiry_date >', $cutoff)
                                                ->orWhere('expiry_date IS NULL', null, false)
                                                ->groupEnd()
                                                ->orderBy('name', 'ASC')
                                                ->findAll();

        // Summary statistics
        $data['total_out_of_stock'] = count($data['out_of_stock_medicines']);
        $data['total_out_of_stock_units'] = 0;
        $data['total_out_of_stock_value'] = 0;

        foreach ($data['out_of_stock_medicines'] as $med) {
            $stock = (int)($med['stock'] ?? 0);
            $price = (float)($med['unit_price'] ?? $med['retail_price'] ?? $med['price'] ?? 0);
            $data['total_out_of_stock_units'] += $stock;
            $data['total_out_of_stock_value'] += $stock * $price;
        }
        
        echo view('Roles/admin/inventory/OutOfStock', $data);
    }

    /**
     * Restock - Update medicine stock and move back to main inventory
     */
    public function restock()
    {
        $model = new MedicineModel();
        $id = $this->request->getPost('id');
        $newStock = intval($this->request->getPost('stock'));

        if (!$id || $newStock < 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $medicine = $model->find($id);
        if (!$medicine) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Medicine not found'
            ])->setStatusCode(404);
        }

        // Update stock
        $model->update($id, ['stock' => $newStock]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Medicine restocked successfully',
            'medicine' => $model->find($id)
        ]);
    }

}
