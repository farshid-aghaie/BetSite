<?php

namespace App\Http\Forms\Admin\Region;

use App\Enums\ELinkTarget;
use App\Enums\ERegionType;
use App\Enums\EState;
use App\Http\Forms\Admin\BaseAdminForm;
use App\Models\Region\Region;

class RegionForm extends BaseAdminForm
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
            'label' => __('admin.regions.fields.state'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'class' => 'make-switch',
                'data-size' => 'small',
                'data-on-color' => 'primary',
                'data-off-color' => 'default',
                'data-on-text' => "&nbsp;" . __('admin.regions.fields.enable_state') . "&nbsp;",
                'data-off-text' => "&nbsp;" . __('admin.regions.fields.disable_state') . "&nbsp;",
            ],
            'checked' => $this->stateIsChecked(),
        ]);

        $this->add('title', 'text', [
            'label' => __('admin.regions.fields.title'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.form_title'),
                'class' => 'form-control',
            ],
        ]);

        $this->add('parent_id', 'choice', [
            'label' => __('admin.regions.fields.parent_id'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.form_parent_id'),
                'class' => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices' => $this->getRegionIds()
        ]);

        $this->add('region_type', 'choice', [
            'label' => __('admin.regions.fields.region_type'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.form_region_type'),
                'class' => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices' => $this->getRegionTypeList()
        ]);

        $this->add('ordering', 'number', [
            'label' => __('admin.regions.fields.ordering'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.form_ordering'),
                'class' => 'form-control',
            ],
        ]);
    }

    protected function getRegionIds()
    {
        return Region::withDepth()->enabled()->defaultOrder()->get()->pluck('treeview_title', 'id')->toArray();
    }

    protected function getRegionTypeList()
    {
        return ERegionType::flipTrans();
    }

    protected function stateIsChecked()
    {
        $model = $this->getModel();

        return $model && $model->state == EState::DISABLED ? false : true;
    }
}
