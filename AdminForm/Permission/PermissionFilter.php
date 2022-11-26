<?php

namespace App\Http\Forms\Admin\Permission;

use App\Enums\EPermissionGroup;
use App\Enums\EState;
use App\Enums\EUserType;
use App\Http\Forms\Admin\BaseAdminForm;

class PermissionFilter extends BaseAdminForm
{
    public function buildForm()
    {
        parent::buildForm();

        $this->add('id', 'text', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column',
            ],
        ]);

        $this->add('state', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.filter_state'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getStateList(),
        ]);

        $this->add('permission_group', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.filter_permission_group'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getPermissionGroupList(),
        ]);

        $this->add('user_type', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.filter_user_type'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getUserTypeList(),
        ]);

        $this->add('title', 'text', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column',
            ],
        ]);

        $this->add('description', 'text', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column',
            ],
        ]);
    }

    protected function getStateList()
    {
        return EState::flipTrans();
    }

    protected function getPermissionGroupList()
    {
        return EPermissionGroup::flipTrans();
    }

    protected function getUserTypeList()
    {
        return EUserType::flipTrans();
    }
}
