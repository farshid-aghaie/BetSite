<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Repositories\Admin\AdminRepository;
use App\Transformers\Admin\Admin\AdminDatatableTransformer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminAjaxController extends BaseAdminController
{
    protected $adminRepository;
    protected $baseRouteName = 'admin.admins';

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function getDatatableData(Request $request)
    {
        return DataTables::of($this->adminRepository->getDataTable($request->input('filter_trash')))
            ->filter(function ($query) use ($request) {
                $this->adminRepository->filterDatatable($query, $request->input('search'));
            })
            ->filterColumn('id', function($query, $keyword) {
                $query->where('admins.id', $keyword);
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->where('admins.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('surname', function($query, $keyword) {
                $query->where('admins.surname', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('email', function($query, $keyword) {
                $query->where('users.email', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('mobile', function($query, $keyword) {
                $query->where('users.mobile', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('username', function($query, $keyword) {
                $query->where('users.username', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('state', function($query, $keyword) {
                $query->where('users.state', 'like', '%' . $keyword . '%');
            })
            ->setTransformer(new AdminDatatableTransformer)
            ->skipTotalRecords()
            ->make(true);
    }

    public function updateState($id)
    {
        $this->adminRepository->updateStateAdmin($id);
    }

    public function destroy($id)
    {
        $this->adminRepository->destroyAdmin($id);
    }

    public function softDelete($id)
    {
        $this->adminRepository->softDeleteAdmin($id);
    }
    public function restore($id)
    {
        $this->adminRepository->restoreAdmin($id);
    }
}
