<?php
namespace App\Controllers;
use App\Models\TemplateReportModel;
class TemplateReportController extends BaseController
{
    protected $templateReportModel;
    public function __construct()
    {
        $this->templateReportModel = new TemplateReportModel();
    }
    public function index()
    {
        return view('layout/template', [
            'title' => 'Template Laporan',
            'pageName' => 'template_report/index',
            'pageView' => 'template_report/index',
            'pageAsset' => 'template_report',
            'pageData' => []
        ]);
    }
    public function getTemplate()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data' => $this->templateReportModel->getEditorData()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Template Report getTemplate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data template.'
            ]);
        }
    }
    public function save()
    {
        try {
            $json = $this->request->getJSON(true);
            if (!is_array($json) || !isset($json['sections']) || !is_array($json['sections'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Data template tidak valid.'
                ]);
            }
            $db = \Config\Database::connect();
            $db->transBegin();
            $submittedIds = [];
            $sortOrder = 1;
            foreach ($json['sections'] as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $sectionId = (int) ($section['id'] ?? 0);
                $sectionTitle = trim((string) ($section['title'] ?? ''));
                if ($sectionTitle === '') {
                    continue;
                }
                $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : [];
                if (count($items) === 0) {
                    if ($sectionId > 0) {
                        $existing = $this->templateReportModel->find($sectionId);
                        if ($existing) {
                            $this->templateReportModel->update($sectionId, [
                                'section_title' => $sectionTitle,
                                'item_title' => '',
                                'sort_order' => $sortOrder++
                            ]);
                            $submittedIds[] = $sectionId;
                        } else {
                            $newId = $this->templateReportModel->insert([
                                'section_title' => $sectionTitle,
                                'item_title' => '',
                                'content' => '',
                                'sort_order' => $sortOrder++
                            ], true);
                            $submittedIds[] = (int) $newId;
                        }
                    } else {
                        $newId = $this->templateReportModel->insert([
                            'section_title' => $sectionTitle,
                            'item_title' => '',
                            'content' => '',
                            'sort_order' => $sortOrder++
                        ], true);
                        $submittedIds[] = (int) $newId;
                    }
                    continue;
                }
                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $itemId = (int) ($item['id'] ?? 0);
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $content = array_key_exists('content', $item) ? (string) $item['content'] : null;
                    if ($itemId > 0) {
                        $existing = $this->templateReportModel->find($itemId);
                        if ($existing) {
                            $updateData = [
                                'section_title' => $sectionTitle,
                                'item_title' => $itemTitle,
                                'sort_order' => $sortOrder++
                            ];
                            if ($content !== null) {
                                $updateData['content'] = $content;
                            }
                            $this->templateReportModel->update($itemId, $updateData);
                            $submittedIds[] = $itemId;
                        } else {
                            $newId = $this->templateReportModel->insert([
                                'section_title' => $sectionTitle,
                                'item_title' => $itemTitle,
                                'content' => $content ?? '',
                                'sort_order' => $sortOrder++
                            ], true);
                            $submittedIds[] = (int) $newId;
                        }
                    } else {
                        $newId = $this->templateReportModel->insert([
                            'section_title' => $sectionTitle,
                            'item_title' => $itemTitle,
                            'content' => $content ?? '',
                            'sort_order' => $sortOrder++
                        ], true);
                        $submittedIds[] = (int) $newId;
                    }
                }
            }
            $existingRows = $this->templateReportModel->select('id')->findAll();
            foreach ($existingRows as $row) {
                $id = (int) $row['id'];
                if (!in_array($id, $submittedIds, true)) {
                    $this->templateReportModel->delete($id);
                }
            }
            if ($db->transStatus() === false) {
                $db->transRollback();
                throw new \RuntimeException('Gagal menyimpan template.');
            }
            $db->transCommit();
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Template berhasil disimpan.'
            ]);
        } catch (\Throwable $e) {
            if (isset($db) && $db->transStatus() !== false) {
                $db->transRollback();
            }
            log_message('error', 'Template Report Save: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Template gagal disimpan.'
            ]);
        }
    }
}