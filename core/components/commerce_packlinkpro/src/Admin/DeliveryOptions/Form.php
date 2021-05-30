<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use modmore\Commerce\Admin\Widgets\FormWidget;


class Form extends FormWidget
{
    protected $classKey = 'plpOrderShipment';
    public $key = 'deliveries-form';
    public $title = '';

    public function getFields(array $options = array())
    {
        $fields = [];




        return $fields;
    }

    public function getFormAction(array $options = array())
    {
        if ($this->record->get('id')) {
            return $this->adapter->makeAdminUrl('referrers/update', ['id' => $this->record->get('id')]);
        }
        return $this->adapter->makeAdminUrl('referrers/create');

    }
}