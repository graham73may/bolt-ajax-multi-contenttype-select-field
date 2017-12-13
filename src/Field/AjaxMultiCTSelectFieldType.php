<?php

namespace Bolt\Extension\Soapbox\AjaxMultiCTSelectField\Field;

use Bolt\Storage\Field\Sanitiser\SanitiserAwareInterface;
use Bolt\Storage\Field\Sanitiser\WysiwygAwareInterface;
use Bolt\Storage\Field\Type\FieldTypeBase;
use Bolt\Storage\QuerySet;
use Doctrine\DBAL\Types\Type;

/**
 * This class extends the base field type and looks after serializing and hydrating the field
 * on save and load.
 *
 * @author Robert Hunt <robert.hunt@soapbox.co.uk>
 */
class AjaxMultiCTSelectFieldType extends FieldTypeBase
{

    public function getName()
    {

        return 'ajaxmultictselect';
    }

    public function getStorageType()
    {

        if ((isset($this->mapping['data']['multiple']) && ($this->mapping['data']['multiple'])) || (isset($this->mapping['multiple']) && ($this->mapping['multiple']))) {
            return Type::getType('json_array');
        }

        return Type::getType('text');
    }

    public function getStorageOptions()
    {

        return [
            'default' => ''
        ];
    }
}
