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
    protected $apiClient;
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
        $this->apiClient = new APIClient($this->useSandbox, $this->apiKey);

        if ($this->orderShipment instanceof \plpOrderShipment) {
            $section = new SimpleSection($this->commerce, [
                'title' => $this->title
            ]);

            $details = $this->getCarrierDetails($this->carrierId);

            if (!$this->orderShipment->getProperty('draft_reference')) {

                $draft = $this->createDraftOrder($details);
                if (isset($draft['reference']) && !empty($draft['reference'])) {
                    // Set property on shipment
                    $this->orderShipment->setProperty('carrier_type', $details['carrier_name']);
                    $this->orderShipment->setProperty('draft_reference', $draft['reference']);
                    $this->orderShipment->save();
                }
            }

            $labelData = $this->getShippingLabel();

            $section->addWidget((new HtmlWidget($this->commerce,[
                'html' => '<pre>'.print_r($labelData,true).'</pre>'
            ]))->setUp());

            $this->addSection($section);
        }

        return $this;
    }

    public function createDraftOrder($details)
    {
        $data = $this->orderShipment->getProperty('packlink');
        $data['platform'] = 'MODX Commerce';
        $data['content'] = $this->getShipmentDescription();
        $data['content_value'] = $this->getShipmentTotalValue();
        $data['service_id'] = $details['service_id'];

        // Reference is order id and shipment id
        $data['shipment_custom_reference'] = 'modx-commerce-' .
            $this->orderShipment->getOrder()->get('id') . '-' .
            $this->orderShipment->get('id');

        $client = new APIClient($this->useSandbox, $this->apiKey);
        $response = $client->request('/v1/shipments', $data, 'POST');
        $this->adapter->log(1,print_r($response,true));

        return $response->getData();
    }

    /**
     * @param int $carrierId
     * @return array
     */
    public function getCarrierDetails(int $carrierId): array
    {
        $response = $this->apiClient->request('/v1/services/available/' . $carrierId . '/details', [], 'GET');
        //$this->adapter->log(1,print_r($response,true));
        return $response->getData();
    }

    public function getShippingLabel() {
        $shipmentRef = $this->orderShipment->getProperty('draft_reference');
        if ($shipmentRef) {
            $response = $this->apiClient->request('/v1/shipments/' . $shipmentRef . '/labels', [], 'GET');
            //$this->adapter->log(1,print_r($response,true));
            return $response->getData();
        }
        return [];
    }

    public function getTrackingId() {

    }

    /**
     * @return float|int
     */
    public function getShipmentTotalValue()
    {
        $value = $this->orderShipment->get('product_value');
        // This assumes currency has two decimal points
        return $value / 100;
    }

    /**
     * @return string
     */
    public function getShipmentDescription(): string
    {
        $output = '';
        $items = $this->orderShipment->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $output .= $product->getProperty('shipping_desc') . ', ';
        }
        return $output;
    }
}