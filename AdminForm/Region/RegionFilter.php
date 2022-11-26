<?php

namespace App\Http\Forms\Admin\Region;

use App\Enums\ELinkTarget;
use App\Enums\ERegionType;
use App\Enums\EState;
use App\Http\Forms\Admin\BaseAdminForm;
use App\Models\Region\Region;

class RegionFilter extends BaseAdminForm
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
                'placeholder' => __('admin.regions.placeholder-fields.filter_state'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getStateList(),
        ]);

        $this->add('title', 'text', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column',
            ],
        ]);

        $this->add('parent_id', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.filter_parent_id'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getRegionIds(),
        ]);

        $this->add('region_type', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.regions.placeholder-fields.filter_region_type'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getRegionTypeList()
        ]);

        $this->add('ordering', 'number', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column',
            ],
        ]);
    }

    protected function getRegionIds()
    {
        return Region::withDepth()->defaultOrder()->get()->pluck('treeview_title', 'id')->toArray();
    }

    protected function getRegionTypeList()
    {
        return ERegionType::flipTrans();
    }

    protected function getStateList()
    {
        return EState::flipTrans();
    }
}
