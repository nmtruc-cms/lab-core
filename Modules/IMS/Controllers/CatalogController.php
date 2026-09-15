<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;
use Modules\IMS\Models\ChecklistClassificationModel;
use Modules\IMS\Models\BrandModel;
use Modules\IMS\Models\CategoryModel;
use Modules\IMS\Models\ChecklistDetailModel;
use Modules\IMS\Models\ChecklistModel;
use Modules\IMS\Models\ItemMasterModel;
use Modules\IMS\Models\ItemRequestListModel;
use Modules\IMS\Models\RequestAttachmentModel;
use Modules\IMS\Models\RequestItemModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\SupplierClassificationModel;
use Modules\IMS\Models\SupplierModel;
use Modules\IMS\Models\UnitModel;

class CatalogController extends BaseImsController
{
    public function locations(): string
    {
        $db = Database::connect();

        $locations = $db->table('ims_storage_locations isl')
            ->select('isl.*, parent.name as parent_name, COUNT(lots.id) as lot_count')
            ->join('ims_storage_locations parent', 'parent.id = isl.parent_id', 'left')
            ->join('ims_stock_lots lots', 'lots.storage_location_id = isl.id', 'left')
            ->groupBy('isl.id')
            ->orderBy('isl.name', 'asc')
            ->get()->getResultArray();

        $lots = $db->table('ims_stock_lots isl')
            ->select('isl.id, isl.storage_location_id, isl.internal_lot_no, isl.lot_no, isl.current_qty, isl.expiry_date, isl.ownership_status, iim.item_name, iim.item_code, iu.unit_name, supplier.supplier_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_suppliers supplier', 'supplier.id = isl.supplier_id', 'left')
            ->where('isl.storage_location_id IS NOT NULL', null, false)
            ->orderBy('isl.id', 'desc')
            ->get()->getResultArray();

        $lotsByLocation = new \stdClass();

        foreach ($lots as $lot) {
            $key = (string) (int) $lot['storage_location_id'];
            if (! isset($lotsByLocation->$key)) {
                $lotsByLocation->$key = [];
            }
            $lotsByLocation->$key[] = $lot;
        }

        return $this->render('Modules\IMS\Views\location\locations', [
            'pageTitle'       => lang('IMS.locations.indexTitle'),
            'pageSubtitle'    => lang('IMS.locations.indexSubtitle'),
            'locations'       => $locations,
            'lotsByLocation'  => $lotsByLocation,
        ]);
    }

    public function createLocation()
    {
        if (($validationRedirect = $this->validateLocationForm()) !== true) {
            return $validationRedirect;
        }

        model(StorageLocationModel::class)->insert($this->locationPayload());

        return $this->redirectLocations('Storage location created successfully.');
    }

    public function updateLocation(int $id)
    {
        $model = model(StorageLocationModel::class);
        $location = $model->find($id);

        if ($location === null) {
            return $this->redirectLocations('Storage location was not found.', null, 'danger');
        }

        if (($validationRedirect = $this->validateLocationForm($id)) !== true) {
            return $validationRedirect;
        }

        $model->update($id, $this->locationPayload());

        return $this->redirectLocations('Storage location updated successfully.');
    }

    public function deleteLocation(int $id)
    {
        try {
            model(StorageLocationModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectLocations('Storage location cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectLocations('Storage location deleted successfully.');
    }

    public function requests(): string
    {
        $state  = $this->resolveRequestPaginationState();
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $db     = Database::connect();

        $countBuilder = $db->table('ims_item_request_list iirl');
        if ($search !== '') {
            $countBuilder->groupStart()
                ->like('iirl.request_name', $search)
                ->orLike('iirl.request_number', $search)
                ->orLike('iirl.status', $search)
                ->groupEnd();
        }
        $total  = (int) $countBuilder->countAllResults();
        $rPager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rowsBuilder = $db
            ->table('ims_item_request_list iirl')
            ->select('iirl.*, creator.username as created_by_name, approver.username as approved_by_name')
            ->join('users creator', 'creator.id = iirl.created_by', 'left')
            ->join('users approver', 'approver.id = iirl.approved_by', 'left');

        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like('iirl.request_name', $search)
                ->orLike('iirl.request_number', $search)
                ->orLike('iirl.status', $search)
                ->groupEnd();
        }

        $rows = $rowsBuilder
            ->orderBy('iirl.id', 'desc')
            ->limit($rPager['perPage'], $rPager['offset'])
            ->get()
            ->getResultArray();

        $requestIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $itemMap    = [];
        $attachMap  = [];

        if ($requestIds !== []) {
            $itemRows = $db
                ->table('ims_request_items iri')
                ->select('iri.*, ic.category_name, ib.brand_name, isp.supplier_name, iu.unit_name')
                ->join('ims_categories ic', 'ic.id = iri.category_id', 'left')
                ->join('ims_brands ib', 'ib.id = iri.suggested_brand_id', 'left')
                ->join('ims_suppliers isp', 'isp.id = iri.suggested_supplier_id', 'left')
                ->join('ims_units iu', 'iu.id = iri.default_unit_id', 'left')
                ->whereIn('iri.ims_item_request_list_id', $requestIds)
                ->orderBy('iri.id', 'asc')
                ->get()->getResultArray();

            foreach ($itemRows as $ir) {
                $itemMap[(int) $ir['ims_item_request_list_id']][] = $ir;
            }

            try {
                $attachRows = model(RequestAttachmentModel::class)
                    ->whereIn('ims_item_request_list_id', $requestIds)
                    ->orderBy('created_at', 'desc')
                    ->findAll();
            } catch (Throwable) {
                $attachRows = [];
            }

            foreach ($attachRows as $ar) {
                $attachMap[(int) $ar['ims_item_request_list_id']][] = $ar;
            }
        }

        foreach ($rows as &$row) {
            $row['items']       = $itemMap[(int) $row['id']] ?? [];
            $row['attachments'] = $attachMap[(int) $row['id']] ?? [];
        }
        unset($row);

        return $this->render('Modules\IMS\Views\purchasing\requests', [
            'pageTitle'   => lang('IMS.requests.indexTitle'),
            'pageSubtitle' => lang('IMS.requests.indexSubtitle'),
            'rows'        => $rows,
            'categories'  => model(CategoryModel::class)->orderBy('category_name', 'asc')->findAll(),
            'brands'      => model(BrandModel::class)->orderBy('brand_name', 'asc')->findAll(),
            'suppliers'   => model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll(),
            'units'       => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'approvers'   => $this->requestApproverOptions(),
            'rPager'      => $rPager,
            'rQuery'      => ['page' => $rPager['page'], 'per_page' => $rPager['perPage'], 'q' => $search],
            'validation'  => session('errors') ?? [],
            'modalState'  => session('ims_requests_modal'),
            'currentUserId' => lab_core_current_user()?->id,
        ]);
    }

    public function createRequest()
    {
        $name = trim((string) $this->request->getPost('request_name'));
        $approverId = $this->nullableInt('approved_by');

        if ($name === '') {
            return $this->redirectRequests(null, 'request-form-modal', 'danger', [
                'request_name' => lang('IMS.requests.validation.requestNameRequired'),
            ]);
        }

        if ($approverId === null || ! $this->userCanApproveRequest($approverId)) {
            return $this->redirectRequests(null, 'request-form-modal', 'danger', [
                'approved_by' => lang('IMS.requests.validation.validApprover'),
            ]);
        }

        model(ItemRequestListModel::class)->insert([
            'request_number' => $this->generateRequestNumber(),
            'request_name' => $name,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => lab_core_current_user()?->id,
            'status'       => 'draft',
            'approved_by'  => $approverId,
            'remark'       => trim((string) $this->request->getPost('remark')) ?: null,
        ]);

        return $this->redirectRequests(lang('IMS.requests.messages.created'));
    }

    public function requestDetail(int $id): string
    {
        $db = Database::connect();
        $request = $db->table('ims_item_request_list iirl')
            ->select('iirl.*, creator.username as created_by_name, approver.username as approved_by_name')
            ->join('users creator', 'creator.id = iirl.created_by', 'left')
            ->join('users approver', 'approver.id = iirl.approved_by', 'left')
            ->where('iirl.id', $id)
            ->get()
            ->getRowArray();

        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('IMS.requests.messages.notFound'));
        }

        $items = $db->table('ims_request_items iri')
            ->select('iri.*, ic.category_name, ib.brand_name, isp.supplier_name, iu.unit_name')
            ->join('ims_categories ic', 'ic.id = iri.category_id', 'left')
            ->join('ims_brands ib', 'ib.id = iri.suggested_brand_id', 'left')
            ->join('ims_suppliers isp', 'isp.id = iri.suggested_supplier_id', 'left')
            ->join('ims_units iu', 'iu.id = iri.default_unit_id', 'left')
            ->where('iri.ims_item_request_list_id', $id)
            ->orderBy('iri.id', 'asc')
            ->get()
            ->getResultArray();

        $attachments = model(RequestAttachmentModel::class)
            ->where('ims_item_request_list_id', $id)
            ->orderBy('created_at', 'desc')
            ->findAll();

        return $this->render('Modules\IMS\Views\purchasing\request_detail', [
            'pageTitle' => lang('IMS.requests.detailTitle'),
            'pageSubtitle' => lang('IMS.requests.detailSubtitle'),
            'request' => $request,
            'items' => $items,
            'attachments' => $attachments,
            'categories' => model(CategoryModel::class)->orderBy('category_name', 'asc')->findAll(),
            'brands' => model(BrandModel::class)->orderBy('brand_name', 'asc')->findAll(),
            'suppliers' => model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll(),
            'units' => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'validation' => session('errors') ?? [],
            'modalState' => session('ims_request_detail_modal'),
            'activeDetailTab' => (string) ($this->request->getGet('tab') ?: session('ims_request_detail_tab') ?: 'items'),
        ]);
    }

