<?php
namespace DigitalPenguin\Commerce_PacklinkPRO\Modules;

use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class PacklinkPRO extends BaseModule {

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

        // Add template path to twig
//        $root = dirname(__DIR__, 2);
//        $this->commerce->view()->addTemplatesPath($root . '/templates/');
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

//        $fields[] = new DescriptionField($this->commerce, [
//            'description' => $this->adapter->lexicon('commerce_packlinkpro.module_description'),
//        ]);

        return $fields;
    }
}
