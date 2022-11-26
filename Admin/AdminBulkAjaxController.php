<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Repositories\Admin\AdminBulkRepository;
use Illuminate\Http\Request;

class AdminBulkAjaxController extends BaseAdminController
{
    protected $adminBulkRepository;
    protected $baseRouteName = 'admin.admins';

    public function __construct(AdminBulkRepository $adminBulkRepository)
    {
        $this->adminBulkRepository = $adminBulkRepository;
    }

    public function updateState(Request $request)
    {
        $data = $request->all();
        $this->adminBulkRepository->updateStateAdmin($data);
    }

    public function destroy(Request $request)
    {
        $data = $request->all();
        $this->adminBulkRepository->destroyAdmin($data);
    }

    public function softDelete(Request $request)
    {
        $data = $request->all();
        $this->adminBulkRepository->softDeleteAdmin($data);
    }

    public function restore(Request $request)
    {
        $data = $request->all();
        $this->adminBulkRepository->restoreAdmin($data);
    }
}
