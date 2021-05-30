<?php

use modmore\Commerce\Admin\Widgets\Form\DescriptionField;
use modmore\Commerce\Admin\Widgets\Form\Tab;
use modmore\Commerce\Admin\Widgets\Form\TextField;

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
class plpShippingMethod extends comShippingMethod
{
    public function getModelFields()
    {
        $fields = [];

        $fields[] = new Tab($this->commerce, [
            'label' => 'Shipping Origin'
        ]);

        $fields[] = new DescriptionField($this->commerce, [
            'label' => 'Shipping Origin',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Set the data for where you will be shipping from so that Packlink PRO can calculate the routes.'//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
        ]);

        $fields[] = new TextField($this->commerce, [
            'label' => 'Country',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'This should be a two-letter country code e.g. DE, FR etc.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[from_country]',
            'value' => $this->getProperty('from_country'),
        ]);

        $fields[] = new TextField($this->commerce, [
            'label' => 'Zip Code',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id'),
            'description' => 'Usually a 5 or 6 digit number.',//$this->adapter->lexicon('commerce_packlinkpro.shipping_id_desc'),
            'name' => 'properties[from_zip]',
            'value' => $this->getProperty('from_zip'),
        ]);

        return $fields;
    }
}
