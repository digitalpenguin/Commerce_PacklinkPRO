<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\CreateOrder;

use DigitalPenguin\Commerce_PacklinkPRO\API\APIClient;
use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Admin\Page;
use modmore\Commerce\Admin\Widgets\HtmlWidget;

class Overview extends Page
{
    public $key = 'deliveryOptions/carrier';
    public $title = 'Loading...';//'commerce_referrals.referrer.edit';

    protected $orderShipment;
    protected $carrierId;
    protected $useSandbox;
    protected $apiKey;
    protected $carrierDetails;

    public function setUp()
    {
        $this->carrierId = (int)$this->getOption('id', 0);
        $shipmentId = (int)$this->getOption('shipment', 0);
        $this->orderShipment = $this->adapter->getObject(\plpOrderShipment::class, [
            'id' => $shipmentId
        ]);

        // Grab the packlink module
        $plpModule = null;
        foreach ($this->commerce->modules as $module) {
            if (get_class($module) === 'DigitalPenguin\Commerce_PacklinkPRO\Modules\PacklinkPRO') {
                $plpModule = $module;
            }
        }
        if (!$plpModule) return [];

        $this->useSandbox = $plpModule->getConfig('sandbox');
        $this->apiKey = $plpModule->getConfig('apikey');

        if ($this->orderShipment instanceof \plpOrderShipment) {
            $section = new SimpleSection($this->commerce, [
                'title' => $this->title
            ]);

            $details = $this->getCarrierDetails($this->carrierId);
            $draft = $this->createDraftOrder($details);

            // Set property on shipment
            $this->orderShipment->setProperty('carrier_type', $details['carrier_name']);
            $this->orderShipment->save();

            $section->addWidget((new HtmlWidget($this->commerce,[
                'html' => '<pre>'.print_r($draft,true).'</pre>'
            ]))->setUp());

            $this->addSection($section);
        }

        return $this;
    }

    public function createDraftOrder($details)
    {
        $data = $this->orderShipment->getProperty('packlink');
        $data['content'] = 'Test content'; // 'Description of content'
        $data['content_value'] = '200'; // total price
        $data['service_id'] = $details['service_id'];
        $data['shipment_custom_reference'] = 'testcustomreference';

        $client = new APIClient($this->useSandbox, $this->apiKey);
        $response = $client->request('/v1/shipments', $data, 'POST');
        $this->adapter->log(1,print_r($response,true));

        return $response->getData();
    }

    public function getCarrierDetails(int $carrierId)
    {
        $client = new APIClient($this->useSandbox, $this->apiKey);
        $response = $client->request('/v1/services/available/' . $carrierId . '/details', [], 'GET');
        //$this->adapter->log(1,print_r($response,true));

        return $response->getData();
    }
}