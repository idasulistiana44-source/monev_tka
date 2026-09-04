<?php

namespace App\Controllers;

use App\Models\TemplateReportModel;

class EditorTemplateController extends BaseController
{
    protected TemplateReportModel $templateReportModel;

    public function __construct()
    {
        $this->templateReportModel = new TemplateReportModel();
    }

    /**
     * Halaman Editor Template
     */
    public function index()
    {
        return view('layout/template', [
            'title'     => 'Editor Template',
            'pageName'  => 'template_report/editor',
            'pageView'  => 'template_report/editor',
            'pageAsset' => 'report_templates',
            'pageData'  => []
        ]);
    }

    /**
     * Ambil data template untuk editor
     */
    public function getData()
    {
        try {

            $data = $this->templateReportModel
                ->getEditorData();

            return $this->response->setJSON([
                'status' => true,
                'data'   => $data
            ]);

        } catch (\Throwable $e) {

            log_message(
                'error',
                'EditorTemplateController::getData - ' .
                $e->getMessage()
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Template gagal dimuat.'
                ]);
        }
    }

    /**
     * Simpan isi editor
     */
    public function save()
    {
        try {

            $data = $this->request->getJSON(true);

            if (!is_array($data)) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'Data template tidak valid.'
                    ]);
            }

            foreach ($data as $item) {

                $id = isset($item['id'])
                    ? (int) $item['id']
                    : 0;

                if ($id <= 0) {
                    continue;
                }

                $existing = $this->templateReportModel->find($id);

                if (!$existing) {
                    continue;
                }

                $content = isset($item['content'])
                    ? (string) $item['content']
                    : '';

                $this->templateReportModel->updateContent(
                    $id,
                    $content
                );
            }

            return $this->response->setJSON([
                'status'   => true,
                'message'  => 'Konten template berhasil disimpan.',
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {

            log_message(
                'error',
                'EditorTemplateController::save - ' .
                $e->getMessage()
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan saat menyimpan template.'
                ]);
        }
    }
}