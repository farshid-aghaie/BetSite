<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Http\Forms\Admin\Admin\AdminFilter;
use App\Http\Forms\Admin\Admin\AdminForm;
use App\Http\Forms\Admin\User\UserFilter;
use App\Http\Forms\Admin\User\UserForm;
use App\Http\Requests\Admin\Admin\StoreAdmin;
use App\Http\Requests\Admin\Admin\UpdateAdmin;
use App\Models\Admin\Admin;
use App\Models\Permission\Permission;
use App\Models\Role\Role;
use App\Repositories\Admin\AdminRepository;
use Flash;
use Kris\LaravelFormBuilder\Facades\FormBuilder;

class AdminController extends BaseAdminController
{
    protected $adminRepository;
    protected $baseRouteName = 'admin.admins';

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function index()
    {
        $this->authorize('adminIndex', Admin::class);

        $form = FormBuilder::create(AdminForm::class);

        $filter = FormBuilder::create(AdminFilter::class);

        return view('AdminTemplate::admin.index', compact('form', 'filter'));
    }

    public function create()
    {
        $this->authorize('adminCreate', Admin::class);

        $form = FormBuilder::create(AdminForm::class, [
            'enctype' => 'multipart/form-data',
            'method' => 'POST',
            'url' => route('admin.admins.store'),
            'role' => 'form',
        ]);

        $filter = FormBuilder::create(AdminFilter::class);

        $roles = Role::query()->enabled()->isAdmin()->get();

        $permissions = Permission::query()->enabled()->isAdmin()->get();

        return view('AdminTemplate::admin.form', compact('form', 'filter', 'roles', 'permissions'));
    }

    public function store(StoreAdmin $request)
    {
        $this->authorize('adminCreate', Admin::class);

        $data = $request->all();

        $admin = $this->adminRepository->createAdmin($data);

        Flash::success(__('admin.admins.flash.store_successful'))->important();

        return $this->redirectToAction($request, $admin);
    }

    public function edit(Admin $admin)
    {
        $this->authorize('adminEdit', [Admin::class, $admin]);

        $form = FormBuilder::create(AdminForm::class, [
            'enctype' => 'multipart/form-data',
            'method' => 'PUT',
            'url' => route('admin.admins.update', $admin),
            'model' => $admin,
        ]);

        $filter = FormBuilder::create(AdminFilter::class);

        $currentRoleIds = $admin->user->roles()->enabled()->pluck('roles.id')->toArray();

        $currentPermissionIds = $admin->user->permissions()->enabled()->pluck('permissions.id')->toArray();

        $roles = Role::query()->enabled()->isAdmin()->get();

        $permissions = Permission::query()->enabled()->isAdmin()->get();

        return view('AdminTemplate::admin.form', compact('form', 'filter', 'admin', 'roles', 'permissions', 'currentPermissionIds', 'currentRoleIds'));
    }

    public function update(UpdateAdmin $request, Admin $admin)
    {
        $this->authorize('adminEdit', [Admin::class, $admin]);

        $data = $request->all();

        $admin = $this->adminRepository->updateAdmin($admin, $data);

        Flash::success(__('admin.admins.flash.update_successful'))->important();

        return $this->redirectToAction($request, $admin);
    }

    public function destroy(Admin $admin)
    {
        $this->authorize('adminDestroy', [Admin::class, $admin]);

        $this->adminRepository->destroyAdmin($admin);

        Flash::success(__('admin.admins.flash.destroy_successful'))->important();

        return redirect()->route('admin.admins.index');
    }
}
