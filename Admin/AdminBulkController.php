<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Repositories\Admin\AdminBulkRepository;
use Flash;
use Illuminate\Http\Request;

class AdminBulkController extends BaseAdminController
{
    protected $adminBulkRepository;
    protected $baseRouteName = 'admin.admins';

    public function __construct(AdminBulkRepository $adminBulkRepository)
    {
        $this->adminBulkRepository = $adminBulkRepository;
    }

    public function destroy(Request $request)
    {
        $data = $request->all();

        $this->adminBulkRepository->destroyAdmin($data);

        Flash::success(__('admin.admins.flash.bulk_destroy_successful'))->important();

        return redirect()->route('admin.admins.index');
    }
}
