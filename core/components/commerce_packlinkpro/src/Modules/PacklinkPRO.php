<?php
namespace DigitalPenguin\Commerce_PacklinkPRO\Modules;

use modmore\Commerce\Admin\Widgets\Form\CheckboxField;
use modmore\Commerce\Admin\Widgets\Form\PasswordField;
use modmore\Commerce\Admin\Widgets\Form\SectionField;
use modmore\Commerce\Events\Admin\GeneratorEvent;
use modmore\Commerce\Modules\BaseModule;
use plpOrderShipment;
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

        $this->adapter->loadClass(plpOrderShipment::class, $path . 'commerce_packlinkpro/');

        // Events
        $dispatcher->addListener(\Commerce::EVENT_DASHBOARD_INIT_GENERATOR, [$this, 'loadPages']);
    }

    public function loadPages(GeneratorEvent $event)
    {
        $generator = $event->getGenerator();
        $generator->addPage('deliveryOptions/deliveries', '\DigitalPenguin\Commerce_PacklinkPRO\Admin\DeliveryOptions\Services');
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        // Checkbox to enable test account keys and endpoints
        $fields[] = new CheckboxField($this->commerce, [
            'name' => 'properties[sandbox]',
            'label' => $this->adapter->lexicon('commerce_packlinkpro.use_sandbox'),
            'value' => $module->getProperty('sandbox', ''),
            'description' => $this->adapter->lexicon('commerce_packlinkpro.sandbox_no_key'),
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
}