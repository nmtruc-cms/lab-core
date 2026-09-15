<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Throwable;
use Modules\IMS\Models\BrandModel;
use Modules\IMS\Models\ChecklistModel;
use Modules\IMS\Models\ChecklistDetailModel;
use Modules\IMS\Models\SupplierEvaluationDetailModel;
use Modules\IMS\Models\SupplierEvaluationModel;
use Modules\IMS\Models\SupplierModel;
use Modules\IMS\Models\SupplierBrandModel;
use Modules\IMS\Models\SupplierUploadModel;

class SupplierController extends BaseImsController
{
    public function index(): string
    {
        $state = $this->resolvePaginationState();
        $db    = Database::connect();

        $total = (int) $db->table('ims_suppliers')->countAllResults();
        $pager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $supplierRows = $db->table('ims_suppliers supplier')
            ->select('supplier.*, class.class_name as approved_status_name')
            ->join('ims_supplier_classification class', 'class.id = supplier.approved_status', 'left')
            ->orderBy('supplier.supplier_name', 'asc')
            ->limit($pager['perPage'], $pager['offset'])
            ->get()->getResultArray();

        $mappingRows = $db->table('ims_supplier_brand isb')
            ->select('isb.supplier_id, isb.brand_id, ib.brand_name')
            ->join('ims_brands ib', 'ib.id = isb.brand_id')
            ->orderBy('ib.brand_name', 'asc')
            ->get()->getResultArray();

        $supplierBrandMap = [];
        foreach ($mappingRows as $mappingRow) {
            $sid = (int) $mappingRow['supplier_id'];
            $supplierBrandMap[$sid]['brands'][] = ['id' => (int) $mappingRow['brand_id'], 'brand_name' => $mappingRow['brand_name']];
        }

        foreach ($supplierRows as &$supplierRow) {
            $sid = (int) $supplierRow['id'];
            $supplierRow['mapped_brands'] = $supplierBrandMap[$sid]['brands'] ?? [];
        }
        unset($supplierRow);

        return $this->render('Modules\IMS\Views\supplier\suppliers', [
            'pageTitle'     => lang('IMS.suppliers.indexTitle'),
            'pageSubtitle'  => lang('IMS.suppliers.indexSubtitle'),
            'rows'          => $supplierRows,
            'supplierPager' => $pager,
            'supplierQuery' => ['page' => $pager['page'], 'per_page' => $pager['perPage']],
            'validation'    => session('errors') ?? [],
            'modalState'    => session('ims_suppliers_modal'),
        ]);
    }

