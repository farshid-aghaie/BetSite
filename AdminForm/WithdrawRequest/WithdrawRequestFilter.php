<?php

namespace App\Http\Forms\Admin\WithdrawRequest;

use App\Enums\EStatus;
use App\Enums\EBank;
use App\Http\Forms\Admin\BaseAdminForm;
use App\Models\User\User;

class WithdrawRequestFilter extends BaseAdminForm
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

        $this->add('status', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.withdraw-requests.placeholder-fields.filter_status'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getStatusList(),
        ]);

        $this->add('user_id', 'choice', [
            'label' => __('admin.withdraw-requests.fields.user_id'),
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.withdraw-requests.placeholder-fields.filter_user_id'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getUserList(),
        ]);

        $this->add('amount', 'text', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'class' => 'form-control datatable-filter-column txt-left',
            ],
        ]);

        $this->add('bank', 'choice', [
            'label' => '',
            'label_attr' => [],
            'attr' => [
                'placeholder' => __('admin.withdraw-requests.placeholder-fields.filter_bank'),
                'class' => 'form-control col-md-12 select2 item-select2 datatable-filter-column',
            ],
            'choices' => $this->getBankList(),
        ]);
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

    protected function getStatusList()
    {
        return EStatus::flipTrans();
    }

    protected function getBankList()
    {
        return EBank::flipTrans();
    }
}
