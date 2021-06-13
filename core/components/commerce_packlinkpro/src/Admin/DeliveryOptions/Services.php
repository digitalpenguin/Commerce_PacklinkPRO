<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Admin\Page;
use modmore\Commerce\Admin\Widgets\HtmlWidget;

class Services extends Page
{
    public $key = 'deliveryOptions/deliveries';
    public $title = 'Delivery Options';//'commerce_referrals.referrer.edit';

    protected $orderShipment;

    public function setUp()
    {
        $orderShipmentId = (int)$this->getOption('id', 0);
        $this->orderShipment = $this->adapter->getObject(\plpOrderShipment::class, [
            'id' => $orderShipmentId
        ]);

        $section = new SimpleSection($this->commerce, [
            'title' => $this->title
        ]);

        if ($this->orderShipment instanceof \plpOrderShipment) {
            $originAddress = $this->getOriginAddress();
            $destinationAddress = $this->getDestinationAddress();

            $section->addWidget((new HtmlWidget($this->commerce,[
                'html' => $this->commerce->view()->render('packlink/services-header.twig', [
                    'origin' =>  $originAddress,
                    'destination' => $destinationAddress
                ])
            ]))->setUp());
        }

        $section->addWidget((new Grid($this->commerce,[
            'id' => $orderShipmentId,
        ]))->setUp());

        $this->addSection($section);

        return $this;
    }

    public function getOriginAddress(): array
    {
        $shippingMethod = $this->adapter->getObject(\plpShippingMethod::class, [
            'id' => $this->orderShipment->get('method')
        ]);
        if (!$shippingMethod instanceof \plpShippingMethod) return [];

        return [
            'name' => $shippingMethod->getProperty('from_first_name'),
            'surname' => $shippingMethod->getProperty('from_last_name'),
            'company' => $shippingMethod->getProperty('from_company'),
            'address1' => $shippingMethod->getProperty('from_address_line1'),
            'address2' => $shippingMethod->getProperty('from_address_line2'),
            'address3' => $shippingMethod->getProperty('from_address_line3'),
            'city' => $shippingMethod->getProperty('from_city'),
            'state' => $shippingMethod->getProperty('from_state'),
            'zip' => $shippingMethod->getProperty('from_zip'),
            'country' => $shippingMethod->getProperty('from_country')
        ];

    }

    public function getDestinationAddress(): array
    {
        /** @var \comOrder $order */
        $order = $this->orderShipment->getOrder();
        $address = $order->getShippingAddress();
        return $address->toArray();
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