    public function detail(int $id): string
    {
        $db = Database::connect();
        $supplier = $db->table('ims_suppliers supplier')
            ->select('supplier.*, class.class_name as approved_status_name')
            ->join('ims_supplier_classification class', 'class.id = supplier.approved_status', 'left')
            ->where('supplier.id', $id)
            ->get()
            ->getRowArray();

        if ($supplier === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Supplier record was not found.');
        }

        $brandRows = $db->table('ims_supplier_brand isb')
            ->select('isb.*, ib.brand_name, ib.country, ib.website, ib.logo, ib.is_active')
            ->join('ims_brands ib', 'ib.id = isb.brand_id', 'left')
            ->where('isb.supplier_id', $id)
            ->orderBy('ib.brand_name', 'asc')
            ->get()
            ->getResultArray();
        $mappedBrandIds = array_map(static fn (array $row): int => (int) $row['brand_id'], $brandRows);

        $purchaseOrders = $db->table('ims_purchase_orders ipo')
            ->select('ipo.*, creator.username as created_by_name, approver.username as approved_by_name')
            ->join('users creator', 'creator.id = ipo.created_by', 'left')
            ->join('users approver', 'approver.id = ipo.approved_by', 'left')
            ->where('ipo.supplier_id', $id)
            ->orderBy('ipo.order_date', 'desc')
            ->orderBy('ipo.id', 'desc')
            ->get()
            ->getResultArray();

        $stockLots = $db->table('ims_stock_lots isl')
            ->select('isl.*, iim.item_code, iim.item_name, iu.unit_name, init_unit.unit_name as initial_unit_name, brand.brand_name, loc.name as storage_location_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_units init_unit', 'init_unit.id = isl.initial_unit_id', 'left')
            ->join('ims_brands brand', 'brand.id = isl.brand_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = isl.storage_location_id', 'left')
            ->where('isl.supplier_id', $id)
            ->orderBy('isl.id', 'desc')
            ->get()
            ->getResultArray();

        $evaluationRows = $db->table('ims_supplier_evaluation ise')
            ->select('ise.*, ic.checklist_name, evaluator.username as evaluated_by_name')
            ->join('ims_checklist ic', 'ic.id = ise.ims_checklist_id', 'left')
            ->join('users evaluator', 'evaluator.id = ise.evaluated_by', 'left')
            ->where('ise.ims_supplier_id', $id)
            ->orderBy('ise.evaluation_date', 'desc')
            ->orderBy('ise.id', 'desc')
            ->get()
            ->getResultArray();

        $evaluationIds = array_map(static fn (array $row): int => (int) $row['id'], $evaluationRows);
        $evaluationDetails = [];

        if ($evaluationIds !== []) {
            $detailRows = $db->table('ims_supplier_evaluation_detail ised')
                ->select('ised.*, icd.check_item, icd.weight, detail_evaluator.username as evaluated_by_name')
                ->join('ims_checklist_detail icd', 'icd.id = ised.ims_checklist_detail_id', 'left')
                ->join('users detail_evaluator', 'detail_evaluator.id = ised.evaluated_by', 'left')
                ->whereIn('ised.ims_supplier_evaluation_id', $evaluationIds)
                ->orderBy('ised.id', 'asc')
                ->get()
                ->getResultArray();

            foreach ($detailRows as $detailRow) {
                $evaluationDetails[(int) $detailRow['ims_supplier_evaluation_id']][] = $detailRow;
            }
        }

        foreach ($evaluationRows as &$evaluationRow) {
            $evaluationRow['details'] = $evaluationDetails[(int) $evaluationRow['id']] ?? [];
        }
        unset($evaluationRow);

        try {
            $documentRows = $db->table('ims_supplier_upload isu')
                ->select('isu.*, uploader.username as uploaded_by_name')
                ->join('users uploader', 'uploader.id = isu.uploaded_by', 'left')
                ->where('isu.ims_supplier_id', $id)
                ->orderBy('isu.created_at', 'desc')
                ->orderBy('isu.id', 'desc')
                ->get()
                ->getResultArray();
        } catch (Throwable) {
            $documentRows = [];
        }

        return $this->render('Modules\IMS\Views\supplier\supplier_detail', [
            'pageTitle' => lang('IMS.suppliers.detailTitle'),
            'pageSubtitle' => lang('IMS.suppliers.detailSubtitle'),
            'supplier' => $supplier,
            'brandRows' => $brandRows,
            'brands' => model(BrandModel::class)->orderBy('brand_name', 'asc')->findAll(),
            'mappedBrandIds' => $mappedBrandIds,
            'purchaseOrders' => $purchaseOrders,
            'stockLots' => $stockLots,
            'documentRows' => $documentRows,
            'checklists' => model(ChecklistModel::class)->orderBy('checklist_name', 'asc')->findAll(),
            'evaluationRows' => $evaluationRows,
            'validation' => session('errors') ?? [],
            'modalState' => session('ims_supplier_detail_modal'),
            'activeDetailTab' => (string) ($this->request->getGet('tab') ?: session('ims_supplier_detail_tab') ?: 'brands'),
        ]);
    }

    public function create()
    {
        if (($v = $this->validateForm()) !== true) {
            return $v;
        }

        model(SupplierModel::class)->insert($this->buildPayload());

        return $this->redirectIndex('Supplier created successfully.');
    }

    public function update(int $id)
    {
        $supplier = model(SupplierModel::class)->find($id);

        if ($supplier === null) {
            return $this->redirectIndex('Supplier record was not found.', null, 'danger');
        }

        if (($v = $this->validateForm($id)) !== true) {
            return $v;
        }

        model(SupplierModel::class)->update($id, $this->buildPayload($supplier));

        return $this->redirectIndex('Supplier updated successfully.');
    }

