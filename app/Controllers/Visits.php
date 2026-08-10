<?php
namespace App\Controllers;
use App\Models\VisitModel;
use Config\Database;
class Visits extends BaseController
{
    protected $db;
    protected $visitModel;
    public function __construct()
    {
        $this->db=Database::connect();
        $this->visitModel=new VisitModel();
    }
    public function index()
    {
        return view('layout/template',['title'=>'Visitasi','pageView'=>'visits/index','pageAsset'=>'visits','pageCss'=>'visits','pageData'=>[]]);
    }
    public function data()
    {
        try {
            $keyword=trim((string)$this->request->getGet('keyword'));
            $status=trim((string)$this->request->getGet('status'));
            $data=$this->visitModel->getVisits($keyword,$status);
            return $this->response->setJSON(['success'=>true,'data'=>$data]);
        } catch (\Throwable $e) {
            log_message('error','ERROR LOAD VISITS: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage(),'data'=>[]]);
        }
    }
    public function assignments()
    {
        try {
            $data=$this->visitModel->getActiveAssignments();
            $available=[];
            foreach($data as $assignment){
                if(!$this->visitModel->hasVisitForAssignment($assignment['id'])){
                    $available[]=$assignment;
                }
            }
            return $this->response->setJSON(['success'=>true,'data'=>$available]);
        } catch (\Throwable $e) {
            log_message('error','ERROR LOAD ASSIGNMENTS: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage(),'data'=>[]]);
        }
    }
    public function store()
    {
        if(!$this->request->is('post')){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Method tidak diizinkan.']);
        }
        $assignmentId=$this->request->getPost('assignment_id');
        $visitDate=trim((string)$this->request->getPost('visit_date'));
        if(!$assignmentId||!is_numeric($assignmentId)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Assignment wajib dipilih.']);
        }
        if($visitDate===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Tanggal visitasi wajib diisi.']);
        }
        $assignment=$this->db->table('assignments')->where('id',(int)$assignmentId)->where('status','ACTIVE')->get()->getRowArray();
        if(!$assignment){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Assignment aktif tidak ditemukan.']);
        }
        if(empty($assignment['school_id'])){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Assignment belum memiliki sekolah.']);
        }
        $school=$this->db->table('schools')->where('id',(int)$assignment['school_id'])->get()->getRowArray();
        if(!$school){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Sekolah pada assignment tidak ditemukan.']);
        }
        if($this->visitModel->hasVisitForAssignment((int)$assignmentId)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Assignment ini sudah memiliki data visitasi.']);
        }
        $id=$this->visitModel->createVisit([
            'assignment_id'=>(int)$assignmentId,
            'school_id'=>(int)$assignment['school_id'],
            'visit_date'=>$visitDate,
            'status'=>'DRAFT',
            'notes'=>null,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ]);
        if(!$id){
            log_message('error','ERROR CREATE VISIT: '.json_encode($this->visitModel->errors()));
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Visitasi gagal dibuat.','errors'=>$this->visitModel->errors()]);
        }
        return $this->response->setJSON(['success'=>true,'message'=>'Visitasi berhasil dibuat.','data'=>['id'=>$id]]);
    }
    public function detail($id)
    {
        try {
            $id=(int)$id;
            $data=$this->visitModel->getVisit($id);
            if(!$data){
                return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Data visitasi tidak ditemukan.']);
            }
            return $this->response->setJSON(['success'=>true,'data'=>$data]);
        } catch (\Throwable $e) {
            log_message('error','ERROR VISIT DETAIL: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function start($id)
    {
        try {
            $id=(int)$id;
            if($id<=0){
                return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'ID visitasi tidak valid.']);
            }
            $visit=$this->visitModel->getVisit($id);
            if(!$visit){
                return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Data visitasi tidak ditemukan.']);
            }
            if($visit['status']==='VERIFIED'){
                return $this->response->setJSON(['success'=>true,'message'=>'Visitasi sudah diverifikasi.','data'=>['redirect'=>base_url('visits/instrument/'.$id)]]);
            }
            if($visit['status']==='COMPLETED'){
                return $this->response->setJSON(['success'=>true,'message'=>'Visitasi sudah selesai.','data'=>['redirect'=>base_url('visits/instrument/'.$id)]]);
            }
            if($visit['status']==='DRAFT'){
                if(!$this->visitModel->updateStatus($id,'IN_PROGRESS')){
                    return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Status visitasi gagal diperbarui.']);
                }
            }
            return $this->response->setJSON(['success'=>true,'message'=>'Visitasi dimulai.','data'=>['redirect'=>base_url('visits/instrument/'.$id)]]);
        } catch (\Throwable $e) {
            log_message('error','ERROR START VISIT: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function instrument($id)
    {
        $id=(int)$id;
        if($id<=0){
            return redirect()->to(base_url('visits'))->with('error','ID visitasi tidak valid.');
        }
        $visit=$this->visitModel->getVisit($id);
        if(!$visit){
            return redirect()->to(base_url('visits'))->with('error','Data visitasi tidak ditemukan.');
        }
        return view('layout/template',['title'=>'Isi Instrumen Visitasi','pageName'=>'visits/instrument','pageView'=>'visits/instrument','pageAsset'=>'visit-instrument','pageCss'=>'visit-instrument','pageData'=>['visitId'=>$id]]);
    }
    public function instrumentData($id)
    {
        try {
            $id=(int)$id;
            if($id<=0){
                return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'ID visitasi tidak valid.']);
            }
            $visit=$this->visitModel->getVisit($id);
            if(!$visit){
                return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Visitasi tidak ditemukan.']);
            }
            $sections=$this->visitModel->getInstrumentSections();
            foreach($sections as &$section){
                $questions=$this->visitModel->getInstrumentQuestions($section['id'],$id);
                foreach($questions as &$question){
                    if(!empty($question['options'])){
                        $decoded=json_decode($question['options'],true);
                        $question['options']=is_array($decoded)?$decoded:[];
                    }else{
                        $question['options']=[];
                    }
                }
                unset($question);
                $section['questions']=$questions;
            }
            unset($section);
            return $this->response->setJSON(['success'=>true,'data'=>['visit'=>$visit,'sections'=>$sections]]);
        } catch (\Throwable $e) {
            log_message('error','ERROR LOAD INSTRUMENT: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function saveAnswers($id)
    {
        if(!$this->request->is('post')){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Method tidak diizinkan.']);
        }
        $id=(int)$id;
        if($id<=0){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'ID visitasi tidak valid.']);
        }
        $visit=$this->visitModel->getVisit($id);
        if(!$visit){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Visitasi tidak ditemukan.']);
        }
        if($visit['status']==='COMPLETED'||$visit['status']==='VERIFIED'){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Visitasi sudah selesai dan tidak dapat diubah.']);
        }
        $answers=$this->request->getPost('answers');
        if(is_string($answers)){
            $answers=json_decode($answers,true);
        }
        if(!is_array($answers)){
            $answers=[];
        }
        if(!$this->visitModel->saveAnswers($id,$answers)){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Data visitasi gagal disimpan.']);
        }
        if($visit['status']==='DRAFT'){
            $this->visitModel->updateStatus($id,'IN_PROGRESS');
        }
        return $this->response->setJSON(['success'=>true,'message'=>'Data visitasi berhasil disimpan.']);
    }
    public function complete($id)
    {
        if(!$this->request->is('post')){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Method tidak diizinkan.']);
        }
        $id=(int)$id;
        if($id<=0){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'ID visitasi tidak valid.']);
        }
        $visit=$this->visitModel->getVisit($id);
        if(!$visit){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Visitasi tidak ditemukan.']);
        }
        if($visit['status']==='COMPLETED'||$visit['status']==='VERIFIED'){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Visitasi sudah selesai.']);
        }
        $answers=$this->request->getPost('answers');
        if(is_string($answers)){
            $answers=json_decode($answers,true);
        }
        if(!is_array($answers)){
            $answers=[];
        }
        if(!$this->visitModel->saveAnswers($id,$answers)){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Jawaban gagal disimpan.']);
        }
        if(!$this->visitModel->updateStatus($id,'COMPLETED')){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Visitasi gagal diselesaikan.']);
        }
        return $this->response->setJSON(['success'=>true,'message'=>'Visitasi berhasil diselesaikan.','data'=>['redirect'=>base_url('visits')]]);
    }
}