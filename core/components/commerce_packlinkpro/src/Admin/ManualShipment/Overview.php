<?php

namespace DigitalPenguin\Commerce_PacklinkPRO\Admin\ManualShipment;

use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Admin\Page;
use modmore\Commerce\Admin\Widgets\HtmlWidget;

class Overview extends Page
{
    public $key = 'deliveryOptions/manual';
    public $title = 'Manual Shipment';//'commerce_referrals.referrer.edit';

    protected $orderShipment;

    public function setUp()
    {
        $orderShipmentId = (int)$this->getOption('id', 0);
        echo $orderShipmentId;
        $this->orderShipment = $this->adapter->getObject(\plpOrderShipment::class, [
            'id' => $orderShipmentId
        ]);

        if ($this->orderShipment instanceof \plpOrderShipment) {
            $section = new SimpleSection($this->commerce, [
                'title' => $this->title
            ]);

            // Set property on shipment
            $this->orderShipment->setProperty('carrier_type', 'manual');
            $this->orderShipment->save();

            $section->addWidget((new HtmlWidget($this->commerce,[
                'html' => $this->commerce->view()->render('admin/widgets/messages/success.twig', [
                    'message' => 'This shipment has been set to manual.'
                ])
            ]))->setUp());

            $this->addSection($section);
        }



        return $this;
    }
}