    public function delete(int $id)
    {
        try {
            model(SupplierModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectIndex('Supplier cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectIndex('Supplier deleted successfully.');
    }

    public function syncBrands(int $id)
    {
        if (model(SupplierModel::class)->find($id) === null) {
            return $this->redirectIndex('Supplier record was not found.', null, 'danger');
        }

        $brandIds = $this->request->getPost('brand_ids');
        $brandIds = is_array($brandIds) ? array_values(array_unique(array_map('intval', $brandIds))) : [];
        $brandIds = array_values(array_filter($brandIds, static fn (int $v): bool => $v > 0));

        if ($brandIds !== []) {
            if (model(BrandModel::class)->whereIn('id', $brandIds)->countAllResults() !== count($brandIds)) {
                return $this->redirectDetail($id, 'One or more selected brands are invalid.', 'supplier-brand-modal', 'danger');
            }
        }

        $model = model(SupplierBrandModel::class);
        $model->where('supplier_id', $id)->delete();

        if ($brandIds !== []) {
            $ts   = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($brandIds as $brandId) {
                $rows[] = ['supplier_id' => $id, 'brand_id' => $brandId, 'created_at' => $ts, 'updated_at' => $ts];
            }
            $model->insertBatch($rows);
        }

        return $this->redirectDetail($id, 'Supplier brand mapping updated successfully.');
    }

    public function createEvaluation(int $id)
    {
        if (model(SupplierModel::class)->find($id) === null) {
            return $this->redirectIndex('Supplier record was not found.', null, 'danger');
        }

        $rules = [
            'evaluation_date' => 'required|valid_date[Y-m-d]',
            'ims_checklist_id' => 'required|integer|is_not_unique[ims_checklist.id]',
            'comment' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectDetail($id, null, 'supplier-evaluation-modal', 'danger', [], 'evaluations');
        }

        $checklistId = (int) $this->request->getPost('ims_checklist_id');
        $userId = lab_core_current_user()?->id;
        $db = Database::connect();

        $db->transStart();

        $evaluationId = model(SupplierEvaluationModel::class)->insert([
            'evaluation_date' => trim((string) $this->request->getPost('evaluation_date')),
            'ims_supplier_id' => $id,
            'ims_checklist_id' => $checklistId,
            'evaluated_by' => $userId,
            'total_rate' => '0.00',
            'comment' => trim((string) $this->request->getPost('comment')) ?: null,
            'locked' => 0,
        ], true);

        $checklistDetails = model(ChecklistDetailModel::class)
            ->where('ims_checklist_id', $checklistId)
            ->orderBy('id', 'asc')
            ->findAll();

        if ($checklistDetails !== []) {
            $detailRows = [];

            foreach ($checklistDetails as $detail) {
                $detailRows[] = [
                    'ims_supplier_evaluation_id' => (int) $evaluationId,
                    'ims_checklist_detail_id' => (int) $detail['id'],
                    'actual_rate' => '0.00',
                    'evaluated_by' => $userId,
                ];
            }

            model(SupplierEvaluationDetailModel::class)->insertBatch($detailRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectDetail($id, 'Unable to create supplier evaluation. Please try again.', 'supplier-evaluation-modal', 'danger', [], 'evaluations');
        }

        return $this->redirectDetail($id, 'Supplier evaluation created successfully.', null, 'success', [], 'evaluations');
    }

    public function deleteEvaluation(int $id, int $evaluationId)
    {
        $model = model(SupplierEvaluationModel::class);
        $evaluation = $model->find($evaluationId);

        if ($evaluation === null || (int) $evaluation['ims_supplier_id'] !== $id) {
            return $this->redirectDetail($id, 'Supplier evaluation was not found.', null, 'danger', [], 'evaluations');
        }

        if ((int) ($evaluation['locked'] ?? 0) === 1) {
            return $this->redirectDetail($id, 'Locked supplier evaluation cannot be deleted.', null, 'danger', [], 'evaluations');
        }

        try {
            $model->delete($evaluationId);
        } catch (Throwable) {
            return $this->redirectDetail($id, 'Supplier evaluation cannot be deleted because it is already referenced by IMS records.', null, 'danger', [], 'evaluations');
        }

        return $this->redirectDetail($id, 'Supplier evaluation deleted successfully.', null, 'success', [], 'evaluations');
    }

    public function confirmEvaluation(int $id, int $evaluationId)
    {
        $evaluationModel = model(SupplierEvaluationModel::class);
        $evaluation = $evaluationModel->find($evaluationId);

        if ($evaluation === null || (int) $evaluation['ims_supplier_id'] !== $id) {
            return $this->redirectDetail($id, 'Supplier evaluation was not found.', null, 'danger', [], 'evaluations');
        }

        if ((int) ($evaluation['locked'] ?? 0) === 1) {
            return $this->redirectDetail($id, 'Supplier evaluation is already locked.', null, 'danger', [], 'evaluations');
        }

        $submittedRates = $this->request->getPost('actual_rate');
        if (! is_array($submittedRates) || $submittedRates === []) {
            return $this->redirectDetail($id, 'No evaluation detail rates were submitted.', null, 'danger', [], 'evaluations');
        }

        $db = Database::connect();
        $detailRows = $db->table('ims_supplier_evaluation_detail ised')
            ->select('ised.id, icd.weight')
            ->join('ims_checklist_detail icd', 'icd.id = ised.ims_checklist_detail_id', 'left')
            ->where('ised.ims_supplier_evaluation_id', $evaluationId)
            ->get()
            ->getResultArray();

        if ($detailRows === []) {
            return $this->redirectDetail($id, 'Supplier evaluation has no detail rows.', null, 'danger', [], 'evaluations');
        }

        $updates = [];
        $totalScore = 0.0;

        foreach ($detailRows as $detail) {
            $detailId = (int) $detail['id'];
            $actualRate = (float) ($submittedRates[$detailId] ?? 0);

            if ($actualRate < 1 || $actualRate > 10) {
                return $this->redirectDetail($id, 'Actual rate must be between 1 and 10.', null, 'danger', [], 'evaluations');
            }

            $weight = (float) ($detail['weight'] ?? 0);
            $totalScore += ($actualRate * $weight) / 10;
            $updates[] = [
                'id' => $detailId,
                'actual_rate' => $actualRate,
                'evaluated_by' => lab_core_current_user()?->id,
            ];
        }

        $classification = $db->table('ims_checklist_classification')
            ->where('ims_checklist_id', (int) $evaluation['ims_checklist_id'])
            ->where('rate_min <', $totalScore)
            ->where('rate_max >=', $totalScore)
            ->orderBy('rate_min', 'desc')
            ->get()
            ->getRowArray();

        if ($classification === null) {
            $classification = $db->table('ims_checklist_classification')
                ->where('ims_checklist_id', (int) $evaluation['ims_checklist_id'])
                ->where('rate_min <=', $totalScore)
                ->where('rate_max >=', $totalScore)
                ->orderBy('rate_min', 'desc')
                ->get()
                ->getRowArray();
        }

        if ($classification === null) {
            return $this->redirectDetail($id, 'No supplier classification matches the total score.', null, 'danger', [], 'evaluations');
        }

        $db->transStart();

        model(SupplierEvaluationDetailModel::class)->updateBatch($updates, 'id');
        $evaluationModel->update($evaluationId, [
            'total_rate' => round($totalScore, 2),
            'locked' => 1,
        ]);
        model(SupplierModel::class)->update($id, [
            'approved_status' => (int) $classification['ims_supplier_classification_id'],
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectDetail($id, 'Unable to confirm supplier evaluation.', null, 'danger', [], 'evaluations');
        }

        return $this->redirectDetail($id, 'Supplier evaluation confirmed successfully.', null, 'success', [], 'evaluations');
    }

    public function uploadDocument(int $id)
    {
        if (model(SupplierModel::class)->find($id) === null) {
            return $this->redirectIndex('Supplier record was not found.', null, 'danger');
        }

        $file = $this->request->getFile('attachment_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectDetail($id, null, 'supplier-document-upload-modal', 'danger', [
                'attachment_file' => 'Please choose a file to upload.',
            ], 'documents');
        }

        if (! $file->isValid()) {
            return $this->redirectDetail($id, null, 'supplier-document-upload-modal', 'danger', [
                'attachment_file' => 'File upload failed. Please try again.',
            ], 'documents');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectDetail($id, null, 'supplier-document-upload-modal', 'danger', [
                'attachment_file' => 'Attachment size must not exceed 10MB.',
            ], 'documents');
        }

        $attachmentType = trim((string) $this->request->getPost('attachment_type')) ?: 'document';
        $destination = WRITEPATH . 'uploads/ims/supplier/' . $id;

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $originalName = $file->getClientName();
        $newName = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(SupplierUploadModel::class)->insert([
            'ims_supplier_id' => $id,
            'attachment_type' => $attachmentType,
            'file_name' => $originalName,
            'file_path' => 'uploads/ims/supplier/' . $id . '/' . $newName,
            'uploaded_by' => lab_core_current_user()?->id,
        ]);

        return $this->redirectDetail($id, 'Supplier document uploaded successfully.', null, 'success', [], 'documents');
    }

    public function updateDocument(int $id, int $documentId)
    {
        $model = model(SupplierUploadModel::class);
        $document = $model->find($documentId);

        if ($document === null || (int) $document['ims_supplier_id'] !== $id) {
            return $this->redirectDetail($id, 'Supplier document was not found.', null, 'danger', [], 'documents');
        }

        $attachmentType = trim((string) $this->request->getPost('attachment_type')) ?: 'document';
        $model->update($documentId, ['attachment_type' => $attachmentType]);

        return $this->redirectDetail($id, 'Supplier document updated successfully.', null, 'success', [], 'documents');
    }

    public function deleteDocument(int $id, int $documentId)
    {
        $model = model(SupplierUploadModel::class);
        $document = $model->find($documentId);

        if ($document === null || (int) $document['ims_supplier_id'] !== $id) {
            return $this->redirectDetail($id, 'Supplier document was not found.', null, 'danger', [], 'documents');
        }

        try {
            $model->delete($documentId);
            $this->deleteSupplierDocumentFile($document['file_path'] ?? null);
        } catch (Throwable) {
            return $this->redirectDetail($id, 'Supplier document cannot be deleted.', null, 'danger', [], 'documents');
        }

        return $this->redirectDetail($id, 'Supplier document deleted successfully.', null, 'success', [], 'documents');
    }

    public function serveDocument(int $id, int $documentId)
    {
        $document = model(SupplierUploadModel::class)->find($documentId);

        if ($document === null || (int) $document['ims_supplier_id'] !== $id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $fullPath = WRITEPATH . ltrim((string) $document['file_path'], '/\\');

        if (! is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . rawurlencode((string) $document['file_name']) . '"')
            ->setHeader('Content-Length', (string) filesize($fullPath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody((string) file_get_contents($fullPath));
    }

    public function downloadTemplate()
    {
        $rows   = model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            return $this->redirectIndex('Unable to generate supplier template right now.', null, 'danger');
        }

        $headers = ['supplier_name', 'supplier_code', 'address', 'vat_code', 'phone', 'email', 'contact_name', 'approved_status', 'is_active'];
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['supplier_name']    ?? '',
                $row['supplier_code']    ?? '',
                $row['address']          ?? '',
                $row['vat_code']         ?? '',
                $row['phone']            ?? '',
                $row['email']            ?? '',
                $row['contact_name']     ?? '',
                $this->supplierClassificationName($row['approved_status'] ?? null),
                (int) ($row['is_active'] ?? 0),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="ims-suppliers-template.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    public function import()
    {
        $file = $this->request->getFile('import_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectIndex(null, 'supplier-import-modal', 'danger', ['import_file' => 'Please choose a CSV file to import.']);
        }

        if (! $file->isValid()) {
            return $this->redirectIndex(null, 'supplier-import-modal', 'danger', ['import_file' => 'Supplier import file upload failed.']);
        }

        if (strtolower((string) $file->getExtension()) !== 'csv') {
            return $this->redirectIndex(null, 'supplier-import-modal', 'danger', ['import_file' => 'Import file must be a CSV exported from the supplier template.']);
        }

        $handle = fopen($file->getTempName(), 'rb');

        if ($handle === false) {
            return $this->redirectIndex(null, 'supplier-import-modal', 'danger', ['import_file' => 'Unable to read the uploaded CSV file.']);
        }

        $expectedHeaders = ['supplier_name', 'supplier_code', 'address', 'vat_code', 'phone', 'email', 'contact_name', 'approved_status', 'is_active'];

        $headers = fgetcsv($handle);
        if (is_array($headers) && isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        }

        if ($headers !== $expectedHeaders) {
            fclose($handle);

            return $this->redirectIndex(null, 'supplier-import-modal', 'danger', [
                'import_file' => 'CSV headers do not match the supplier template. Please download the latest template and try again.',
            ]);
        }

        $supplierModel   = model(SupplierModel::class);
        $rowNumber       = 1;
        $created         = 0;
        $updated         = 0;
        $classificationRows = db_connect()
            ->table('ims_supplier_classification')
            ->select('id, class_name')
            ->get()
            ->getResultArray();
        $classificationMap = [];
        foreach ($classificationRows as $classification) {
            $classificationMap[strtolower((string) $classification['class_name'])] = (int) $classification['id'];
        }
        $legacyStatusMap = [
            'approved' => $classificationMap['approved'] ?? null,
            'pending' => $classificationMap['conditional approval'] ?? null,
            'rejected' => $classificationMap['disqualified'] ?? null,
            'suspended' => $classificationMap['improvement required'] ?? null,
        ];

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($data === [null] || $data === false) {
                continue;
            }

            $row     = array_map(static fn ($v): string => trim((string) $v), array_pad($data, count($expectedHeaders), ''));
            $payload = array_combine($expectedHeaders, $row);

            if ($payload === false) {
                fclose($handle);

                return $this->redirectIndex(null, 'supplier-import-modal', 'danger', [
                    'import_file' => 'Invalid CSV structure detected at row ' . $rowNumber . '.',
                ]);
            }

            if (implode('', $payload) === '') {
                continue;
            }

            if ($payload['supplier_name'] === '') {
                fclose($handle);

                return $this->redirectIndex(null, 'supplier-import-modal', 'danger', [
                    'import_file' => 'Supplier name is required at row ' . $rowNumber . '.',
                ]);
            }

            if ($payload['email'] !== '' && filter_var($payload['email'], FILTER_VALIDATE_EMAIL) === false) {
                fclose($handle);

                return $this->redirectIndex(null, 'supplier-import-modal', 'danger', [
                    'import_file' => 'Invalid email format at row ' . $rowNumber . '.',
                ]);
            }

            $statusInput = strtolower($payload['approved_status'] ?: 'conditional approval');
            $statusId = $classificationMap[$statusInput] ?? $legacyStatusMap[$statusInput] ?? null;

            if ($statusId === null) {
                fclose($handle);

                return $this->redirectIndex(null, 'supplier-import-modal', 'danger', [
                    'import_file' => 'Invalid approved_status at row ' . $rowNumber . '. Use a supplier classification name.',
                ]);
            }

            $isActive = in_array(strtolower($payload['is_active']), ['1', 'true', 'yes', 'y'], true) ? 1 : 0;

            $existing = null;
            if ($payload['supplier_code'] !== '') {
                $existing = $supplierModel->where('supplier_code', $payload['supplier_code'])->first();
            }
            if ($existing === null) {
                $existing = $supplierModel->where('supplier_name', $payload['supplier_name'])->first();
            }

            $saveData = [
                'supplier_name'   => $payload['supplier_name'],
                'supplier_code'   => $existing !== null
                    ? ($existing['supplier_code'] ?: $this->generateUniqueCode())
                    : $this->generateUniqueCode(),
                'address'         => $payload['address']      !== '' ? $payload['address']      : null,
                'vat_code'        => $payload['vat_code']     !== '' ? $payload['vat_code']     : null,
                'phone'           => $payload['phone']        !== '' ? $payload['phone']        : null,
                'email'           => $payload['email']        !== '' ? $payload['email']        : null,
                'contact_name'    => $payload['contact_name'] !== '' ? $payload['contact_name'] : null,
                'approved_status' => $statusId,
                'is_active'       => $isActive,
            ];

            if ($existing !== null) {
                $supplierModel->update((int) $existing['id'], $saveData);
                $updated++;
                continue;
            }

            $supplierModel->insert($saveData);
            $created++;
        }

        fclose($handle);

        return $this->redirectIndex('Supplier import completed. Created: ' . $created . ', Updated: ' . $updated . '.');
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function resolvePaginationState(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);

        return ['page' => $page, 'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10];
    }

    private function redirectIndex(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [])
    {
        $state    = $this->resolvePaginationState();
        $redirect = redirect()->to(site_url('ims/suppliers?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_suppliers_modal', $modal);
        }

        return $redirect;
    }

    private function redirectDetail(int $supplierId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], string $tab = 'brands')
    {
        $redirect = redirect()->to(site_url('ims/suppliers/' . $supplierId . '?' . http_build_query(['tab' => $tab])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_supplier_detail_modal', $modal);
        }

        $redirect = $redirect->with('ims_supplier_detail_tab', $tab);

        return $redirect;
    }

    private function validateForm(?int $ignoreId = null)
    {
        $rules = [
            'supplier_name'   => 'required|min_length[2]|max_length[180]',
            'address'         => 'permit_empty|max_length[255]',
            'vat_code'        => 'permit_empty|max_length[50]',
            'phone'           => 'permit_empty|max_length[50]',
            'email'           => 'permit_empty|valid_email|max_length[150]',
            'contact_name'    => 'permit_empty|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectIndex(null, 'supplier-form-modal');
        }

        $supplierName = trim((string) $this->request->getPost('supplier_name'));
        $nameQuery    = model(SupplierModel::class)->where('supplier_name', $supplierName);

        if ($ignoreId !== null) {
            $nameQuery = $nameQuery->where('id !=', $ignoreId);
        }

        if ($nameQuery->first() !== null) {
            return $this->redirectIndex(null, 'supplier-form-modal', 'danger', [
                'supplier_name' => 'Supplier name already exists.',
            ]);
        }

        return true;
    }

    private function buildPayload(?array $existing = null): array
    {
        return [
            'supplier_name'   => trim((string) $this->request->getPost('supplier_name')),
            'supplier_code'   => $existing['supplier_code'] ?? $this->generateUniqueCode(),
            'address'         => trim((string) $this->request->getPost('address'))       ?: null,
            'vat_code'        => trim((string) $this->request->getPost('vat_code'))      ?: null,
            'phone'           => trim((string) $this->request->getPost('phone'))         ?: null,
            'email'           => trim((string) $this->request->getPost('email'))         ?: null,
            'contact_name'    => trim((string) $this->request->getPost('contact_name'))  ?: null,
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
        ];
    }

    private function generateUniqueCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $maxIndex = strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, $maxIndex)];
            }
        } while (model(SupplierModel::class)->where('supplier_code', $code)->first() !== null);

        return $code;
    }

    private function deleteSupplierDocumentFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $fullPath = WRITEPATH . ltrim($path, '/\\');

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function supplierClassificationName(int|string|null $classificationId): string
    {
        static $classifications = null;

        if ($classifications === null) {
            $rows = db_connect()
                ->table('ims_supplier_classification')
                ->select('id, class_name')
                ->get()
                ->getResultArray();
            $classifications = [];

            foreach ($rows as $row) {
                $classifications[(int) $row['id']] = (string) $row['class_name'];
            }
        }

        return $classifications[(int) $classificationId] ?? '';
    }

    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage    = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $perPage;
        $from       = $total === 0 ? 0 : $offset + 1;
        $to         = min($offset + $perPage, $total);
        $start      = max(1, $page - 2);
        $end        = min($totalPages, $page + 2);

        return compact('page', 'perPage', 'total', 'totalPages', 'offset', 'from', 'to') + ['pages' => range($start, $end)];
    }
}
