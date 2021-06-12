<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use DigitalPenguin\Commerce_PacklinkPRO\API\APIClient;
use modmore\Commerce\Admin\Util\Action;
use modmore\Commerce\Admin\Util\Column;
use modmore\Commerce\Admin\Widgets\GridWidget;
use modmore\Commerce\Events\Admin\OrderActions;

class Grid extends GridWidget {
    public $key = 'carriers-grid';
    protected $orderShipmentId;
    /** @var \plpOrderShipment */
    protected $orderShipment;
    /** @var \comOrder */
    protected $order;

    public function setUp()
    {
        $this->orderShipmentId = (int)$this->getOption('id', 0);
        $this->orderShipment = $this->adapter->getObject(\plpOrderShipment::class,['id' => $this->orderShipmentId]);
        $this->order = $this->orderShipment->getOrder();
        return parent::setUp();
    }

    public function getTopToolbar(array $options = [])
    {
        $toolbar = [];

        $toolbar[] = [
            'name' => 'filter_by_carrier',
            'title' => 'Filter by Carrier',//$this->adapter->lexicon('commerce.search_by_carrier'),
            'type' => 'textfield',
            'value' => array_key_exists('filter_by_carrier', $options) ? htmlentities($options['filter_by_carrier'], ENT_QUOTES, 'UTF-8') : '',
            'position' => 'top',
            'width' => 'four wide',
        ];

        $toolbar[] = [
            'name' => 'manual_shipment',
            'title' => 'Manual Shipment',
            'type' => 'button',
            'link' => $this->adapter->makeAdminUrl('deliveryOptions/manual', [
                'id'    => $this->orderShipmentId
            ]),
            'button_class' => 'commerce-ajax-modal',
            'icon_class' => 'envelope',
            'modal_title' => 'Manual Shipment',
            'position' => 'top',
            'width' => 'four wide',
        ];
        return $toolbar;
    }

    public function getItems(array $options = [])
    {
        $services = $this->getServices();

        $output = [];
        // Find partial matches when filtering
        if (!empty($options['filter_by_carrier'])) {
            foreach ($services as $service) {
                if (strpos(strtolower($service['carrier_name']), strtolower($options['filter_by_carrier'])) !== false) {
                    $output[] = $this->prepareItem($service);
                }
            }
        }
        // List all
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

            new Column('logo_id', '', false, true/*$this->adapter->lexicon('commerce_packlinkpro.carrier_name')*/),
            new Column('carrier_name', 'Carrier'/*$this->adapter->lexicon('commerce_packlinkpro.carrier_name')*/),
            new Column('name', 'Name'),
            new Column('currency','Currency'),
            new Column('base_price','Price', false, true),
            new Column('transit_time','Transit'),
            new Column('first_estimated_delivery_date','Delivery Est.'),
            new Column('button','', false, true)
        ];
    }

    public function prepareItem($service) {
        if (array_key_exists('logo_id', $service)) {
            $service['logo_id'] = '<img style="height:50px;" src="https://cdn.packlink.com/apps/carrier-logos/' . $service['logo_id'] . '.svg">';
        }

        if (array_key_exists('base_price', $service)) {
            $service['base_price'] = '<strong>' . $service['base_price'] . '</strong>';
        }

        $url = $this->adapter->makeAdminUrl('deliveryOptions/carrier', ['id' => $service['id'], 'shipment' => $this->orderShipmentId, 'order' => $this->order->get('id')]);
        $service['button'] = '<a href="' . $url . '" style="white-space:pre-wrap; word-break:break-word;" class="ui primary button commerce-ajax-modal">Select</a>';

        return $service;
    }

    public function getServices()
    {
        // Grab the packlink module
        $plpModule = null;
        foreach ($this->commerce->modules as $module) {
            if (get_class($module) === 'DigitalPenguin\Commerce_PacklinkPRO\Modules\PacklinkPRO') {
                $plpModule = $module;
            }
        }
        if (!$plpModule) return [];

        $useSandbox = $plpModule->getConfig('sandbox');
        $apiKey = $plpModule->getConfig('apikey');

        if(!$this->orderShipment instanceof \plpOrderShipment) return [];
        $data = $this->orderShipment->getShipmentData();

        $client = new APIClient($useSandbox, $apiKey);
        $response = $client->request('/v1/services', $data, 'GET');
        //$this->adapter->log(1,print_r($response,true));

        $responseData = $response->getData();

        if ($response->getStatusCode() !== 200) {
            echo 'Error connecting to the API...<br>';
            print_r($response);
            echo '<br><br>NOTE: If you are using the Packlink sandbox API, it is often offline. You may need to switch to the live API.<br>';
            return [];
        }

        return $responseData;
    }
}