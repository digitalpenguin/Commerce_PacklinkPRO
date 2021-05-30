<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use modmore\Commerce\Admin\Util\Action;
use modmore\Commerce\Admin\Util\Column;
use modmore\Commerce\Admin\Widgets\GridWidget;
use modmore\Commerce\Events\Admin\OrderActions;

class Grid extends GridWidget {
    public $key = 'carriers-grid';
    public $title = '';

    public $defaultSort = 'received_on';
    public $defaultSortDir = 'DESC';

    public function getTopToolbar(array $options = [])
    {
        $toolbar = [];
        $toolbar[] = [
            'name' => 'manual_label',
            'title' => 'Manual Label',
            'type' => 'button',
            'link' => $this->adapter->makeAdminUrl('deliveryOptions/manual'),
            'button_class' => 'commerce-ajax-modal',
            'icon_class' => 'envelope',
            'modal_title' => $this->adapter->lexicon('commerce.new_status'),
            'position' => 'top',
            'width' => 'four wide',
        ];
        return $toolbar;
    }

    public function getItems(array $options = [])
    {
        $services = $this->getOption('services');

        return $services;
    }

    public function getColumns(array $options = [])
    {
        return [

            new Column('carrier_name', 'Carrier'/*$this->adapter->lexicon('commerce_packlinkpro.carrier_name')*/, true),

            new Column('currency','Currency',true),
            new Column('base_price','Price',true),
            new Column('transit_time','Transit Time',true),
            new Column('first_estimated_delivery_date','Delivery Estimation',true),
            new Column('actions','Actions',true)
        ];
    }
}