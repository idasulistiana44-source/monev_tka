<?php
namespace App\Models;
use CodeIgniter\Model;
class InstrumentModel extends Model
{
    protected $table='instruments';
    protected $primaryKey='id';
    protected $returnType='array';
    protected $allowedFields=['section_id','code','question','description','answer_type','options','is_required','is_active','sort_order','created_at','updated_at'];
    public function getAllInstruments($keyword='',$sectionId='')
    {
        $builder=$this->db->table('instruments i');
        $builder->select('i.*,s.name as section_name');
        $builder->join('instrument_sections s','s.id=i.section_id','left');
        if($keyword!==''){
            $builder->groupStart();
            $builder->like('i.code',$keyword);
            $builder->orLike('i.question',$keyword);
            $builder->orLike('s.name',$keyword);
            $builder->groupEnd();
        }
        if($sectionId!==''){
            $builder->where('i.section_id',(int)$sectionId);
        }
        return $builder->orderBy('s.sort_order','ASC')->orderBy('i.sort_order','ASC')->orderBy('i.id','ASC')->get()->getResultArray();
    }
    public function getInstrument($id)
    {
        return $this->db->table('instruments i')->select('i.*,s.name as section_name')->join('instrument_sections s','s.id=i.section_id','left')->where('i.id',(int)$id)->get()->getRowArray();
    }
    public function getSections()
    {
        return $this->db->table('instrument_sections')->where('is_active',1)->orderBy('sort_order','ASC')->orderBy('name','ASC')->get()->getResultArray();
    }
    public function getSection($id)
    {
        return $this->db->table('instrument_sections')->where('id',(int)$id)->get()->getRowArray();
    }
    public function createSection($data)
    {
        $data['created_at']=date('Y-m-d H:i:s');
        $data['updated_at']=date('Y-m-d H:i:s');
        $this->db->table('instrument_sections')->insert($data);
        return $this->db->insertID();
    }
    public function updateSection($id,$data)
    {
        $data['updated_at']=date('Y-m-d H:i:s');
        return $this->db->table('instrument_sections')->where('id',(int)$id)->update($data);
    }
    public function deleteSection($id)
    {
        $count=$this->db->table('instruments')->where('section_id',(int)$id)->countAllResults();
        if($count>0){
            return false;
        }
        return $this->db->table('instrument_sections')->where('id',(int)$id)->delete();
    }
    public function createInstrument($data)
    {
        $data['created_at']=date('Y-m-d H:i:s');
        $data['updated_at']=date('Y-m-d H:i:s');
        $this->insert($data);
        return $this->getInsertID();
    }
    public function updateInstrument($id,$data)
    {
        $data['updated_at']=date('Y-m-d H:i:s');
        return $this->update((int)$id,$data);
    }
    public function deleteInstrument($id)
    {
        return $this->delete((int)$id);
    }
}