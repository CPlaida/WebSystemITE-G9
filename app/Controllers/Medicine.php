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
            $data['edit_medicine'] = $model->find((int)$editId);
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
        foreach ($expiries as $i => $exp) {
            if (!empty($exp) && $exp < $today) {
                return redirect()->to('/medicines')->with('error', 'One or more medicines have an expiry date in the past. Please correct and try again.');
            }
        }

        $baseCount = $model->countAll();

        foreach ($names as $index => $name) {
            if (trim((string)$name) === '') continue;

            $seq = $baseCount + $index + 1;
            $medicine_id = 'MEDI' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

            $data = [
                'medicine_id' => $medicine_id,
                'name' => $name,
                'brand' => $brands[$index] ?? null,
                'category' => $categories[$index] ?? null,
                'stock' => intval($stocks[$index] ?? 0),
                'price' => (float)($prices[$index] ?? 0),
                'expiry_date' => $expiries[$index] ?? null,
            ];
            $model->insert($data);
        }

        return redirect()->to('/medicines')->with('success', 'Medicine(s) added successfully!');
    }

    public function edit($id = null)
    {
        // Redirect to index and open the modal pre-filled via query param
        return redirect()->to('/medicines?edit=' . (int)$id);
    }

    public function update($id = null)
    {
        $model = new MedicineModel();

        $expiry = $this->request->getPost('expiry_date');
        $today = date('Y-m-d');
        if (!empty($expiry) && $expiry < $today) {
            return redirect()->to('/medicines?edit=' . (int)$id)->with('error', 'Expiry date cannot be in the past.');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'brand' => $this->request->getPost('brand'),
            'category' => $this->request->getPost('category'),
            'stock' => intval($this->request->getPost('stock')),
            'price' => floatval($this->request->getPost('price')),
            'expiry_date' => $expiry,
        ];

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
