<?php
namespace DigitalPenguin\Commerce_PacklinkPRO\Modules;

use DigitalPenguin\Commerce_PacklinkPRO\API\APIClient;
use modmore\Commerce\Admin\Widgets\Form\CheckboxField;
use modmore\Commerce\Admin\Widgets\Form\PasswordField;
use modmore\Commerce\Admin\Widgets\Form\SectionField;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class PacklinkPRO extends BaseModule
{

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_packlinkpro:default');
        return $this->adapter->lexicon('commerce_packlinkpro');
    }

    public function getAuthor()
    {
        return 'Murray Wood';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_packlinkpro.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_packlinkpro:default');

        // Add the xPDO package so Commerce can detect the derivative classes
        $root = dirname(__DIR__, 2);
        $path = $root . '/model/';
        $this->adapter->loadPackage('commerce_packlinkpro', $path);

        // Add template path to twig
        $root = dirname(__DIR__, 2);
        $this->commerce->view()->addTemplatesPath($root . '/templates/');
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        // Checkbox to enable test account keys and endpoints
        $fields[] = new CheckboxField($this->commerce, [
            'name' => 'properties[sandbox]',
            'label' => $this->adapter->lexicon('commerce_packlinkpro.use_sandbox'),
            'value' => $module->getProperty('sandbox', '')
        ]);

        $fields[] = new SectionField($this->commerce, [
            'label' => $this->adapter->lexicon('commerce_packlinkpro.packlinkpro_api'),
            'description' => $this->adapter->lexicon('commerce_packlinkpro.api_key_required'),
        ]);

        $fields[] = new PasswordField($this->commerce, [
            'name' => 'properties[apikey]',
            'label' => $this->adapter->lexicon('commerce_packlinkpro.api_key'),
            'description' => $this->adapter->lexicon('commerce_packlinkpro.api_key_desc'),
            'value' => $module->getProperty('apikey', '')
        ]);

        return $fields;
    }

    /**
     * @param \comOrder $order
     * @param array $fulfillmentOrders
     */
    public function sendRequest(): void
    {
        $apiKey = $this->getConfig('apikey');
        $useSandbox = in_array($this->getConfig('usesandbox'), [1, true, 'on']);

        // Determine if we should use test API credentials or not
        $apiClient = new APIClient($apiKey, $useSandbox);

        // Authenticate with API and retrieve token
        $response = $apiClient->request('shipments',[],'GET');
        $data = $response->getData();
        $this->commerce->modx->log(MODX_LOG_LEVEL_ERROR, print_r($data, true));

    }
}