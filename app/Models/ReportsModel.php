<?php
namespace App\Models;
use CodeIgniter\Model;
class ReportsModel extends Model
{
    protected $table='visits';
    protected $primaryKey='id';
    
    public function getReports(
        $keyword='',
        $regionId='',
        $status='',
        $dateFrom='',
        $dateTo=''
    ) {
        $builder=$this->db->table('visits v');

        $builder->select('
            v.id,
            v.school_id,
            v.visit_date,
            v.status,
            v.created_at,
            v.updated_at,
            s.npsn,
            s.school_name,
            s.level,
            s.region_id,
            r.name AS region_name
        ');

        $builder->join(
            'schools s',
            's.id=v.school_id',
            'left'
        );

        $builder->join(
            'region r',
            'r.id=s.region_id',
            'left'
        );

        // FILTER KEYWORD
        if($keyword!==''){
            $builder->groupStart();

            $builder->like(
                's.school_name',
                $keyword
            );

            $builder->orLike(
                's.npsn',
                $keyword
            );

            $builder->orLike(
                'r.name',
                $keyword
            );

            $builder->groupEnd();
        }

        // FILTER WILAYAH
        if($regionId!==''){
            $builder->where(
                's.region_id',
                (int)$regionId
            );
        }

        // FILTER STATUS
        if($status!==''){
            $builder->where(
                'v.status',
                $status
            );
        }

        // FILTER TANGGAL MULAI
        if($dateFrom!==''){
            $builder->where(
                'v.visit_date >=',
                $dateFrom
            );
        }

        // FILTER TANGGAL SELESAI
        if($dateTo!==''){
            $builder->where(
                'v.visit_date <=',
                $dateTo
            );
        }

        $builder->orderBy(
            'v.visit_date',
            'DESC'
        );

        $builder->orderBy(
            'v.id',
            'DESC'
        );

        $visits=$builder->get()->getResultArray();

        foreach($visits as &$visit){

            $visit['members']=$this->getMembers(
                $visit['id']
            );

            $names=[];

            foreach($visit['members'] as $member){

                if(!empty($member['name'])){
                    $names[]=$member['name'];
                }

            }

            $visit['member_names']=implode(
                ', ',
                $names
            );
        }

        return $visits;
    }
    public function getReport($id)
    {
        $id=(int)$id;
        $builder=$this->db->table('visits v');
        $builder->select('v.id,v.school_id,v.visit_date,v.status,v.created_at,v.updated_at,s.npsn,s.school_name,s.level,s.region_id,r.name AS region_name');
        $builder->join('schools s','s.id=v.school_id','left');
        $builder->join('region r','r.id=s.region_id','left');
        $builder->where('v.id',$id);
        $visit=$builder->get()->getRowArray();
        if(!$visit){
            return null;
        }
        $visit['members']=$this->getMembers($id);
        $names=[];
        foreach($visit['members'] as $member){
            if(!empty($member['name'])){
                $names[]=$member['name'];
            }
        }
        $visit['member_names']=implode(', ',$names);
        $allAnswers=$this->db->table('visit_answers va')
            ->select('va.id,va.visit_id,va.question_id,va.answer,i.code,i.question,i.description,i.answer_type,i.options')
            ->join('instruments i','i.id=va.question_id','left')
            ->where('va.visit_id',$id)
            ->orderBy('va.question_id','ASC')
            ->get()
            ->getResultArray();
        $visit['answers']=[];
        $visit['documents']=[];
        $visit['photos']=[];
        foreach($allAnswers as $answer){
            $questionId=(int)$answer['question_id'];
            if($questionId===20){
                if(!empty($answer['answer'])){
                    $visit['documents'][]=$answer;
                }
                continue;
            }
            if($questionId===21){
                if(!empty($answer['answer'])){
                    $visit['photos'][]=$answer;
                }
                continue;
            }
            $visit['answers'][]=$answer;
        }
        return $visit;
    }
    protected function getMembers($visitId)
    {
        return $this->db->table('visit_team vt')
            ->select('u.id,u.name')
            ->join('users u','u.id=vt.user_id','left')
            ->where('vt.visit_id',(int)$visitId)
            ->orderBy('u.name','ASC')
            ->get()
            ->getResultArray();
    }
    public function getRegions()
    {
        return $this->db->table('region')
            ->select('id,name')
            ->orderBy('name','ASC')
            ->get()
            ->getResultArray();
    }
}