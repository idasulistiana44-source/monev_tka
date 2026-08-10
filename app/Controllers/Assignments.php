<?php
namespace App\Controllers;
use App\Models\AssignmentModel;
class Assignments extends BaseController
{
    protected $assignmentModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
    }

    public function index()
    {
        return view('layout/template',[
            'title'=>'Assignments',
            'pageName'=>'assignments/index',
            'pageView'=>'assignments/index',
            'pageAsset'=>'assignments',
            'pageData'=>[]
        ]);
    }

    public function data()
    {
        $keyword = $this->request->getGet('keyword') ?? '';
        $status = $this->request->getGet('status') ?? '';

        return $this->response->setJSON([
            'success'=>true,
            'data'=>$this->assignmentModel->getAssignments($keyword,$status)
        ]);
    }

    public function schools()
    {
        return $this->response->setJSON([
            'success'=>true,
            'data'=>$this->assignmentModel->getSchools()
        ]);
    }

    public function users()
    {
        return $this->response->setJSON([
            'success'=>true,
            'data'=>$this->assignmentModel->getUsers()
        ]);
    }

    public function detail($id)
    {
        $data = $this->assignmentModel->getAssignment($id);

        if(!$data){
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success'=>false,
                    'message'=>'Assignment not found.'
                ]);
        }

        return $this->response->setJSON([
            'success'=>true,
            'data'=>$data
        ]);
    }

    public function store()
    {
        $schoolId = $this->request->getPost('school_id');
        $userId = $this->request->getPost('user_id');
        $assignmentDate = $this->request->getPost('assignment_date');
        $status = $this->request->getPost('status') ?: 'ACTIVE';
        $notes = $this->request->getPost('notes');

        if(!$schoolId || !$userId || !$assignmentDate){
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success'=>false,
                    'message'=>'School, officer and assignment date are required.'
                ]);
        }

        if($status === 'ACTIVE' && $this->assignmentModel->hasActiveAssignment($schoolId)){
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success'=>false,
                    'message'=>'This school already has an active assignment.'
                ]);
        }

        $this->assignmentModel->insert([
            'school_id'=>$schoolId,
            'user_id'=>$userId,
            'assignment_date'=>$assignmentDate,
            'status'=>$status,
            'notes'=>$notes
        ]);

        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Assignment successfully created.'
        ]);
    }

    public function update($id)
    {
        $existing = $this->assignmentModel->find($id);

        if(!$existing){
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success'=>false,
                    'message'=>'Assignment not found.'
                ]);
        }

        $schoolId = $this->request->getPost('school_id');
        $userId = $this->request->getPost('user_id');
        $assignmentDate = $this->request->getPost('assignment_date');
        $status = $this->request->getPost('status') ?: 'ACTIVE';
        $notes = $this->request->getPost('notes');

        if(!$schoolId || !$userId || !$assignmentDate){
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success'=>false,
                    'message'=>'School, officer and assignment date are required.'
                ]);
        }

        if($status === 'ACTIVE' && $this->assignmentModel->hasActiveAssignment($schoolId,$id)){
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success'=>false,
                    'message'=>'This school already has an active assignment.'
                ]);
        }

        $this->assignmentModel->update($id,[
            'school_id'=>$schoolId,
            'user_id'=>$userId,
            'assignment_date'=>$assignmentDate,
            'status'=>$status,
            'notes'=>$notes
        ]);

        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Assignment successfully updated.'
        ]);
    }

    public function delete($id)
    {
        $existing = $this->assignmentModel->find($id);

        if(!$existing){
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success'=>false,
                    'message'=>'Assignment not found.'
                ]);
        }

        $this->assignmentModel->delete($id);

        return $this->response->setJSON([
            'success'=>true,
            'message'=>'Assignment successfully deleted.'
        ]);
    }
}