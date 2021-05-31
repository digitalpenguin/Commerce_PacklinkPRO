<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use DigitalPenguin\Commerce_PacklinkPRO\API\APIClient;
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
            'name' => 'search_by_carrier',
            'title' => 'Search by carrier',//$this->adapter->lexicon('commerce.search_by_carrier'),
            'type' => 'textfield',
            'value' => array_key_exists('search_by_carrier', $options) ? htmlentities($options['search_by_carrier'], ENT_QUOTES, 'UTF-8') : '',
            'position' => 'top',
            'width' => 'four wide',
        ];

        $toolbar[] = [
            'name' => 'manual_shipment',
            'title' => 'Manual Shipment',
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
        $services = $this->getServices();

        $output = [];
        if (!empty($options['search_by_carrier'])) {
            foreach ($services as $service) {
                // Find an exact or fuzzy match by the carrier name
                if (strpos(strtolower($service['carrier_name']), strtolower($options['search_by_carrier'])) !== false) {
                    $output[] = $this->prepareItem($service);
                }
            }
        }
        else {
            foreach ($services as $service) {
                $output[] = $this->prepareItem($service);
            }
        }
        return $output;
    }

    public function getColumns(array $options = [])
    {
        return [

            new Column('logo_id', '',false,true/*$this->adapter->lexicon('commerce_packlinkpro.carrier_name')*/),
            new Column('carrier_name', 'Carrier'/*$this->adapter->lexicon('commerce_packlinkpro.carrier_name')*/),
            new Column('name', 'Name'),
            new Column('currency','Currency'),
            new Column('base_price','Price',false,true),
            new Column('transit_time','Transit'),
            new Column('first_estimated_delivery_date','Delivery Est.'),
            new Column('button','',false,true)
        ];
    }

    public function prepareItem($service) {
        if (array_key_exists('logo_id', $service)) {
            $service['logo_id'] = '<img style="height:50px;" src="https://cdn.packlink.com/apps/carrier-logos/' . $service['logo_id'] . '.svg">';
        }

        if (array_key_exists('base_price', $service)) {
            $service['base_price'] = '<strong>' . $service['base_price'] . '</strong>';
        }

        $service['button'] = '<a href="#" style="white-space:pre-wrap; word-break:break-word;" class="ui primary button">Select</a>';

        return $service;
    }

    public function getServices()
    {
        $plpModule = null;
        foreach ($this->commerce->modules as $module) {
            if (get_class($module) === 'DigitalPenguin\Commerce_PacklinkPRO\Modules\PacklinkPRO') {
                $plpModule = $module;
            }
        }
        if (!$plpModule) return [];

        $useSandbox = $plpModule->getConfig('sandbox');
        $apiKey = $plpModule->getConfig('apikey');

        $orderShipment = $this->adapter->getObject(\plpOrderShipment::class,['id' => $this->getOption('id')]);
        if(!$orderShipment instanceof \plpOrderShipment) return [];

        $data = $orderShipment->getShipmentData();

        $client = new APIClient($useSandbox, $apiKey);
        $response = $client->request('/v1/services', $data, 'GET');
        $data = $response->getData();
//        echo '<pre>';
//        var_dump($data[3]);
//        echo '</pre>';

        return $data;

    }
}