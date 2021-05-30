<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use DigitalPenguin\Commerce_PacklinkPRO\API\APIClient;
use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Admin\Page;
use modmore\Commerce\Exceptions\ViewException;

class Services extends Page
{
    public $classKey = 'plpOrderShipment';
    public $key = 'deliveryOptions/deliveries';
    public $title = 'Delivery Options';//'commerce_referrals.referrer.edit';

    protected $orderShipment;
    protected $orderShipmentId;
    protected $order;

    public function setUp()
    {
        $orderShipmentId = (int)$this->getOption('id', 0);
        $this->order = $this->adapter->getObject(\comOrder::class, ['id' => $this->getOption('order')]);
        $this->orderShipment = $this->adapter->getObject('plpOrderShipment', ['id' => $orderShipmentId]);

        if ($this->orderShipment instanceof \plpOrderShipment) {
            $section = new SimpleSection($this->commerce, [
                'title' => $this->title
            ]);

            $section->addWidget((new Grid($this->commerce,[
                'orderShipmentId' => $this->orderShipmentId,
                'order' => $this->order,
                'services' => $this->getServices()
            ]))->setUp());

            $section2 = new SimpleSection($this->commerce, [

            ]);

            $this->addSection($section);
            $this->addSection($section2);

            return $this;
        }

        return $this->returnError($this->adapter->lexicon(''));
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

        $output = [];

        $data = $this->orderShipment->getShipmentData();

        $client = new APIClient($useSandbox, $apiKey);
        $response = $client->request('/v1/services', $data, 'GET');
        $data = $response->getData();
        echo '<pre>';
        var_dump($data[0]);
        echo '</pre>';

        return $data;

    }
}
//https://apisandbox.packlink.com/v1/services?
//platform=PRO
//&platform_country=DE
//&from[country]=DE
//&from[zip]=10179
//&packages[0][height]=20
//&packages[0][length]=12
//&packages[0][weight]=0.5
//&packages[0][width]=30
//&packages[1][height]=10
//&packages[1][length]=6
//&packages[1][weight]=0.5
//&packages[1][width]=30
//&sortBy=totalPrice
//&source=PRO&to[country]=GB
//&to[zip]=FY2 0AA