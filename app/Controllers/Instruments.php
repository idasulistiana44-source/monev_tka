<?php
namespace App\Controllers;
use App\Models\InstrumentModel;
class Instruments extends BaseController
{
    protected $instrumentModel;
    public function __construct()
    {
        $this->instrumentModel=new InstrumentModel();
    }
    public function index()
    {
        return view('layout/template',[
            'title'=>'Instrumen Monev',
            'pageView'=>'instruments/index',
            'pageAsset'=>'instruments',
            'pageData'=>[]
        ]);
    }
    public function data()
    {
        try{
            $keyword=trim((string)$this->request->getGet('keyword'));
            $sectionId=trim((string)$this->request->getGet('section_id'));
            return $this->response->setJSON([
                'success'=>true,
                'data'=>$this->instrumentModel->getAllInstruments($keyword,$sectionId)
            ]);
        }catch(\Throwable $e){
            log_message('error','INSTRUMENT DATA: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success'=>false,
                'message'=>'Data instrumen gagal dimuat.'
            ]);
        }
    }
    public function sections()
    {
        try{
            return $this->response->setJSON([
                'success'=>true,
                'data'=>$this->instrumentModel->getSections()
            ]);
        }catch(\Throwable $e){
            return $this->response->setStatusCode(500)->setJSON([
                'success'=>false,
                'message'=>'Data section gagal dimuat.'
            ]);
        }
    }
    public function detail($id)
    {
        try{
            $data=$this->instrumentModel->getInstrument((int)$id);
            if(!$data){
                return $this->response->setStatusCode(404)->setJSON([
                    'success'=>false,
                    'message'=>'Instrumen tidak ditemukan.'
                ]);
            }
            return $this->response->setJSON([
                'success'=>true,
                'data'=>$data
            ]);
        }catch(\Throwable $e){
            return $this->response->setStatusCode(500)->setJSON([
                'success'=>false,
                'message'=>'Detail instrumen gagal dimuat.'
            ]);
        }
    }
    public function store()
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON([
                'success'=>false,
                'message'=>'Request tidak valid.'
            ]);
        }
        $sectionId=(int)$this->request->getPost('section_id');
        $code=trim((string)$this->request->getPost('code'));
        $question=trim((string)$this->request->getPost('question'));
        $description=trim((string)$this->request->getPost('description'));
        $answerType=trim((string)$this->request->getPost('answer_type'));
        $options=trim((string)$this->request->getPost('options'));
        $isRequired=(int)$this->request->getPost('is_required');
        $isActive=(int)$this->request->getPost('is_active');
        $sortOrder=(int)$this->request->getPost('sort_order');
        if($sectionId<=0){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Section wajib dipilih.']);
        }
        if(!$this->instrumentModel->getSection($sectionId)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Section tidak ditemukan.']);
        }
        if($code===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Kode instrumen wajib diisi.']);
        }
        if($question===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Pertanyaan wajib diisi.']);
        }
        $allowedTypes=['text','textarea','number','date','select','radio','checkbox','yesno','pdf','photo'];
        if(!in_array($answerType,$allowedTypes,true)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Jenis jawaban tidak valid.']);
        }
        $id=$this->instrumentModel->createInstrument([
            'section_id'=>$sectionId,
            'code'=>$code,
            'question'=>$question,
            'description'=>$description,
            'answer_type'=>$answerType,
            'options'=>$options,
            'is_required'=>$isRequired?1:0,
            'is_active'=>$isActive?1:0,
            'sort_order'=>$sortOrder
        ]);
        if(!$id){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Instrumen gagal ditambahkan.']);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Instrumen berhasil ditambahkan.',
            'data'=>$this->instrumentModel->getInstrument($id)
        ]);
    }
    public function update($id)
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Request tidak valid.']);
        }
        $id=(int)$id;
        if(!$this->instrumentModel->getInstrument($id)){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Instrumen tidak ditemukan.']);
        }
        $sectionId=(int)$this->request->getPost('section_id');
        $code=trim((string)$this->request->getPost('code'));
        $question=trim((string)$this->request->getPost('question'));
        $description=trim((string)$this->request->getPost('description'));
        $answerType=trim((string)$this->request->getPost('answer_type'));
        $options=trim((string)$this->request->getPost('options'));
        $isRequired=(int)$this->request->getPost('is_required');
        $isActive=(int)$this->request->getPost('is_active');
        $sortOrder=(int)$this->request->getPost('sort_order');
        if($sectionId<=0||!$this->instrumentModel->getSection($sectionId)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Section tidak valid.']);
        }
        if($code===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Kode instrumen wajib diisi.']);
        }
        if($question===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Pertanyaan wajib diisi.']);
        }
        $allowedTypes=['text','textarea','number','date','select','radio','checkbox','yesno','pdf','photo'];
        if(!in_array($answerType,$allowedTypes,true)){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Jenis jawaban tidak valid.']);
        }
        $result=$this->instrumentModel->updateInstrument($id,[
            'section_id'=>$sectionId,
            'code'=>$code,
            'question'=>$question,
            'description'=>$description,
            'answer_type'=>$answerType,
            'options'=>$options,
            'is_required'=>$isRequired?1:0,
            'is_active'=>$isActive?1:0,
            'sort_order'=>$sortOrder
        ]);
        if(!$result){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Instrumen gagal diperbarui.']);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Instrumen berhasil diperbarui.',
            'data'=>$this->instrumentModel->getInstrument($id)
        ]);
    }
    public function delete($id)
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Request tidak valid.']);
        }
        $id=(int)$id;
        if(!$this->instrumentModel->getInstrument($id)){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Instrumen tidak ditemukan.']);
        }
        if(!$this->instrumentModel->deleteInstrument($id)){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Instrumen gagal dihapus.']);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Instrumen berhasil dihapus.'
        ]);
    }
    public function sectionStore()
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Request tidak valid.']);
        }
        $name=trim((string)$this->request->getPost('name'));
        $description=trim((string)$this->request->getPost('description'));
        $sortOrder=(int)$this->request->getPost('sort_order');
        if($name===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Nama section wajib diisi.']);
        }
        $id=$this->instrumentModel->createSection([
            'name'=>$name,
            'description'=>$description,
            'sort_order'=>$sortOrder,
            'is_active'=>1
        ]);
        if(!$id){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Section gagal ditambahkan.']);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Section berhasil ditambahkan.',
            'data'=>$this->instrumentModel->getSection($id)
        ]);
    }
    public function sectionUpdate($id)
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Request tidak valid.']);
        }
        $id=(int)$id;
        if(!$this->instrumentModel->getSection($id)){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Section tidak ditemukan.']);
        }
        $name=trim((string)$this->request->getPost('name'));
        $description=trim((string)$this->request->getPost('description'));
        $sortOrder=(int)$this->request->getPost('sort_order');
        if($name===''){
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Nama section wajib diisi.']);
        }
        if(!$this->instrumentModel->updateSection($id,[
            'name'=>$name,
            'description'=>$description,
            'sort_order'=>$sortOrder
        ])){
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Section gagal diperbarui.']);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Section berhasil diperbarui.'
        ]);
    }
    public function sectionDelete($id)
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Request tidak valid.']);
        }
        $id=(int)$id;
        if(!$this->instrumentModel->getSection($id)){
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Section tidak ditemukan.']);
        }
        if(!$this->instrumentModel->deleteSection($id)){
            return $this->response->setStatusCode(422)->setJSON([
                'success'=>false,
                'message'=>'Section tidak dapat dihapus karena masih memiliki instrumen.'
            ]);
        }
        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Section berhasil dihapus.'
        ]);
    }
}