    public function updateRequest(int $id)
    {
        $model  = model(ItemRequestListModel::class);
        $record = $model->find($id);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftEditOnly'), null, 'danger');
        }

        $name = trim((string) $this->request->getPost('request_name'));
        $approverId = $this->nullableInt('approved_by');

        if ($name === '') {
            return $this->redirectRequests(null, 'request-form-modal', 'danger', [
                'request_name' => lang('IMS.requests.validation.requestNameRequired'),
            ], $id);
        }

        if ($approverId === null || ! $this->userCanApproveRequest($approverId)) {
            return $this->redirectRequests(null, 'request-form-modal', 'danger', [
                'approved_by' => lang('IMS.requests.validation.validApprover'),
            ], $id);
        }

        $model->update($id, [
            'request_name' => $name,
            'approved_by'  => $approverId,
            'remark'       => trim((string) $this->request->getPost('remark')) ?: null,
        ]);

        return $this->redirectRequests(lang('IMS.requests.messages.updated'));
    }

    public function deleteRequest(int $id)
    {
        $record = model(ItemRequestListModel::class)->find($id);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftDeleteOnly'), null, 'danger');
        }

        try {
            model(ItemRequestListModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectRequests(lang('IMS.requests.messages.deleteFailed'), null, 'danger');
        }

        return $this->redirectRequests(lang('IMS.requests.messages.deleted'));
    }

    public function submitRequest(int $id)
    {
        $record = model(ItemRequestListModel::class)->find($id);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftSubmitOnly'), null, 'danger');
        }

        if (empty($record['approved_by'])) {
            return $this->redirectRequests(lang('IMS.requests.messages.approverRequired'), null, 'danger');
        }

        model(ItemRequestListModel::class)->update($id, ['status' => 'submitted']);

        return $this->redirectRequests(lang('IMS.requests.messages.submitted'));
    }

    public function approveRequest(int $id)
    {
        $record = model(ItemRequestListModel::class)->find($id);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'submitted') {
            return $this->redirectRequests(lang('IMS.requests.messages.submittedApproveOnly'), null, 'danger');
        }

        $currentUserId = lab_core_current_user()?->id;
        if ($currentUserId === null || (int) $record['approved_by'] !== (int) $currentUserId) {
            return $this->redirectRequests(lang('IMS.requests.messages.assignedApproverApproveOnly'), null, 'danger');
        }

        model(ItemRequestListModel::class)->update($id, [
            'status'        => 'approved',
            'approved_date' => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectRequests(lang('IMS.requests.messages.approved'));
    }

    public function rejectRequest(int $id)
    {
        $record = model(ItemRequestListModel::class)->find($id);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'submitted') {
            return $this->redirectRequests(lang('IMS.requests.messages.submittedRejectOnly'), null, 'danger');
        }

        $currentUserId = lab_core_current_user()?->id;
        if ($currentUserId === null || (int) $record['approved_by'] !== (int) $currentUserId) {
            return $this->redirectRequests(lang('IMS.requests.messages.assignedApproverRejectOnly'), null, 'danger');
        }

        model(ItemRequestListModel::class)->update($id, [
            'status' => 'rejected',
            'remark' => trim((string) $this->request->getPost('remark')) ?: $record['remark'],
        ]);

        return $this->redirectRequests(lang('IMS.requests.messages.rejected'));
    }

    public function addRequestItem(int $requestId)
    {
        $record = model(ItemRequestListModel::class)->find($requestId);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftAddItemOnly'), null, 'danger');
        }

        $itemName = trim((string) $this->request->getPost('item_name'));

        if ($itemName === '') {
            return $this->redirectRequestItems($requestId, lang('IMS.requests.validation.itemNameRequired'), 'danger');
        }

        model(RequestItemModel::class)->insert([
            'ims_item_request_list_id' => $requestId,
            'item_name'                => $itemName,
            'alternate_name'           => trim((string) $this->request->getPost('alternate_name')) ?: null,
            'category_id'              => $this->nullableInt('category_id'),
            'description'              => trim((string) $this->request->getPost('description')) ?: null,
            'catalog_no'               => trim((string) $this->request->getPost('catalog_no')) ?: null,
            'cas_no'                   => trim((string) $this->request->getPost('cas_no')) ?: null,
            'ec_no'                    => trim((string) $this->request->getPost('ec_no')) ?: null,
            'grade'                    => trim((string) $this->request->getPost('grade')) ?: null,
            'unit_price'               => ($p = trim((string) $this->request->getPost('unit_price'))) !== '' ? (float) $p : null,
            'pack_size'                => trim((string) $this->request->getPost('pack_size')) ?: null,
            'qty'                      => ($q = trim((string) $this->request->getPost('qty'))) !== '' ? (float) $q : null,
            'suggested_brand_id'       => $this->nullableInt('suggested_brand_id'),
            'suggested_supplier_id'    => $this->nullableInt('suggested_supplier_id'),
            'default_unit_id'          => $this->nullableInt('default_unit_id'),
        ]);

        return $this->redirectRequestItems($requestId, lang('IMS.requests.messages.itemAdded'));
    }

    public function updateRequestItem(int $requestId, int $itemId)
    {
        $record = model(ItemRequestListModel::class)->find($requestId);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftEditItemOnly'), null, 'danger');
        }

        $item = model(RequestItemModel::class)->find($itemId);

        if ($item === null || (int) $item['ims_item_request_list_id'] !== $requestId) {
            return $this->redirectRequestItems($requestId, lang('IMS.requests.messages.itemNotFound'), 'danger');
        }

        $itemName = trim((string) $this->request->getPost('item_name'));

        if ($itemName === '') {
            return $this->redirectRequestItems($requestId, lang('IMS.requests.validation.itemNameRequired'), 'danger');
        }

        model(RequestItemModel::class)->update($itemId, [
            'item_name'             => $itemName,
            'alternate_name'        => trim((string) $this->request->getPost('alternate_name')) ?: null,
            'category_id'           => $this->nullableInt('category_id'),
            'description'           => trim((string) $this->request->getPost('description')) ?: null,
            'catalog_no'            => trim((string) $this->request->getPost('catalog_no')) ?: null,
            'cas_no'                => trim((string) $this->request->getPost('cas_no')) ?: null,
            'ec_no'                 => trim((string) $this->request->getPost('ec_no')) ?: null,
            'grade'                 => trim((string) $this->request->getPost('grade')) ?: null,
            'unit_price'            => ($p = trim((string) $this->request->getPost('unit_price'))) !== '' ? (float) $p : null,
            'pack_size'             => trim((string) $this->request->getPost('pack_size')) ?: null,
            'qty'                   => ($q = trim((string) $this->request->getPost('qty'))) !== '' ? (float) $q : null,
            'suggested_brand_id'    => $this->nullableInt('suggested_brand_id'),
            'suggested_supplier_id' => $this->nullableInt('suggested_supplier_id'),
            'default_unit_id'       => $this->nullableInt('default_unit_id'),
        ]);

        return $this->redirectRequestItems($requestId, lang('IMS.requests.messages.itemUpdated'));
    }

    public function deleteRequestItem(int $requestId, int $itemId)
    {
        $record = model(ItemRequestListModel::class)->find($requestId);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        if ($record['status'] !== 'draft') {
            return $this->redirectRequests(lang('IMS.requests.messages.draftRemoveItemOnly'), null, 'danger');
        }

        $item = model(RequestItemModel::class)->find($itemId);

        if ($item === null || (int) $item['ims_item_request_list_id'] !== $requestId) {
            return $this->redirectRequestItems($requestId, lang('IMS.requests.messages.itemNotFound'), 'danger');
        }

        model(RequestItemModel::class)->delete($itemId);

        return $this->redirectRequestItems($requestId, lang('IMS.requests.messages.itemRemoved'));
    }

    public function uploadRequestAttachment(int $requestId)
    {
        $record = model(ItemRequestListModel::class)->find($requestId);

        if ($record === null) {
            return $this->redirectRequests(lang('IMS.requests.messages.notFound'), null, 'danger');
        }

        $file = $this->request->getFile('attachment_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectRequestDetail($requestId, null, 'request-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.requests.validation.chooseFile'),
            ], 'documents');
        }

        if (! $file->isValid()) {
            return $this->redirectRequestDetail($requestId, null, 'request-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.requests.validation.uploadFailed'),
            ], 'documents');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectRequestDetail($requestId, null, 'request-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.requests.validation.maxFileSize'),
            ], 'documents');
        }

        $attachmentType = trim((string) $this->request->getPost('attachment_type')) ?: 'document';
        $remarks        = trim((string) $this->request->getPost('remarks')) ?: null;
        $destination    = FCPATH . 'uploads/ims/requests/' . $requestId;

        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(RequestAttachmentModel::class)->insert([
            'ims_item_request_list_id' => $requestId,
            'attachment_type'          => $attachmentType,
            'file_name'                => $originalName,
            'file_path'                => 'uploads/ims/requests/' . $requestId . '/' . $newName,
            'remarks'                  => $remarks,
        ]);

        return $this->redirectRequestDetail($requestId, lang('IMS.requests.messages.attachmentUploaded'), null, 'success', [], 'documents');
    }

    public function deleteRequestAttachment(int $requestId, int $attachmentId)
    {
        $attachment = model(RequestAttachmentModel::class)->find($attachmentId);

        if ($attachment === null || (int) $attachment['ims_item_request_list_id'] !== $requestId) {
            return $this->redirectRequestDetail($requestId, lang('IMS.requests.messages.attachmentNotFound'), null, 'danger', [], 'documents');
        }

        model(RequestAttachmentModel::class)->delete($attachmentId);
        $fullPath = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) ($attachment['file_path'] ?? '')), DIRECTORY_SEPARATOR);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        return $this->redirectRequestDetail($requestId, lang('IMS.requests.messages.attachmentDeleted'), null, 'success', [], 'documents');
    }

    private function resolveRequestPaginationState(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        return ['page' => $page, 'perPage' => $perPage];
    }

    private function redirectRequests(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $requestId = null)
    {
        $state    = $this->resolveRequestPaginationState();
        $redirect = redirect()->to(site_url('ims/requests?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_requests_modal', ['modal' => $modal, 'r_id' => $requestId]);
        }

        return $redirect;
    }

    private function redirectRequestItems(int $requestId, ?string $message = null, string $type = 'success')
    {
        return $this->redirectRequestDetail($requestId, $message, null, $type, [], 'items');
    }

    private function redirectRequestDetail(int $requestId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], string $tab = 'items')
    {
        $redirect = redirect()->to(site_url('ims/requests/' . $requestId . '?' . http_build_query(['tab' => $tab])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_request_detail_modal', $modal);
        }

        $redirect = $redirect->with('ims_request_detail_tab', $tab);

        return $redirect;
    }

    private function generateRequestNumber(): string
    {
        $db = Database::connect();
        $dateKey = date('Ymd');
        $prefix = 'PR-' . $dateKey . '-';

        $rows = $db->table('ims_item_request_list')
            ->select('request_number')
            ->like('request_number', $prefix, 'after')
            ->get()
            ->getResultArray();

        $max = 0;
        foreach ($rows as $row) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) $row['request_number'], $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $max++;
            $requestNumber = $prefix . str_pad((string) $max, 2, '0', STR_PAD_LEFT);
            $exists = $db->table('ims_item_request_list')
                ->where('request_number', $requestNumber)
                ->countAllResults() > 0;
        } while ($exists);

        return $requestNumber;
    }

    private function requestApproverOptions(): array
    {
        $eligibleGroups = $this->requestApproverGroups();
        $db = Database::connect();

        $builder = $db->table('users u')
            ->select('u.id, u.username')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_permissions_users apu', "apu.user_id = u.id AND apu.permission = 'ims.requests.approve'", 'left');

        if ($eligibleGroups !== []) {
            $builder->groupStart()
                ->whereIn('agu.group', $eligibleGroups)
                ->orWhere('apu.permission', 'ims.requests.approve')
                ->groupEnd();
        } else {
            $builder->where('apu.permission', 'ims.requests.approve');
        }

        return $builder
            ->groupBy('u.id, u.username')
            ->orderBy('u.username', 'asc')
            ->get()
            ->getResultArray();
    }

    private function userCanApproveRequest(int $userId): bool
    {
        $eligibleGroups = $this->requestApproverGroups();
        $db = Database::connect();

        if ($eligibleGroups !== []) {
            $hasGroupPermission = $db->table('auth_groups_users')
                ->where('user_id', $userId)
                ->whereIn('group', $eligibleGroups)
                ->countAllResults() > 0;

            if ($hasGroupPermission) {
                return true;
            }
        }

        return $db->table('auth_permissions_users')
            ->where('user_id', $userId)
            ->where('permission', 'ims.requests.approve')
            ->countAllResults() > 0;
    }

    /**
     * @return list<string>
     */
    private function requestApproverGroups(): array
    {
        $groups = [];
        $matrix = setting('AuthGroups.matrix') ?? [];

        if ($matrix === []) {
            $roleManager = service('roleManager');

            foreach ($roleManager->roleTemplates() as $alias => $role) {
                $matrix[$alias] = $roleManager->normalizePermissions($role['permissions'] ?? []);
            }
        }

        foreach ($matrix as $alias => $permissions) {
            if (! is_array($permissions)) {
                continue;
            }

            if (in_array('ims.requests.approve', $permissions, true)) {
                $groups[] = (string) $alias;
            }
        }

        return array_values(array_unique($groups));
    }

    public function masterData(): string
    {
        $activeTab = (string) ($this->request->getGet('tab') ?: session('ims_master_tab') ?: 'brands');
        $activeTab = in_array($activeTab, ['brands', 'categories', 'units', 'locations', 'checklists', 'supplier_classifications'], true) ? $activeTab : 'brands';
        $paginationState = $this->resolveMasterPaginationState();

        $brandData = $this->paginateSimpleTable(
            'ims_brands',
            'brand_name',
            'asc',
            $paginationState['brands']['page'],
            $paginationState['brands']['perPage']
        );

        $categoryData = $this->paginateSimpleTable(
            'ims_categories',
            'category_name',
            'asc',
            $paginationState['categories']['page'],
            $paginationState['categories']['perPage']
        );

        $unitData = $this->paginateUnits(
            $paginationState['units']['page'],
            $paginationState['units']['perPage']
        );

        $checklistData = $this->paginateChecklists(
            $paginationState['checklists']['page'],
            $paginationState['checklists']['perPage']
        );

        $supplierClassificationData = $this->paginateSimpleTable(
            'ims_supplier_classification',
            'id',
            'asc',
            $paginationState['supplier_classifications']['page'],
            $paginationState['supplier_classifications']['perPage']
        );

        $checklistIds = array_map(static fn (array $row): int => (int) $row['id'], $checklistData['rows']);
        $checklistDetails = [];
        $checklistClassifications = [];

        if ($checklistIds !== []) {
            $detailRows = model(ChecklistDetailModel::class)
                ->whereIn('ims_checklist_id', $checklistIds)
                ->orderBy('id', 'asc')
                ->findAll();

            foreach ($detailRows as $detail) {
                $checklistDetails[(int) $detail['ims_checklist_id']][] = $detail;
            }

            $classificationRows = Database::connect()
                ->table('ims_checklist_classification icc')
                ->select('icc.*, isc.class_name')
                ->join('ims_supplier_classification isc', 'isc.id = icc.ims_supplier_classification_id', 'left')
                ->whereIn('icc.ims_checklist_id', $checklistIds)
                ->orderBy('icc.ims_checklist_id', 'asc')
                ->orderBy('isc.id', 'asc')
                ->get()
                ->getResultArray();

            foreach ($classificationRows as $classification) {
                $checklistClassifications[(int) $classification['ims_checklist_id']][] = $classification;
            }
        }

        $db = Database::connect();
        $locState = $paginationState['locations'];
        $locTotal = (int) $db->table('ims_storage_locations')->countAllResults();
        $locationsPager = $this->buildPaginationData($locTotal, $locState['page'], $locState['perPage']);
        $locationRows = $db->table('ims_storage_locations isl')
            ->select('isl.*, parent.name as parent_name')
            ->join('ims_storage_locations parent', 'parent.id = isl.parent_id', 'left')
            ->orderBy('isl.name', 'asc')
            ->limit($locationsPager['perPage'], $locationsPager['offset'])
            ->get()->getResultArray();

        $masterQuery = ['tab' => $activeTab];

        foreach ($paginationState as $tab => $state) {
            $masterQuery[$tab . '_page'] = $state['page'];
            $masterQuery[$tab . '_per_page'] = $state['perPage'];
        }

        return $this->render('Modules\IMS\Views\catalog\master_data', [
            'pageTitle' => lang('IMS.masterData.title'),
            'pageSubtitle' => lang('IMS.masterData.subtitle'),
            'brands' => $brandData['rows'],
            'categories' => $categoryData['rows'],
            'units' => $unitData['rows'],
            'checklists' => $checklistData['rows'],
            'supplierClassifications' => $supplierClassificationData['rows'],
            'checklistDetails' => $checklistDetails,
            'checklistClassifications' => $checklistClassifications,
            'locationRows' => $locationRows,
            'brandsPager' => $brandData['pagination'],
            'categoriesPager' => $categoryData['pagination'],
            'unitsPager' => $unitData['pagination'],
            'checklistsPager' => $checklistData['pagination'],
            'supplierClassificationsPager' => $supplierClassificationData['pagination'],
            'locationsPager' => $locationsPager,
            'unitOptions' => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'parentOptions' => model(StorageLocationModel::class)->orderBy('name', 'asc')->findAll(),
            'activeTab' => $activeTab,
            'paginationState' => $paginationState,
            'masterQuery' => $masterQuery,
            'validation' => session('errors') ?? [],
            'modalState' => session('ims_master_modal'),
            'locationModalId' => session('ims_master_location_id'),
            'checklistModalId' => session('ims_master_checklist_id'),
            'checklistDetailModalId' => session('ims_master_detail_id'),
            'supplierClassificationModalId' => session('ims_master_supplier_classification_id'),
        ]);
    }

    public function createBrand()
    {
        $tab = $this->resolveMasterTab('brands');

        $rules = [
            'brand_name' => 'required|min_length[2]|max_length[150]|is_unique[ims_brands.brand_name]',
            'country' => 'permit_empty|max_length[120]',
            'website' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'brand-form-modal');
        }

        $logoPath = $this->handleBrandLogoUpload();

        if (is_array($logoPath)) {
            return redirect()->to(site_url('ims/master-data?tab=' . $tab))
                ->withInput()
                ->with('errors', $logoPath)
                ->with('ims_master_tab', $tab)
                ->with('ims_master_modal', 'brand-form-modal');
        }

        model(BrandModel::class)->insert([
            'brand_name' => trim((string) $this->request->getPost('brand_name')),
            'country' => trim((string) $this->request->getPost('country')) ?: null,
            'website' => trim((string) $this->request->getPost('website')) ?: null,
            'logo' => $logoPath,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return $this->redirectMasterData($tab, 'Brand created successfully.');
    }

    public function updateBrand(int $id)
    {
        $tab = $this->resolveMasterTab('brands');
        $model = model(BrandModel::class);

        if ($model->find($id) === null) {
            return $this->redirectMasterData($tab, 'Brand record was not found.', null, 'danger');
        }

        $rules = [
            'brand_name' => 'required|min_length[2]|max_length[150]|is_unique[ims_brands.brand_name,id,' . $id . ']',
            'country' => 'permit_empty|max_length[120]',
            'website' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'brand-form-modal');
        }

        $currentLogo = $model->find($id)['logo'] ?? null;
        $logoPath = $this->handleBrandLogoUpload($currentLogo);

        if (is_array($logoPath)) {
            return redirect()->to(site_url('ims/master-data?tab=' . $tab))
                ->withInput()
                ->with('errors', $logoPath)
                ->with('ims_master_tab', $tab)
                ->with('ims_master_modal', 'brand-form-modal');
        }

        $model->update($id, [
            'brand_name' => trim((string) $this->request->getPost('brand_name')),
            'country' => trim((string) $this->request->getPost('country')) ?: null,
            'website' => trim((string) $this->request->getPost('website')) ?: null,
            'logo' => $logoPath,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return $this->redirectMasterData($tab, 'Brand updated successfully.');
    }

    public function deleteBrand(int $id)
    {
        $tab = $this->resolveMasterTab('brands');
        $record = model(BrandModel::class)->find($id);

        try {
            model(BrandModel::class)->delete($id);
            $this->deleteBrandLogoFile($record['logo'] ?? null);
        } catch (Throwable) {
            return $this->redirectMasterData($tab, 'Brand cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Brand deleted successfully.');
    }

    public function createCategory()
    {
        $tab = $this->resolveMasterTab('categories');
        $rules = [
            'category_name' => 'required|min_length[2]|max_length[150]|is_unique[ims_categories.category_name]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'category-form-modal');
        }

        model(CategoryModel::class)->insert([
            'category_name' => trim((string) $this->request->getPost('category_name')),
        ]);

        return $this->redirectMasterData($tab, 'Category created successfully.');
    }

    public function updateCategory(int $id)
    {
        $tab = $this->resolveMasterTab('categories');
        $model = model(CategoryModel::class);

        if ($model->find($id) === null) {
            return $this->redirectMasterData($tab, 'Category record was not found.', null, 'danger');
        }

        $rules = [
            'category_name' => 'required|min_length[2]|max_length[150]|is_unique[ims_categories.category_name,id,' . $id . ']',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'category-form-modal');
        }

        $model->update($id, [
            'category_name' => trim((string) $this->request->getPost('category_name')),
        ]);

        return $this->redirectMasterData($tab, 'Category updated successfully.');
    }

    public function deleteCategory(int $id)
    {
        $tab = $this->resolveMasterTab('categories');
        try {
            model(CategoryModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectMasterData($tab, 'Category cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Category deleted successfully.');
    }

    public function createUnit()
    {
        $tab = $this->resolveMasterTab('units');
        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]',
            'unit_type' => 'permit_empty|max_length[50]',
            'conversion_factor' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'unit-form-modal');
        }

        if ($this->unitExists()) {
            return redirect()->to(site_url('ims/master-data?tab=' . $tab))
                ->withInput()
                ->with('errors', ['unit_name' => 'This unit name and type combination already exists.'])
                ->with('ims_master_tab', $tab)
                ->with('ims_master_modal', 'unit-form-modal');
        }

        model(UnitModel::class)->insert([
            'unit_name' => trim((string) $this->request->getPost('unit_name')),
            'unit_type' => trim((string) $this->request->getPost('unit_type')) ?: null,
            'base_unit_id' => $this->nullableInt('base_unit_id'),
            'conversion_factor' => $this->request->getPost('conversion_factor') !== null && $this->request->getPost('conversion_factor') !== ''
                ? $this->request->getPost('conversion_factor')
                : 1,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return $this->redirectMasterData($tab, 'Unit created successfully.');
    }

    public function updateUnit(int $id)
    {
        $tab = $this->resolveMasterTab('units');
        $model = model(UnitModel::class);

        if ($model->find($id) === null) {
            return $this->redirectMasterData($tab, 'Unit record was not found.', null, 'danger');
        }

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]',
            'unit_type' => 'permit_empty|max_length[50]',
            'conversion_factor' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData($tab, null, 'unit-form-modal');
        }

        if ($this->unitExists($id)) {
            return redirect()->to(site_url('ims/master-data?tab=' . $tab))
                ->withInput()
                ->with('errors', ['unit_name' => 'This unit name and type combination already exists.'])
                ->with('ims_master_tab', $tab)
                ->with('ims_master_modal', 'unit-form-modal');
        }

        $baseUnitId = $this->nullableInt('base_unit_id');

        if ($baseUnitId === $id) {
            return redirect()->to(site_url('ims/master-data?tab=' . $tab))
                ->withInput()
                ->with('errors', ['base_unit_id' => 'A unit cannot reference itself as base unit.'])
                ->with('ims_master_tab', $tab)
                ->with('ims_master_modal', 'unit-form-modal');
        }

        $model->update($id, [
            'unit_name' => trim((string) $this->request->getPost('unit_name')),
            'unit_type' => trim((string) $this->request->getPost('unit_type')) ?: null,
            'base_unit_id' => $baseUnitId,
            'conversion_factor' => $this->request->getPost('conversion_factor') !== null && $this->request->getPost('conversion_factor') !== ''
                ? $this->request->getPost('conversion_factor')
                : 1,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return $this->redirectMasterData($tab, 'Unit updated successfully.');
    }

    public function deleteUnit(int $id)
    {
        $tab = $this->resolveMasterTab('units');
        try {
            model(UnitModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectMasterData($tab, 'Unit cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Unit deleted successfully.');
    }

    public function createChecklist()
    {
        $tab = $this->resolveMasterTab('checklists');

        if (($validationRedirect = $this->validateChecklistForm()) !== true) {
            return $validationRedirect;
        }

        $db = Database::connect();
        $db->transStart();

        $checklistId = (int) model(ChecklistModel::class)->insert($this->checklistPayload() + [
            'created_by' => lab_core_current_user()?->id,
        ], true);

        $supplierClassifications = model(SupplierClassificationModel::class)
            ->orderBy('id', 'asc')
            ->findAll();

        $defaultClassificationRates = [
            'Approved Preferred' => ['min' => 95.00, 'max' => 100.00],
            'Approved' => ['min' => 80.00, 'max' => 95.00],
            'Conditional Approval' => ['min' => 70.00, 'max' => 80.00],
            'Improvement Required' => ['min' => 60.00, 'max' => 70.00],
            'Disqualified' => ['min' => 0.00, 'max' => 60.00],
        ];

        $classificationRows = [];
        foreach ($supplierClassifications as $classification) {
            $defaultRate = $defaultClassificationRates[(string) $classification['class_name']] ?? ['min' => 0.00, 'max' => 0.00];
            $classificationRows[] = [
                'ims_checklist_id' => $checklistId,
                'rate_min' => $defaultRate['min'],
                'rate_max' => $defaultRate['max'],
                'ims_supplier_classification_id' => (int) $classification['id'],
            ];
        }

        if ($classificationRows !== []) {
            model(ChecklistClassificationModel::class)->insertBatch($classificationRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectMasterData($tab, 'Checklist could not be created.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Checklist created successfully.');
    }

    public function updateChecklist(int $id)
    {
        $tab = $this->resolveMasterTab('checklists');
        $model = model(ChecklistModel::class);

        if ($model->find($id) === null) {
            return $this->redirectMasterData($tab, 'Checklist record was not found.', null, 'danger');
        }

        if (($validationRedirect = $this->validateChecklistForm($id)) !== true) {
            return $validationRedirect;
        }

        $model->update($id, $this->checklistPayload());

        return $this->redirectMasterData($tab, 'Checklist updated successfully.');
    }

    public function deleteChecklist(int $id)
    {
        $tab = $this->resolveMasterTab('checklists');

        try {
            model(ChecklistModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectMasterData($tab, 'Checklist cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Checklist deleted successfully.');
    }

    public function updateChecklistClassifications(int $checklistId)
    {
        $tab = $this->resolveMasterTab('checklists');

        if (model(ChecklistModel::class)->find($checklistId) === null) {
            return $this->redirectMasterData($tab, 'Checklist record was not found.', null, 'danger');
        }

        $submittedRows = $this->request->getPost('classifications');
        if (! is_array($submittedRows) || $submittedRows === []) {
            return $this->redirectMasterData($tab, 'No classification config was submitted.', null, 'danger');
        }

        $model = model(ChecklistClassificationModel::class);
        $existingRows = $model
            ->where('ims_checklist_id', $checklistId)
            ->findAll();
        $existingIds = array_map(static fn (array $row): int => (int) $row['id'], $existingRows);

        $updates = [];
        foreach ($submittedRows as $rowId => $values) {
            $rowId = (int) $rowId;
            if (! in_array($rowId, $existingIds, true) || ! is_array($values)) {
                continue;
            }

            $rateMin = trim((string) ($values['rate_min'] ?? ''));
            $rateMax = trim((string) ($values['rate_max'] ?? ''));

            if ($rateMin === '' || $rateMax === '' || ! is_numeric($rateMin) || ! is_numeric($rateMax)) {
                return $this->redirectMasterData($tab, 'Rate min and rate max must be valid numbers.', null, 'danger');
            }

            if ((float) $rateMin > (float) $rateMax) {
                return $this->redirectMasterData($tab, 'Rate min must be less than or equal to rate max.', null, 'danger');
            }

            $updates[] = [
                'id' => $rowId,
                'rate_min' => (float) $rateMin,
                'rate_max' => (float) $rateMax,
            ];
        }

        if ($updates === []) {
            return $this->redirectMasterData($tab, 'No valid classification config was submitted.', null, 'danger');
        }

        $db = Database::connect();
        $db->transStart();
        $model->updateBatch($updates, 'id');
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectMasterData($tab, 'Classification config could not be updated.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Classification config updated successfully.');
    }

    public function createChecklistDetail(int $checklistId)
    {
        $tab = $this->resolveMasterTab('checklists');

        if (model(ChecklistModel::class)->find($checklistId) === null) {
            return $this->redirectMasterData($tab, 'Checklist record was not found.', null, 'danger');
        }

        if (($validationRedirect = $this->validateChecklistDetailForm($checklistId)) !== true) {
            return $validationRedirect;
        }

        model(ChecklistDetailModel::class)->insert($this->checklistDetailPayload($checklistId));

        return $this->redirectMasterData($tab, 'Checklist item created successfully.');
    }

    public function updateChecklistDetail(int $checklistId, int $detailId)
    {
        $tab = $this->resolveMasterTab('checklists');
        $model = model(ChecklistDetailModel::class);
        $detail = $model->find($detailId);

        if ($detail === null || (int) $detail['ims_checklist_id'] !== $checklistId) {
            return $this->redirectMasterData($tab, 'Checklist item was not found.', null, 'danger');
        }

        if (($validationRedirect = $this->validateChecklistDetailForm($checklistId, $detailId)) !== true) {
            return $validationRedirect;
        }

        $model->update($detailId, $this->checklistDetailPayload($checklistId));

        return $this->redirectMasterData($tab, 'Checklist item updated successfully.');
    }

    public function deleteChecklistDetail(int $checklistId, int $detailId)
    {
        $tab = $this->resolveMasterTab('checklists');
        $model = model(ChecklistDetailModel::class);
        $detail = $model->find($detailId);

        if ($detail === null || (int) $detail['ims_checklist_id'] !== $checklistId) {
            return $this->redirectMasterData($tab, 'Checklist item was not found.', null, 'danger');
        }

        try {
            $model->delete($detailId);
        } catch (Throwable) {
            return $this->redirectMasterData($tab, 'Checklist item cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectMasterData($tab, 'Checklist item deleted successfully.');
    }

    public function updateSupplierClassification(int $id)
    {
        $tab = $this->resolveMasterTab('supplier_classifications');
        $model = model(SupplierClassificationModel::class);

        if ($model->find($id) === null) {
            return $this->redirectMasterData($tab, 'Supplier classification record was not found.', null, 'danger');
        }

        $uniqueRule = 'is_unique[ims_supplier_classification.class_name,id,' . $id . ']';
        if (! $this->validate([
            'class_name' => 'required|min_length[2]|max_length[100]|' . $uniqueRule,
        ])) {
            return $this->redirectMasterData($tab, null, 'supplier-classification-form-modal', 'success', [], [
                'ims_master_supplier_classification_id' => $id,
            ]);
        }

        $model->update($id, [
            'class_name' => trim((string) $this->request->getPost('class_name')),
        ]);

        return $this->redirectMasterData($tab, 'Supplier classification updated successfully.');
    }

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function validateChecklistForm(?int $ignoreId = null)
    {
        $uniqueRule = $ignoreId === null
            ? 'is_unique[ims_checklist.checklist_name]'
            : 'is_unique[ims_checklist.checklist_name,id,' . $ignoreId . ']';

        $rules = [
            'checklist_name' => 'required|min_length[2]|max_length[255]|' . $uniqueRule,
            'description' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData('checklists', null, 'checklist-form-modal', 'success', [], [
                'ims_master_checklist_id' => $ignoreId,
            ]);
        }

        return true;
    }

    /**
     * @return array<string, int|string|null>
     */
    private function checklistPayload(): array
    {
        return [
            'checklist_name' => trim((string) $this->request->getPost('checklist_name')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ];
    }

    private function validateChecklistDetailForm(int $checklistId, ?int $detailId = null)
    {
        $rules = [
            'check_item' => 'required|min_length[2]|max_length[255]',
            'weight' => 'required|decimal|greater_than_equal_to[0]',
            'description' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectMasterData('checklists', null, 'checklist-detail-form-modal', 'success', [], [
                'ims_master_checklist_id' => $checklistId,
                'ims_master_detail_id' => $detailId,
            ]);
        }

        $newWeight = (float) trim((string) $this->request->getPost('weight'));
        $weightQuery = Database::connect()
            ->table('ims_checklist_detail')
            ->select('COALESCE(SUM(weight), 0) as total_weight', false)
            ->where('ims_checklist_id', $checklistId);

        if ($detailId !== null) {
            $weightQuery->where('id !=', $detailId);
        }

        $currentTotal = (float) ($weightQuery->get()->getRowArray()['total_weight'] ?? 0);
        if (($currentTotal + $newWeight) > 100) {
            return $this->redirectMasterData('checklists', null, 'checklist-detail-form-modal', 'danger', [
                'weight' => 'Total checklist weight must be less than or equal to 100.',
            ], [
                'ims_master_checklist_id' => $checklistId,
                'ims_master_detail_id' => $detailId,
            ]);
        }

        return true;
    }

    /**
     * @return array<string, int|string|null>
     */
    private function checklistDetailPayload(int $checklistId): array
    {
        return [
            'ims_checklist_id' => $checklistId,
            'check_item' => trim((string) $this->request->getPost('check_item')),
            'weight' => trim((string) $this->request->getPost('weight')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ];
    }

    private function unitExists(?int $ignoreId = null): bool
    {
        $builder = model(UnitModel::class)
            ->where('unit_name', trim((string) $this->request->getPost('unit_name')));

        $unitType = trim((string) $this->request->getPost('unit_type')) ?: null;

        if ($unitType === null) {
            $builder->where('unit_type', null);
        } else {
            $builder->where('unit_type', $unitType);
        }

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->first() !== null;
    }

    /**
     * @return array<string, string>|string|null
     */
    private function handleBrandLogoUpload(?string $currentLogo = null)
    {
        /** @var UploadedFile|null $file */
        $file = $this->request->getFile('logo_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $currentLogo;
        }

        if (! $file->isValid()) {
            return ['logo_file' => 'Logo upload failed. Please try again.'];
        }

        $mimeType = $file->getMimeType();
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            return ['logo_file' => 'Logo must be a JPG, PNG, WEBP, or SVG image.'];
        }

        if ($file->getSizeByUnit('mb') > 2) {
            return ['logo_file' => 'Logo size must not exceed 2MB.'];
        }

        $destination = FCPATH . 'uploads/ims/brands';

        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($destination, $newName, true);

        $newPath = 'uploads/ims/brands/' . $newName;
        $this->deleteBrandLogoFile($currentLogo);

        return $newPath;
    }

    private function deleteBrandLogoFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (preg_match('~^(?:https?:)?//|^data:~', $path)) {
            return;
        }

        $fullPath = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function resolveMasterTab(string $default): string
    {
        $tab = (string) ($this->request->getPost('active_tab') ?? $default);

        return in_array($tab, ['brands', 'categories', 'units', 'checklists', 'supplier_classifications'], true) ? $tab : $default;
    }

    private function validateLocationForm(?int $ignoreId = null)
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'code' => 'required|min_length[2]|max_length[50]',
            'parent_id' => 'permit_empty|integer',
            'temperature_min' => 'permit_empty|decimal',
            'temperature_max' => 'permit_empty|decimal',
            'humidity_min' => 'permit_empty|decimal',
            'humidity_max' => 'permit_empty|decimal',
            'description' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectLocations(null, 'location-form-modal');
        }

        $name = trim((string) $this->request->getPost('name'));
        $code = trim((string) $this->request->getPost('code'));
        $parentId = $this->request->getPost('parent_id');
        $parentId = $parentId !== null && $parentId !== '' ? (int) $parentId : null;
        $model = model(StorageLocationModel::class);

        $codeQuery = $model->where('code', $code);

        if ($ignoreId !== null) {
            $codeQuery = $codeQuery->where('id !=', $ignoreId);
        }

        if ($codeQuery->first() !== null) {
            return $this->redirectLocations(null, 'location-form-modal', 'danger', [
                'code' => 'Location code already exists.',
            ], $ignoreId);
        }

        if ($parentId !== null) {
            if ($ignoreId !== null && $parentId === $ignoreId) {
                return $this->redirectLocations(null, 'location-form-modal', 'danger', [
                    'parent_id' => 'A location cannot be its own parent.',
                ], $ignoreId);
            }

            if ($model->find($parentId) === null) {
                return $this->redirectLocations(null, 'location-form-modal', 'danger', [
                    'parent_id' => 'Selected parent location is invalid.',
                ], $ignoreId);
            }
        }

        $temperatureMin = $this->nullableDecimal('temperature_min');
        $temperatureMax = $this->nullableDecimal('temperature_max');
        $humidityMin = $this->nullableDecimal('humidity_min');
        $humidityMax = $this->nullableDecimal('humidity_max');

        if ($temperatureMin !== null && $temperatureMax !== null && $temperatureMin > $temperatureMax) {
            return $this->redirectLocations(null, 'location-form-modal', 'danger', [
                'temperature_max' => 'Temperature max must be greater than or equal to temperature min.',
            ], $ignoreId);
        }

        if ($humidityMin !== null && $humidityMax !== null && $humidityMin > $humidityMax) {
            return $this->redirectLocations(null, 'location-form-modal', 'danger', [
                'humidity_max' => 'Humidity max must be greater than or equal to humidity min.',
            ], $ignoreId);
        }

        return true;
    }

    /**
     * @return array<string, int|string|float|null>
     */
    private function locationPayload(): array
    {
        $parentId = $this->request->getPost('parent_id');

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'parent_id' => $parentId !== null && $parentId !== '' ? (int) $parentId : null,
            'temperature_min' => $this->nullableDecimal('temperature_min'),
            'temperature_max' => $this->nullableDecimal('temperature_max'),
            'humidity_min' => $this->nullableDecimal('humidity_min'),
            'humidity_max' => $this->nullableDecimal('humidity_max'),
            'requires_restricted_access' => $this->request->getPost('requires_restricted_access') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ];
    }

    private function nullableDecimal(string $field): ?float
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : (float) $value;
    }

    private function nullableDate(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, string> $errors
     */
    private function redirectLocations(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $locationId = null)
    {
        $paginationState = $this->resolveMasterPaginationState();
        $query = ['tab' => 'locations'];

        foreach ($paginationState as $tab => $state) {
            $query[$tab . '_page'] = $state['page'];
            $query[$tab . '_per_page'] = $state['perPage'];
        }

        $redirect = redirect()->to(site_url('ims/master-data?' . http_build_query($query)))
            ->withInput()
            ->with('ims_master_tab', 'locations');

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirectErrors = $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []);
            $redirect = $redirect
                ->with('errors', $redirectErrors)
                ->with('ims_master_modal', $modal)
                ->with('ims_master_location_id', $locationId);
        }

        return $redirect;
    }

    /**
     * @return array{page:int, perPage:int}
     */
    private function resolveLocationPaginationState(): array
    {
        $page = (int) ($this->request->getGet('page')
            ?? $this->request->getPost('page')
            ?? 1);
        $perPage = (int) ($this->request->getGet('per_page')
            ?? $this->request->getPost('per_page')
            ?? 10);

        return [
            'page' => max(1, $page),
            'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10,
        ];
    }

    /**
     * @return array<string, array{page:int, perPage:int}>
     */
    private function resolveMasterPaginationState(): array
    {
        $state = [];

        foreach (['brands', 'categories', 'units', 'locations', 'checklists', 'supplier_classifications'] as $tab) {
            $page = (int) ($this->request->getGet($tab . '_page')
                ?? $this->request->getPost($tab . '_page')
                ?? 1);
            $perPage = (int) ($this->request->getGet($tab . '_per_page')
                ?? $this->request->getPost($tab . '_per_page')
                ?? 10);

            $state[$tab] = [
                'page' => max(1, $page),
                'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10,
            ];
        }

        return $state;
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    private function paginateSimpleTable(
        string $table,
        string $orderColumn,
        string $direction,
        int $page,
        int $perPage
    ): array {
        $db = Database::connect();
        $total = (int) $db->table($table)->countAllResults();
        $pagination = $this->buildPaginationData($total, $page, $perPage);

        $rows = $db->table($table)
            ->orderBy($orderColumn, $direction)
            ->limit($pagination['perPage'], $pagination['offset'])
            ->get()
            ->getResultArray();

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    private function paginateUnits(int $page, int $perPage): array
    {
        $db = Database::connect();
        $total = (int) $db->table('ims_units')->countAllResults();
        $pagination = $this->buildPaginationData($total, $page, $perPage);

        $rows = $db->table('ims_units iu')
            ->select('iu.*, base.unit_name as base_unit_name')
            ->join('ims_units base', 'base.id = iu.base_unit_id', 'left')
            ->orderBy('iu.unit_name', 'asc')
            ->limit($pagination['perPage'], $pagination['offset'])
            ->get()
            ->getResultArray();

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    private function paginateChecklists(int $page, int $perPage): array
    {
        $db = Database::connect();
        $total = (int) $db->table('ims_checklist')->countAllResults();
        $pagination = $this->buildPaginationData($total, $page, $perPage);

        $rows = $db->table('ims_checklist ic')
            ->select('ic.*, creator.username as created_by_name, COUNT(icd.id) as detail_count, COALESCE(SUM(icd.weight), 0) as total_weight')
            ->join('users creator', 'creator.id = ic.created_by', 'left')
            ->join('ims_checklist_detail icd', 'icd.ims_checklist_id = ic.id', 'left')
            ->groupBy('ic.id')
            ->orderBy('ic.checklist_name', 'asc')
            ->limit($pagination['perPage'], $pagination['offset'])
            ->get()
            ->getResultArray();

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * @return array{
     *   page:int,
     *   perPage:int,
     *   total:int,
     *   totalPages:int,
     *   offset:int,
     *   from:int,
     *   to:int,
     *   pages: array<int, int>
     * }
     */
    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;
        $from = $total === 0 ? 0 : $offset + 1;
        $to = $total === 0 ? 0 : min($offset + $perPage, $total);
        $windowStart = max(1, $page - 2);
        $windowEnd = min($totalPages, $page + 2);

        return [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'from' => $from,
            'to' => $to,
            'pages' => range($windowStart, $windowEnd),
        ];
    }

    private function redirectMasterData(string $tab, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], array $context = [])
    {
        $paginationState = $this->resolveMasterPaginationState();
        $query = ['tab' => $tab];

        foreach ($paginationState as $stateTab => $state) {
            $query[$stateTab . '_page'] = $state['page'];
            $query[$stateTab . '_per_page'] = $state['perPage'];
        }

        $redirect = redirect()->to(site_url('ims/master-data?' . http_build_query($query)))
            ->withInput()
            ->with('ims_master_tab', $tab);

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirectErrors = $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []);
            $redirect = $redirect->with('errors', $redirectErrors)->with('ims_master_modal', $modal);
        }

        foreach ($context as $key => $value) {
            $redirect = $redirect->with($key, $value);
        }

        return $redirect;
    }

}
