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
        $items = $this->db->table('prescription_items pi')
            ->select('pi.*, m.name as medication_name')
            ->join('medicines m','m.id=pi.medication_id','left')
            ->where('pi.prescription_id', function ($qb) use ($trx) {
                $qb->select('id')->from('prescriptions')->where('patient_id',$trx['patient_id'])->orderBy('id','DESC')->limit(1);
            })->get()->getResultArray();
        return view('Roles/admin/pharmacy/TransactionDetail', ['transaction'=>$trx,'items'=>$items,'title'=>'Transaction Details']);
    }

    public function print($id)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login');
        }
        $trx = $this->db->table('pharmacy_transactions')->where('id',$id)->get()->getRowArray();
        if (!$trx) return redirect()->back();
        $items = $this->db->table('prescription_items pi')
            ->select('pi.*, m.name as medication_name')
            ->join('medicines m','m.id=pi.medication_id','left')
            ->where('pi.prescription_id', function ($qb) use ($trx) {
                $qb->select('id')->from('prescriptions')->where('patient_id',$trx['patient_id'])->orderBy('id','DESC')->limit(1);
            })->get()->getResultArray();
        return view('Roles/admin/pharmacy/TransactionPrint', ['transaction'=>$trx,'items'=>$items,'title'=>'Print']);
    }
}