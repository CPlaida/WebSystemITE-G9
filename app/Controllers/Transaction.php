<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Transaction extends Controller
{
    protected $db;
    public function __construct() { $this->db = \Config\Database::connect(); }

    public function index()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login');
        }
        return view('Roles/admin/pharmacy/Transaction', ['title'=>'Pharmacy Transactions']);
    }

    public function view($id)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login');
        }
        $trx = $this->db->table('pharmacy_transactions')->where('id',$id)->get()->getRowArray();
        if (!$trx) return redirect()->back();
        // Get prescription items by matching transaction date 
        $items = $this->db->table('prescription_items pi')
            ->select('pi.*, m.name as medication_name')
            ->join('medicines m','m.id=pi.medication_id','left')
            ->join('prescriptions pr','pr.id = pi.prescription_id','left')
            ->where('pr.date', $trx['date'])
            ->orderBy('pr.id','DESC')
            ->get()->getResultArray();
        return view('Roles/admin/pharmacy/TransactionDetail', ['transaction'=>$trx,'items'=>$items,'title'=>'Transaction Details']);
    }

    public function print($id)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login');
        }
        $trx = $this->db->table('pharmacy_transactions')->where('id',$id)->get()->getRowArray();
        if (!$trx) return redirect()->back();
        // Get prescription items by matching transaction date
        $items = $this->db->table('prescription_items pi')
            ->select('pi.*, m.name as medication_name')
            ->join('medicines m','m.id=pi.medication_id','left')
            ->join('prescriptions pr','pr.id = pi.prescription_id','left')
            ->where('pr.date', $trx['date'])
            ->orderBy('pr.id','DESC')
            ->get()->getResultArray();
        return view('Roles/admin/pharmacy/TransactionPrint', ['transaction'=>$trx,'items'=>$items,'title'=>'Print']);
    }
}