<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;
use Modules\IMS\Models\FormulationAttachmentModel;
use Modules\IMS\Models\FormulationComponentModel;
use Modules\IMS\Models\FormulationModel;
use Modules\IMS\Models\StockLotModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\UnitModel;

class FormulationController extends BaseImsController
{
    public function index(): string
    {
        $state  = $this->resolvePaginationState();
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $db     = Database::connect();

        $countBuilder = $db->table('ims_formulation fm');
        if ($search !== '') {
            $countBuilder->groupStart()
                ->like('fm.name', $search)
                ->orLike('fm.formulation_lot', $search)
                ->orLike('fm.formulation_type', $search)
                ->groupEnd();
        }
        $total  = (int) $countBuilder->countAllResults();
        $fPager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rowsBuilder = $db->table('ims_formulation fm')
            ->select('fm.*, iu.unit_name, cu.unit_name as concentration_unit_name, loc.name as storage_location_name, pb.username as prepared_by_name, ab.username as approved_by_name, rb.username as retested_by_name')
            ->join('ims_units iu', 'iu.id = fm.unit_id', 'left')
            ->join('ims_units cu', 'cu.id = fm.concentration_unit_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = fm.storage_location_id', 'left')
            ->join('users pb', 'pb.id = fm.prepared_by', 'left')
            ->join('users ab', 'ab.id = fm.approved_by', 'left')
            ->join('users rb', 'rb.id = fm.retested_by', 'left');

        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like('fm.name', $search)
                ->orLike('fm.formulation_lot', $search)
                ->orLike('fm.formulation_type', $search)
                ->groupEnd();
        }

