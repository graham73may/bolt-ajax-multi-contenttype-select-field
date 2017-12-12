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
        if (
            (isset($this->mapping['data']['multiple']) && ($this->mapping['data']['multiple']))
            ||
            (isset($this->mapping['multiple']) && ($this->mapping['multiple']))
        ) {
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

    /**
     * {@inheritdoc}
     */
    public function persist(QuerySet $queries, $entity)
    {

        $attribute = $this->getMappingAttribute();
        $key       = $this->mapping['fieldname'];

        $qb          = &$queries[0];
        $valueMethod = 'serialize' . ucfirst($this->camelize($attribute));
        $value       = $entity->$valueMethod();

        if ($this instanceof SanitiserAwareInterface && is_string($value)) {
            $isWysiwyg = $this instanceof WysiwygAwareInterface;
            $value     = $this->getSanitiser()
                              ->sanitise($value, $isWysiwyg);
        }

        $type = $this->getStorageType();

        if ($value !== null) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $value = $type->convertToDatabaseValue($value, $this->getPlatform());
        } elseif (isset($this->mapping['default'])) {
            $value = $this->mapping['default'];
        }
        $qb->setValue($key, ':' . $key);
        $qb->set($key, ':' . $key);
        $qb->setParameter($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($data, $entity)
    {

        $key  = $this->mapping['fieldname'];
        $type = $this->getStorageType();
        $val  = isset($data[$key]) ? $data[$key] : null;

        if ($val !== null) {
            if (strpos($val, ',') !== false) {
                $val = explode(',', $val);
            }

            $value = $type->convertToPHPValue($val, $this->getPlatform());
            $this->set($entity, $value);
        }
    }
}
