<?php
namespace App\Models;
use CodeIgniter\Model;
class TemplateReportModel extends Model
{
    protected $table = 'report_template_sections';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'section_title',
        'item_title',
        'content',
        'sort_order'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    public function getTemplate()
    {
        return $this->select([
            'id',
            'section_title',
            'item_title',
            'content',
            'sort_order'
        ])->orderBy('sort_order', 'ASC')->findAll();
    }
    public function getEditorData()
    {
        return $this->select([
            'id',
            'section_title',
            'item_title',
            'content',
            'sort_order'
        ])->orderBy('sort_order', 'ASC')->findAll();
    }
    public function updateContent(int $id, string $content): bool
    {
        return $this->update($id, [
            'content' => $content
        ]);
    }
}