        $rows = $rowsBuilder
            ->orderBy('fm.id', 'desc')
            ->limit($fPager['perPage'], $fPager['offset'])
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\formulation\formulations', [
            'pageTitle'     => lang('IMS.formulations.indexTitle'),
            'pageSubtitle'  => lang('IMS.formulations.indexSubtitle'),
            'rows'          => $rows,
            'units'         => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'users'         => $db->table('users')->select('id, username')->orderBy('username', 'asc')->get()->getResultArray(),
            'locations'     => model(StorageLocationModel::class)->where('is_active', 1)->orderBy('name', 'asc')->findAll(),
            'fPager'        => $fPager,
            'fQuery'        => ['page' => $fPager['page'], 'per_page' => $fPager['perPage'], 'q' => $search],
            'validation'    => session('errors') ?? [],
            'modalState'    => session('ims_formulations_modal'),
            'currentUserId' => lab_core_current_user()?->id,
        ]);
    }

    public function form(?int $id = null): string
    {
        $formulation = [];

        if ($id !== null) {
            $formulation = model(FormulationModel::class)->find($id);

            if ($formulation === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('IMS.formulations.messages.notFound'));
            }
        }

        $options = $this->formOptions();

        return $this->render('Modules\IMS\Views\formulation\formulation_form', [
            'pageTitle' => $id === null ? lang('IMS.formulations.actions.add') : lang('IMS.formulations.actions.edit'),
            'pageSubtitle' => lang('IMS.formulations.form.subtitle'),
            'formulation' => $formulation,
            'units' => $options['units'],
            'users' => $options['users'],
            'locations' => $options['locations'],
            'validation' => session('errors') ?? [],
            'currentUserId' => lab_core_current_user()?->id,
        ]);
    }

    public function detail(int $id): string
    {
        $db = Database::connect();

        $fm = $this->findFormulationForDisplay($id);

        if ($fm === null) {
            return redirect()->to(site_url('ims/formulations'))->with('message', lang('IMS.formulations.messages.notFound'))->with('message_type', 'danger');
        }

        $components = $db->table('ims_formulation_component ifc')
            ->select('ifc.*, isl.internal_lot_no, isl.lot_no, iim.item_name, iim.item_code, sfm.formulation_lot as source_fm_lot, sfm.name as source_fm_name, uu.unit_name as used_unit_name, cu2.unit_name as concentration_unit_name')
            ->join('ims_stock_lots isl', 'isl.id = ifc.source_lot_id', 'left')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_formulation sfm', 'sfm.id = ifc.source_formulation_id', 'left')
            ->join('ims_units uu', 'uu.id = ifc.used_unit_id', 'left')
            ->join('ims_units cu2', 'cu2.id = ifc.concentration_unit_id', 'left')
            ->where('ifc.formulation_id', $id)
            ->orderBy('ifc.sort_order', 'asc')
            ->orderBy('ifc.id', 'asc')
            ->get()->getResultArray();

        foreach ($components as &$cr) {
            $cr['source_label'] = $cr['source_type'] === 'stock_lot'
                ? ($cr['internal_lot_no'] ?: ($cr['lot_no'] ?: '-'))
                : ($cr['source_fm_lot'] ?: '-');
            $cr['source_sub'] = $cr['source_type'] === 'stock_lot'
                ? ($cr['item_name'] ?: '-')
                : ($cr['source_fm_name'] ?: '-');
        }
        unset($cr);

        $attachments = model(FormulationAttachmentModel::class)
            ->where('formulation_id', $id)
            ->orderBy('uploaded_at', 'desc')
            ->findAll();

        $allStockLots = $db->table('ims_stock_lots isl')
            ->select('isl.id, isl.internal_lot_no, isl.lot_no, isl.current_qty, iim.item_code, iim.item_name, iu.unit_name, loc.name as storage_location_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = isl.storage_location_id', 'left')
            ->orderBy('iim.item_name', 'asc')
            ->get()->getResultArray();

        $allFormulations = $db->table('ims_formulation fm2')
            ->select('fm2.id, fm2.formulation_lot, fm2.name, fm2.formulation_type, fm2.qty, fm2.status, iu2.unit_name, fm2.concentration, cu3.unit_name as concentration_unit_name')
            ->join('ims_units iu2', 'iu2.id = fm2.unit_id', 'left')
            ->join('ims_units cu3', 'cu3.id = fm2.concentration_unit_id', 'left')
            ->orderBy('fm2.id', 'desc')
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\formulation\formulation_detail', [
            'pageTitle'       => $fm['formulation_lot'] . ' - ' . $fm['name'],
            'pageSubtitle'    => lang('IMS.formulations.detail.subtitle'),
            'fm'              => $fm,
            'components'      => $components,
            'attachments'     => $attachments,
            'allStockLots'    => $allStockLots,
            'allFormulations' => $allFormulations,
            'units'           => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'validation'      => session('errors') ?? [],
            'modalState'      => session('ims_formulation_detail_modal'),
        ]);
    }

    public function label(int $id)
    {
        return $this->renderLabelPdf($id, 'Modules\IMS\Views\formulation\formulation-label-pdf', [0, 0, 141.73, 85.04], 'label');
    }

    public function label3x2(int $id)
    {
        return $this->renderLabelPdf($id, 'Modules\IMS\Views\formulation\label-3x2-pdf', [0, 0, 85.04, 56.69], 'label-3x2');
    }

    public function create()
    {
        if (($v = $this->validateForm()) !== true) {
            return $v;
        }

        model(FormulationModel::class)->insert($this->buildPayload(true));

        return $this->redirectIndex(lang('IMS.formulations.messages.created'));
    }

    public function update(int $id)
    {
        if (model(FormulationModel::class)->find($id) === null) {
            return $this->redirectIndex(lang('IMS.formulations.messages.notFound'), null, 'danger');
        }

        if (($v = $this->validateForm($id)) !== true) {
            return $v;
        }

        model(FormulationModel::class)->update($id, $this->buildPayload(false));

        return $this->redirectIndex(lang('IMS.formulations.messages.updated'));
    }

    public function delete(int $id)
    {
        try {
            model(FormulationModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectIndex(lang('IMS.formulations.messages.deleteBlocked'), null, 'danger');
        }

        return $this->redirectIndex(lang('IMS.formulations.messages.deleted'));
    }

    public function uploadAttachment(int $fId)
    {
        if (model(FormulationModel::class)->find($fId) === null) {
            return $this->redirectDetail($fId, lang('IMS.formulations.messages.notFound'), null, 'danger');
        }

        $file = $this->request->getFile('attachment_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectDetail($fId, null, 'formulation-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.formulations.validation.chooseFile'),
            ]);
        }

        if (! $file->isValid()) {
            return $this->redirectDetail($fId, null, 'formulation-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.formulations.validation.uploadFailed'),
            ]);
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectDetail($fId, null, 'formulation-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.formulations.validation.maxFileSize'),
            ]);
        }

        $destination = WRITEPATH . 'uploads/ims/formulation/' . $fId;

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(FormulationAttachmentModel::class)->insert([
            'formulation_id'  => $fId,
            'attachment_type' => trim((string) $this->request->getPost('attachment_type')) ?: 'document',
            'file_name'       => $originalName,
            'file_path'       => 'uploads/ims/formulation/' . $fId . '/' . $newName,
            'remarks'         => trim((string) $this->request->getPost('remarks')) ?: null,
            'uploaded_by'     => lab_core_current_user()?->id,
            'uploaded_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectDetail($fId, lang('IMS.formulations.messages.attachmentUploaded'));
    }

    public function serveAttachment(int $fId, int $attachmentId)
    {
        $att = model(FormulationAttachmentModel::class)->find($attachmentId);

        if ($att === null || (int) $att['formulation_id'] !== $fId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $fullPath = WRITEPATH . ltrim((string) $att['file_path'], '/\\');

        if (! is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . rawurlencode((string) $att['file_name']) . '"')
            ->setHeader('Content-Length', (string) filesize($fullPath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody((string) file_get_contents($fullPath));
    }

    public function addComponent(int $fId)
    {
        if (model(FormulationModel::class)->find($fId) === null) {
            return $this->redirectComponent($fId, lang('IMS.formulations.messages.notFound'), 'danger');
        }

        $sourceType = $this->request->getPost('source_type');

        if (! in_array($sourceType, ['stock_lot', 'formulation'], true)) {
            return $this->redirectComponent($fId, lang('IMS.formulations.validation.invalidSourceType'), 'danger');
        }

        $sourceData    = ['source_type' => $sourceType, 'source_lot_id' => null, 'source_formulation_id' => null];
        $defaultUnitId = null;

        if ($sourceType === 'stock_lot') {
            $lotId = $this->nullableInt('source_lot_id');

            if ($lotId === null) {
                return $this->redirectComponent($fId, lang('IMS.formulations.validation.stockLotRequired'), 'danger');
            }

            $lot = model(StockLotModel::class)->find($lotId);

            if ($lot === null) {
                return $this->redirectComponent($fId, lang('IMS.formulations.validation.stockLotNotFound'), 'danger');
            }

            $sourceData['source_lot_id'] = $lotId;
            $defaultUnitId               = $lot['current_unit_id'];
        } else {
            $fmId = $this->nullableInt('source_formulation_id');

            if ($fmId === null || $fmId === $fId) {
                return $this->redirectComponent($fId, lang('IMS.formulations.validation.invalidSourceFormulation'), 'danger');
            }

            $sourceFm = model(FormulationModel::class)->find($fmId);

            if ($sourceFm === null) {
                return $this->redirectComponent($fId, lang('IMS.formulations.validation.sourceFormulationNotFound'), 'danger');
            }

            $sourceData['source_formulation_id'] = $fmId;
            $defaultUnitId                       = $sourceFm['unit_id'];
        }

        if ($defaultUnitId === null) {
            return $this->redirectComponent($fId, lang('IMS.formulations.validation.sourceNoUnit'), 'danger');
        }

        model(FormulationComponentModel::class)->insert([
            ...$sourceData,
            'formulation_id' => $fId,
            'used_qty'       => 0,
            'used_unit_id'   => $defaultUnitId,
            'is_solvent'     => 0,
            'is_markup'      => 0,
            'sort_order'     => 0,
        ]);

        return $this->redirectComponent($fId);
    }

    public function batchUpdateComponents(int $fId)
    {
        if (model(FormulationModel::class)->find($fId) === null) {
            return $this->redirectComponent($fId, lang('IMS.formulations.messages.notFound'), 'danger');
        }

        $details = $this->request->getPost('components');

        if (is_array($details)) {
            $model = model(FormulationComponentModel::class);

            foreach ($details as $i => $data) {
                $compId = (int) ($data['id'] ?? 0);

                if ($compId <= 0) {
                    continue;
                }

                $record = $model->find($compId);

                if ($record === null || (int) $record['formulation_id'] !== $fId) {
                    continue;
                }

                $usedUnitId = ($data['used_unit_id'] ?? '') !== '' ? (int) $data['used_unit_id'] : null;

                if ($usedUnitId === null) {
                    continue;
                }

                $model->update($compId, [
                    'used_qty'              => (float) ($data['used_qty'] ?? 0),
                    'used_unit_id'          => $usedUnitId,
                    'concentration'         => ($data['concentration'] ?? '') !== '' ? (float) $data['concentration'] : null,
                    'concentration_unit_id' => ($data['concentration_unit_id'] ?? '') !== '' ? (int) $data['concentration_unit_id'] : null,
                    'is_solvent'            => (int) (bool) ($data['is_solvent'] ?? 0),
                    'is_markup'             => (int) (bool) ($data['is_markup'] ?? 0),
                    'sort_order'            => $i,
                    'remark'                => trim((string) ($data['remark'] ?? '')) ?: null,
                ]);
            }
        }

        return $this->redirectComponent($fId, lang('IMS.formulations.messages.componentsUpdated'));
    }

    public function deleteComponent(int $fId, int $componentId)
    {
        $comp = model(FormulationComponentModel::class)->find($componentId);

        if ($comp === null || (int) $comp['formulation_id'] !== $fId) {
            return $this->redirectComponent($fId, lang('IMS.formulations.validation.componentNotFound'), 'danger');
        }

        model(FormulationComponentModel::class)->delete($componentId);

        return $this->redirectComponent($fId);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function resolvePaginationState(): array
    {
        $page    = (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);

        return [
            'page'    => max(1, $page),
            'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10,
        ];
    }

    private function redirectIndex(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $fId = null)
    {
        $state    = $this->resolvePaginationState();
        $redirect = redirect()->to(site_url('ims/formulations?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_formulations_modal', ['modal' => $modal, 'f_id' => $fId]);
        }

        return $redirect;
    }

    private function redirectDetail(int $fId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [])
    {
        $redirect = redirect()->to(site_url('ims/formulations/' . $fId))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_formulation_detail_modal', ['modal' => $modal, 'f_id' => $fId]);
        }

        return $redirect;
    }

    private function redirectComponent(int $fId, ?string $message = null, string $type = 'success')
    {
        $redirect = redirect()->to(site_url('ims/formulations/' . $fId))
            ->with('ims_formulation_detail_modal', ['modal' => 'formulation-component-modal', 'f_id' => $fId]);

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        return $redirect;
    }

    private function validateForm(?int $ignoreId = null)
    {
        $rules = [
            'name'                  => 'required|max_length[255]',
            'formulation_type'      => 'required|in_list[Reagent mixture,Stock Solution,Working solution,Calibrator]',
            'unit_id'               => 'required|integer|is_not_unique[ims_units.id]',
            'qty'                   => 'required|decimal',
            'status'                => 'required|in_list[active,approved,depleted,expired]',
            'storage_location_id'   => 'permit_empty|integer|is_not_unique[ims_storage_locations.id]',
            'concentration'         => 'permit_empty|decimal',
            'concentration_unit_id' => 'permit_empty|integer|is_not_unique[ims_units.id]',
            'prepared_date'         => 'permit_empty',
            'prepared_by'           => 'permit_empty|integer|is_not_unique[users.id]',
            'approved_date'         => 'permit_empty',
            'approved_by'           => 'permit_empty|integer|is_not_unique[users.id]',
            'expired_date'          => 'permit_empty',
            'retest_date'           => 'permit_empty',
            'retested_by'           => 'permit_empty|integer|is_not_unique[users.id]',
            'retest_result'         => 'permit_empty|max_length[100]',
            'retest_comment'        => 'permit_empty|max_length[255]',
            'next_retest_date'      => 'permit_empty',
            'description'           => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectForm($ignoreId, null, 'danger');
        }

        if ((float) $this->request->getPost('qty') <= 0) {
            return $this->redirectForm($ignoreId, [
                'qty' => 'Quantity must be greater than zero.',
            ], 'danger');
        }

        return true;
    }

    private function redirectForm(?int $id = null, ?array $errors = null, string $type = 'danger')
    {
        $url = $id === null ? site_url('ims/formulations/create') : site_url('ims/formulations/' . $id . '/edit');
        $redirect = redirect()->to($url)->withInput()->with('message_type', $type);

        return $redirect->with('errors', $errors ?? ($this->validator?->getErrors() ?? []));
    }

    private function buildPayload(bool $isCreate): array
    {
        $nd = fn (string $f): ?string => trim((string) $this->request->getPost($f)) ?: null;

        $payload = [
            'name'                  => trim((string) $this->request->getPost('name')),
            'formulation_type'      => trim((string) $this->request->getPost('formulation_type')),
            'unit_id'               => (int) $this->request->getPost('unit_id'),
            'qty'                   => (float) $this->request->getPost('qty'),
            'status'                => trim((string) $this->request->getPost('status')),
            'storage_location_id'   => $this->nullableInt('storage_location_id'),
            'concentration'         => ($c = trim((string) $this->request->getPost('concentration'))) !== '' ? (float) $c : null,
            'concentration_unit_id' => $this->nullableInt('concentration_unit_id'),
            'prepared_date'         => $nd('prepared_date'),
            'prepared_by'           => $this->nullableInt('prepared_by') ?? ($isCreate ? lab_core_current_user()?->id : null),
            'approved_date'         => $nd('approved_date'),
            'approved_by'           => $this->nullableInt('approved_by'),
            'expired_date'          => $nd('expired_date'),
            'retest_date'           => $nd('retest_date'),
            'retested_by'           => $this->nullableInt('retested_by'),
            'retest_result'         => trim((string) $this->request->getPost('retest_result')) ?: null,
            'retest_comment'        => trim((string) $this->request->getPost('retest_comment')) ?: null,
            'next_retest_date'      => $nd('next_retest_date'),
            'description'           => trim((string) $this->request->getPost('description')) ?: null,
        ];

        if ($isCreate) {
            $payload['formulation_lot'] = $this->generateUniqueLot();
        }

        return $payload;
    }

    private function generateUniqueLot(): string
    {
        $db     = Database::connect();
        $date   = date('Ymd');
        $prefix = 'FM-' . $date . '-';

        $existing = $db->table('ims_formulation')
            ->select('formulation_lot')
            ->like('formulation_lot', $prefix, 'after')
            ->get()->getResultArray();

        $max = 0;

        foreach ($existing as $row) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) $row['formulation_lot'], $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        do {
            $max++;
            $candidate = $prefix . str_pad((string) $max, 3, '0', STR_PAD_LEFT);
        } while ($db->table('ims_formulation')->where('formulation_lot', $candidate)->countAllResults() > 0);

        return $candidate;
    }

    private function findFormulationForDisplay(int $id): ?array
    {
        return Database::connect()->table('ims_formulation fm')
            ->select('fm.*, iu.unit_name, cu.unit_name as concentration_unit_name, loc.name as storage_location_name, pb.username as prepared_by_name, ab.username as approved_by_name, rb.username as retested_by_name')
            ->join('ims_units iu', 'iu.id = fm.unit_id', 'left')
            ->join('ims_units cu', 'cu.id = fm.concentration_unit_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = fm.storage_location_id', 'left')
            ->join('users pb', 'pb.id = fm.prepared_by', 'left')
            ->join('users ab', 'ab.id = fm.approved_by', 'left')
            ->join('users rb', 'rb.id = fm.retested_by', 'left')
            ->where('fm.id', $id)
            ->get()->getRowArray();
    }

    private function renderLabelPdf(int $id, string $view, array $paper, string $suffix)
    {
        $fm = $this->findFormulationForDisplay($id);

        if ($fm === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('IMS.formulations.messages.notFound'));
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, ['fm' => $fm]), 'UTF-8');
        $dompdf->setPaper($paper);
        $dompdf->render();

        $fileName = preg_replace('/[^A-Za-z0-9_\-]+/', '-', (string) ($fm['formulation_lot'] ?? 'formulation-label'));
        $fileName = trim((string) $fileName, '-') ?: 'formulation-label';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '-' . $suffix . '.pdf"')
            ->setBody($dompdf->output());
    }

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);

        return ($value === null || $value === '') ? null : (int) $value;
    }

    /**
     * @return array{units: array<int, array<string, mixed>>, users: array<int, array<string, mixed>>, locations: array<int, array<string, mixed>>}
     */
    private function formOptions(): array
    {
        $db = Database::connect();

        return [
            'units' => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'users' => $db->table('users')->select('id, username')->orderBy('username', 'asc')->get()->getResultArray(),
            'locations' => model(StorageLocationModel::class)->where('is_active', 1)->orderBy('name', 'asc')->findAll(),
        ];
    }

    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage    = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $perPage;
        $from       = $total === 0 ? 0 : $offset + 1;
        $to         = min($offset + $perPage, $total);

        $window = 2;
        $start  = max(1, $page - $window);
        $end    = min($totalPages, $page + $window);
        $pages  = range($start, $end);

        return compact('page', 'perPage', 'total', 'totalPages', 'offset', 'from', 'to', 'pages');
    }
}
