<?php

namespace App\Http\Forms\Admin\WithdrawRequest;

use App\Enums\EBank;
use App\Enums\EStatus;
use App\Http\Forms\Admin\BaseAdminForm;
use App\Models\User\User;

class WithdrawRequestForm extends BaseAdminForm
{
    protected $hasCreatedAtField = true;
    protected $hasUpdatedAtField = true;
    protected $hasDeletedAtField = false;
    protected $hasStateField = false;

    protected $showSaveAndReloadButton = false;
    protected $showSaveAndNewButton = false;
    protected $showSaveAndCloseButton = true;
    protected $showCancelButton = true;

    protected $showBulkEnableStateButton = false;
    protected $showSingleEnableStateButton = false;

    protected $showBulkDisableStateButton = false;
    protected $showSingleDisableStateButton = false;

    protected $showBulkSoftDeleteButton = false;
    protected $showSingleSoftDeleteButton = false;

    protected $showBulkDestroyButton = false;
    protected $showSingleDestroyButton = false;

    protected $showBulkRestoreButton = false;
    protected $showSingleRestoreButton = false;

    protected $showOnlyTrashedButton = false;
    protected $showWithoutTrashedButton = false;
    protected $showAllButton = false;

    protected $hasExportExcelXlsx = false;
    protected $hasExportExcelXls = false;
    protected $hasExportCsv = false;
    protected $hasExportPdf = false;

    public function buildForm()
    {
        parent::buildForm();

        $this->add( 'user_id', 'choice', [
            'label'      => __( 'admin.withdraw-requests.fields.user_id' ),
            'label_attr' => [],
            'attr'       => [
                'placeholder' => __( 'admin.withdraw-requests.placeholder-fields.form_user_id' ),
                'class'       => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices'    => $this->getUserList(),
        ] );

        $this->add('amount', 'text', [
            'label' => __('admin.withdraw-requests.fields.amount'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control txt-left disabled',
            ],
        ]);

        $this->add('status', 'choice', [
            'label' => __('admin.withdraw-requests.fields.status'),
            'label_attr' => [
                'class' => 'control-label col-xs-12',
            ],
            'attr' => [
                'placeholder' => __('admin.withdraw-requests.placeholder-fields.form_status'),
                'class' => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices' => $this->getStatuses(),
        ]);

        $this->add( 'bank', 'choice', [
            'label'      => __( 'admin.withdraw-requests.fields.bank' ),
            'label_attr' => [],
            'attr'       => [
                'placeholder' => __( 'admin.withdraw-requests.placeholder-fields.form_bank' ),
                'class'       => 'form-control col-md-12 select2 item-select2 item-select2-disable-placeholder',
            ],
            'choices'    => $this->getBankList(),
        ] );

        $this->add('card_number', 'text', [
            'label' => __('admin.withdraw-requests.fields.card_number'),
            'label_attr' => [
                'class' => 'control-label disabled',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add('sheba_number', 'text', [
            'label' => __('admin.withdraw-requests.fields.sheba_number'),
            'label_attr' => [
                'class' => 'control-label disabled',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add('jalali_created_at', 'text', [
            'label' => __('admin.withdraw-requests.fields.jalali_created_at'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add('jalali_updated_at', 'text', [
            'label' => __('admin.withdraw-requests.fields.jalali_updated_at'),
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control disabled',
            ],
        ]);
    }

    protected function getStatuses()
    {
        return EStatus::flipTrans();
    }

    protected function getBankList()
    {
        return EBank::flipTrans();
    }

    protected function getUserList()
    {
        $users = User::query()->isMember()->enabled()->get();
        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'title' => $user->userable->fullName,
            ];
        })->toArray();

        return array_column($result,'title', 'id');
    }
}
