<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions;

use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Admin\Page;

class Services extends Page
{
    public $key = 'deliveryOptions/deliveries';
    public $title = 'Delivery Options';//'commerce_referrals.referrer.edit';

    protected $orderShipment;

    public function setUp()
    {
        $orderShipmentId = (int)$this->getOption('id', 0);
        $section = new SimpleSection($this->commerce, [
            'title' => $this->title
        ]);

        $section->addWidget((new Grid($this->commerce,[
            'id' => $orderShipmentId
        ]))->setUp());

        $this->addSection($section);

        return $this;
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