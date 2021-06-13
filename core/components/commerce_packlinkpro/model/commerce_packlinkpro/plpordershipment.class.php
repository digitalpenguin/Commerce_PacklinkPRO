<?php

use modmore\Commerce\Admin\Util\Action;
use modmore\Commerce\Admin\Widgets\Form\DescriptionField;
use modmore\Commerce\Admin\Widgets\Form\NumberField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Admin\Widgets\Form\Validation\Required;
use modmore\Commerce\Gateways\Helpers\GatewayHelper;

/**
 * PacklinkPRO for Commerce.
 *
 * Copyright 2021 by Murray Wood @ Digital Penguin
 *
 * This file is meant to be used with Commerce by modmore. A valid Commerce license is required.
 *
 * @package commerce_packlinkpro
 * @license See core/components/commerce_packlinkpro/docs/license.txt
 */
class plpOrderShipment extends comOrderShipment
{
    /**
     * Defines actions on this shipment which will be added to the actions menu when viewing
     * shipments for a specific order.
     *
     * Note: when linking to non-core dashboard pages, these need to be registered with the
     * generator by a module.
     *
     * @return array
     */
    public function getShipmentActions()
    {
        $actions = [];
        $editUrl = $this->adapter->makeAdminUrl('deliveryOptions/deliveries', ['id' => $this->get('id'), 'order' => $this->get('order')]);
        $actions[] = (new Action())
            ->setUrl($editUrl)
            ->setTitle('Delivery Options'/*$this->adapter->lexicon('commerce.shipment.edit')*/)
            ->setIcon('icon-edit');
        return $actions;
    }


    public static function getFieldsForProduct(Commerce $commerce, comProduct $product, comDeliveryType $deliveryType)
    {
        $fields = [];

        $fields[] = new TextField($commerce, [
            'label' => 'Shipping Description',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'One or two words describing the type of item.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[shipping_desc]',
            'value' => $product->getProperty('shipping_desc'),
            'validation' => [
                new Required(),
            ]
        ]);

        $fields[] = new NumberField($commerce, [
            'label' => 'Width (cm)',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Packlink: Width of the package in cm.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[width]',
            'value' => $product->getProperty('width'),
            'validation' => [
                new Required(),
            ]
        ]);

        $fields[] = new NumberField($commerce, [
            'label' => 'Height (cm)',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Packlink: Height of the package in cm.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[height]',
            'value' => $product->getProperty('height'),
            'validation' => [
                new Required(),
            ]
        ]);

        $fields[] = new NumberField($commerce, [
            'label' => 'Length (cm)',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Packlink: Length of the package in cm.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[length]',
            'value' => $product->getProperty('length'),
            'validation' => [
                new Required(),
            ]
        ]);

        $fields[] = new DescriptionField($commerce, [
            'label' => 'Weight must be in kg',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Packlink expects the weight to be in kg.'//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
        ]);

        return $fields;
    }

    /**
     * Return an array of arrays with keys "label" and "value" to add to the order items grid in the shipment column.
     *
     * This should only be the most important at-a-glance information - consider making all values available via
     * getModelFields() in the modal or a custom shipment action.
     *
     * @return array[]
     */
    public function getShipmentDetails(): array
    {

        return [];
//      [
//          'label' =>  'Tracking Link',
//          'value' =>  $this->getProperty('tracking_url')
//      ]

    }

    /**
     * @return array|string[]
     */
    public function getShipmentData(): array
    {
        $data = [
            'platform' => 'PRO',
            'platform_country' => 'DE'
        ];

        // Get origin data
        $shippingMethod = $this->adapter->getObject(\plpShippingMethod::class, [
            'id' => $this->get('method'),
            'removed' => false,
        ]);
        if (!$shippingMethod instanceof \plpShippingMethod) return [];

        $data['from'] = [
            'name'      =>  $shippingMethod->getProperty('from_first_name'),
            'surname'   =>  $shippingMethod->getProperty('from_last_name'),
            'company'   =>  $shippingMethod->getProperty('from_company'),
            'street1'   =>  $shippingMethod->getProperty('from_address_line1'),
            'street2'   =>  $shippingMethod->getProperty('from_address_line2'),
            'city'      =>  $shippingMethod->getProperty('from_city'),
            'state'     =>  $shippingMethod->getProperty('from_state'),
            'country'   =>  $shippingMethod->getProperty('from_country'),
            'zip'       =>  $shippingMethod->getProperty('from_zip'),
            'zip_code'  =>  $shippingMethod->getProperty('from_zip'),
            'phone'     =>  $shippingMethod->getProperty('from_phone'),
            'email'     =>  $shippingMethod->getProperty('from_email'),
        ];

        // Get destination data
        $order = $this->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $firstName = $shippingAddress->get('firstname');
        $lastName = $shippingAddress->get('lastname');
        $fullName = $shippingAddress->get('fullname');
        GatewayHelper::normalizeNames($firstName, $lastName, $fullName);

        $data['to'] = [
            'name'      =>  $firstName,
            'surname'   =>  $lastName,
            'company'   =>  $shippingAddress->get('company'),
            'street1'   =>  $shippingAddress->get('address1'),
            'street2'   =>  $shippingAddress->get('address2'),
            'city'      =>  $shippingAddress->get('city'),
            'state'     =>  $shippingAddress->get('state'),
            'country'   =>  $shippingAddress->get('country'),
            'zip'       =>  $shippingAddress->get('zip'),
            'zip_code'  =>  $shippingAddress->get('zip'),
            'phone'     =>  $shippingAddress->get('phone'),
            'email'     =>  $shippingAddress->get('email')
        ];

        // Get order item data
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();

            for ($i = 0; $i < $item->get('quantity'); $i++) {
                $data['packages'][$i] = [
                    'height' => $product->getProperty('height'),
                    'width' => $product->getProperty('width'),
                    'length' => $product->getProperty('length'),
                    'weight' => $product->get('weight')
                ];
            }
        }
//        echo '<pre>';
//        $this->adapter->log(1,print_r($data,true));
//        var_dump($data);
//        echo '</pre>';
        return  $data;
    }

    /**
     * Return a fully-qualified tracking URL for the shipment, if available. Return an empty string if not available.
     *
     * @return string
     */
    public function getTrackingURL(): string
    {
        return '';
    }

    /**
     * Executed when an order is marked as processing (from the cart), allowing order shipment types
     * to process actions.
     *
     * IMPORTANT: this method may be executed during a visitor request. Don't execute slow processes
     * synchronously in this method, considering scheduling those asynchronously.
     *
     * @return bool
     */
    public function onOrderStateProcessing()
    {
        $data = $this->getShipmentData();
        // Add "from" address to shipment
        $this->setProperty('packlink', $data);

        // Add services admin link
        $this->setProperty('services_link', $this->adapter->makeAdminUrl('deliveryOptions/deliveries', ['id' => $this->get('id'), 'order' => $this->get('order')]));
        $this->save();
        return true;
    }
}
