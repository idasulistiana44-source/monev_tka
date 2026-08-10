<?php
namespace App\Models;
use CodeIgniter\Model;
class VisitModel extends Model
{
    protected $table='visits';
    protected $primaryKey='id';
    protected $returnType='array';
    protected $allowedFields=['assignment_id','school_id','visit_date','status','notes','created_at','updated_at'];
    public function getVisits($keyword='',$status='')
    {
        $builder=$this->db->table('visits v');
        $builder->select('v.id,v.assignment_id,v.school_id,v.visit_date,v.status,v.notes,v.created_at,v.updated_at,a.user_id,a.assignment_date,a.status as assignment_status,s.npsn,s.school_name,s.level,u.name as officer_name,u.username as officer_username');
        $builder->join('assignments a','a.id=v.assignment_id','left');
        $builder->join('schools s','s.id=v.school_id','left');
        $builder->join('users u','u.id=a.user_id','left');
        if($keyword!==''){
            $builder->groupStart();
            $builder->like('s.npsn',$keyword);
            $builder->orLike('s.school_name',$keyword);
            $builder->orLike('u.name',$keyword);
            $builder->groupEnd();
        }
        if($status!==''){
            $builder->where('v.status',$status);
        }
        return $builder->orderBy('v.visit_date','DESC')->orderBy('v.id','DESC')->get()->getResultArray();
    }
    public function getVisit($id)
    {
        return $this->db->table('visits v')
            ->select('v.id,v.assignment_id,v.school_id,v.visit_date,v.status,v.notes,v.created_at,v.updated_at,a.user_id,a.assignment_date,a.status as assignment_status,a.notes as assignment_notes,s.npsn,s.school_name,s.level,u.name as officer_name,u.username as officer_username')
            ->join('assignments a','a.id=v.assignment_id','left')
            ->join('schools s','s.id=v.school_id','left')
            ->join('users u','u.id=a.user_id','left')
            ->where('v.id',(int)$id)
            ->get()
            ->getRowArray();
    }
    public function getActiveAssignments()
    {
        return $this->db->table('assignments a')
            ->select('a.id,a.school_id,a.user_id,a.assignment_date,a.status,a.notes,s.npsn,s.school_name,s.level,u.name as officer_name,u.username as officer_username')
            ->join('schools s','s.id=a.school_id','left')
            ->join('users u','u.id=a.user_id','left')
            ->where('a.status','ACTIVE')
            ->orderBy('a.assignment_date','ASC')
            ->get()
            ->getResultArray();
    }
    public function createVisit($data)
    {
        return $this->insert($data);
    }
    public function updateStatus($id,$status)
    {
        return $this->update((int)$id,['status'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);
    }
    public function getInstrumentSections()
    {
        return $this->db->table('instrument_sections')
            ->where('is_active',1)
            ->orderBy('sort_order','ASC')
            ->orderBy('id','ASC')
            ->get()
            ->getResultArray();
    }
    public function getInstrumentQuestions($sectionId,$visitId)
    {
        $builder=$this->db->table('instruments q');
        $builder->select('q.id,q.section_id,q.code,q.question,q.description,q.answer_type,q.options,q.is_required,q.sort_order,va.answer');
        $builder->join('visit_answers va','va.question_id=q.id AND va.visit_id='.(int)$visitId,'left');
        $builder->where('q.section_id',(int)$sectionId);
        $builder->where('q.is_active',1);
        return $builder->orderBy('q.sort_order','ASC')->orderBy('q.id','ASC')->get()->getResultArray();
    }
    public function saveAnswers($visitId,$answers)
    {
        $this->db->transStart();
        foreach($answers as $questionId=>$answer){
            $questionId=(int)$questionId;
            if($questionId<=0){
                continue;
            }
            if(is_array($answer)){
                $answer=json_encode($answer,JSON_UNESCAPED_UNICODE);
            }else{
                $answer=(string)$answer;
            }
            $existing=$this->db->table('visit_answers')->where(['visit_id'=>(int)$visitId,'question_id'=>$questionId])->get()->getRowArray();
            if($existing){
                $this->db->table('visit_answers')->where('id',$existing['id'])->update([
                    'answer'=>$answer,
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            }else{
                $this->db->table('visit_answers')->insert([
                    'visit_id'=>(int)$visitId,
                    'question_id'=>$questionId,
                    'answer'=>$answer,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            }
        }
        $this->db->transComplete();
        return $this->db->transStatus();
    }
    public function hasVisitForAssignment($assignmentId)
    {
        return $this->where('assignment_id',(int)$assignmentId)->whereIn('status',['DRAFT','IN_PROGRESS','COMPLETED','VERIFIED'])->countAllResults()>0;
    }
}