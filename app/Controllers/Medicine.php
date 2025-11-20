<?php namespace App\Controllers;

use App\Models\MedicineModel;
use CodeIgniter\Controller;

class Medicine extends Controller
{
    public function index()
    {
        $model = new MedicineModel();
        $data['medicines'] = $model->orderBy('id', 'DESC')->findAll();

        // counts using fresh builders to avoid state leakage
        $data['total'] = (new MedicineModel())->countAll();
        $data['low_stock'] = (new MedicineModel())->where('stock <=', 5)->countAllResults();
        $data['out_stock'] = (new MedicineModel())->where('stock', 0)->countAllResults();

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

        $names = $this->request->getPost('name');
        $brands = $this->request->getPost('brand');
        $categories = $this->request->getPost('category');
        $stocks = $this->request->getPost('stock');
        $prices = $this->request->getPost('price');
        $expiries = $this->request->getPost('expiry_date');

        if (!is_array($names)) {
            $names = [$names];
            $brands = [$brands];
            $categories = [$categories];
            $stocks = [$stocks];
            $prices = [$prices];
            $expiries = [$expiries];
        }

        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime('+30 days'));
        foreach ($expiries as $i => $exp) {
            if (!empty($exp) && $exp < $today) {
                return redirect()->to('/medicines')->with('error', 'One or more medicines have an expiry date in the past. Please correct and try again.');
            }
            if (!empty($exp) && $exp <= $limit) {
                return redirect()->to('/medicines')->with('error', 'One or more medicines are expiring within 30 days and cannot be added.');
            }
        }

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
                'name' => $name,
                'brand' => $brands[$index] ?? null,
                'category' => $categories[$index] ?? null,
                'stock' => intval($stocks[$index] ?? 0),
                'price' => (float)($prices[$index] ?? 0),
                'expiry_date' => $expiries[$index] ?? null,
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

        $expiry = $this->request->getPost('expiry_date');
        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime('+30 days'));
        if (!empty($expiry) && $expiry < $today) {
            return redirect()->to('/medicines?edit=' . $id)->with('error', 'Expiry date cannot be in the past.');
        }
        if (!empty($expiry) && $expiry <= $limit) {
            return redirect()->to('/medicines?edit=' . $id)->with('error', 'Expiry date is within 30 days and cannot be saved.');
        }

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
            'name' => $this->request->getPost('name'),
            'brand' => $this->request->getPost('brand'),
            'category' => $this->request->getPost('category'),
            'stock' => intval($this->request->getPost('stock')),
            'price' => floatval($this->request->getPost('price')),
            'expiry_date' => $expiry,
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

    public function delete($id = null)
    {
        $model = new MedicineModel();
        $model->delete($id);
        return redirect()->to('/medicines')->with('success', 'Medicine deleted successfully!');
    }
}
