<?php

namespace App\Http\Forms\Admin\Role;

use App\Enums\ERoleGroup;
use App\Enums\EState;
use App\Enums\EUserType;
use App\Http\Forms\Admin\BaseAdminForm;

class RoleForm extends BaseAdminForm
{
    protected $hasCreatedAtField = true;
    protected $hasUpdatedAtField = true;
    protected $hasDeletedAtField = true;
    protected $hasStateField = true;

    protected $showSaveAndReloadButton = true;
    protected $showSaveAndNewButton = true;
    protected $showSaveAndCloseButton = true;
    protected $showCancelButton = true;

    protected $showBulkEnableStateButton = true;
    protected $showSingleEnableStateButton = true;

    protected $showBulkDisableStateButton = true;
    protected $showSingleDisableStateButton = true;

    protected $showBulkSoftDeleteButton = true;
    protected $showSingleSoftDeleteButton = true;

    protected $showBulkDestroyButton = true;
    protected $showSingleDestroyButton = true;

    protected $showBulkRestoreButton = true;
    protected $showSingleRestoreButton = true;

    protected $showOnlyTrashedButton = true;
    protected $showWithoutTrashedButton = true;
    protected $showAllButton = true;

    protected $hasExportExcelXlsx = false;
    protected $hasExportExcelXls = false;
    protected $hasExportCsv = false;
    protected $hasExportPdf = false;

    public function buildForm()
    {
        parent::buildForm();

        $this->add('state', 'checkbox', [
            'label' => __('admin.permissions.fields.state'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'class' => 'make-switch',
                'data-size' => 'small',
                'data-on-color' => 'primary',
                'data-off-color' => 'default',
                'data-on-text' => "&nbsp;" . __('admin.permissions.fields.enable_state') . "&nbsp;",
                'data-off-text' => "&nbsp;" . __('admin.permissions.fields.disable_state') . "&nbsp;",
            ],
            'checked' => $this->stateIsChecked(),
        ]);

        $this->add('title', 'text', [
            'label' => __('admin.permissions.fields.title'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.form_title'),
                'class' => 'form-control',
            ],
        ]);

        $this->add('user_type', 'choice', [
            'label' => __('admin.permissions.fields.user_type'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.form_user_type'),
                'class' => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices' => $this->getUserTypes(),
        ]);

        $this->add('description', 'text', [
            'label' => __('admin.permissions.fields.description'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'placeholder' => __('admin.permissions.placeholder-fields.form_description'),
                'class' => 'form-control',
            ],
        ]);
    }

    protected function stateIsChecked()
    {
        $model = $this->getModel();

        return $model && $model->state == EState::DISABLED ? false : true;
    }

    protected function getUserTypes()
    {
        return EUserType::flipTrans();
    }
}
