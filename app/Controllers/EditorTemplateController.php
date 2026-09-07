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
        // Hanya admin yang dapat mengakses halaman editor
        if (strtolower((string) session()->get('role')) !== 'admin') {
            return redirect()->to('/dashboard')
                ->with('error', 'Akses hanya untuk admin.');
        }

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
        // Hanya admin yang dapat mengambil data template
        if (strtolower((string) session()->get('role')) !== 'admin') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Akses hanya untuk admin.'
                ]);
        }

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
        // Hanya admin yang dapat menyimpan perubahan template
        if (strtolower((string) session()->get('role')) !== 'admin') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Akses hanya untuk admin.'
                ]);
        }

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