<?php

namespace Bolt\Extension\Soapbox\AjaxMultiCTSelectField;

use Bolt\Extension\SimpleExtension;
use Bolt\Extension\Soapbox\AjaxMultiCTSelectField\Provider\AjaxMultiCTSelectFieldProvider;
use Bolt\Asset\File\JavaScript;
use Bolt\Controller\Zone;
use Exception;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The main extension class.
 *
 * @author Robert Hunt <robert.hunt@soapbox.co.uk>
 */
class AjaxMultiCTSelectFieldExtension extends SimpleExtension
{

    /**
     * Pretty extension name
     *
     * @return string
     */
    public function getDisplayName()
    {

        return 'AJAX Multiple Content Type Select Field Type';
    }

    public function getServiceProviders()
    {

        return [
            $this,
            new AjaxMultiCTSelectFieldProvider()
        ];
    }

    protected function registerTwigPaths()
    {

        return [
            'templates/bolt' => [
                'position'  => 'prepend',
                'namespace' => 'bolt'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {

        $asset = new JavaScript();

        $asset->setFileName('ajax-multict-select-field.js')
              ->setZone(Zone::BACKEND)
              ->setLate(true);

        return [$asset];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFilters()
    {

        return [
            'ajaxmultictselectfield' => 'ajaxMultiCTSelectField'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendRoutes(ControllerCollection $collection)
    {

        // GET requests on the AJAX route
        $collection->match('/ajax-multi-ct-select', [
            $this,
            'getAjaxItems'
        ]);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function getAjaxItems(Application $app, Request $request)
    {

        $response = new JsonResponse();
        $response->setData([]);

        if ($request->isMethod('POST')) {
            // Handle the POST data
            $data = $request->request->all();

            if (!array_key_exists('field', $data)) {
                return $response;
            }

            $filter            = '';
            $field             = $data['field']['field'];
            $lookup_type       = explode('/', $field['values'])[0];
            $lookup_field      = explode('/', $field['values'])[1];
            $lookup_field_list = [];
            $order             = 'id';
            $limit             = 30;
            $page              = 1;
            $where_filter      = [];

            if (array_key_exists('q', $data)) {
                $filter = $data['q'];
            }

            if (strpos($lookup_field, ',') !== false) {
                $lookup_field_list = explode(',', $lookup_field);
            }

            if (array_key_exists('sort', $field)) {
                $order = $field['sort'];
            } else if (!empty($lookup_field_list)) {
                $order = $lookup_field_list[0];
            } elseif (!empty($lookup_field)) {
                $order = $lookup_field;
            }

            if ($order === 'contenttype' && !array_key_exists('sort', $field)) {
                if (count($lookup_field_list) >= 2) {
                    $order = $lookup_field_list[1];
                } else if (!empty($lookup_field)) {
                    $order = $lookup_field;
                }
            }

            if (array_key_exists('limit', $field)) {
                $limit = intval($field['limit'], 10);
            }

            if (array_key_exists('page', $data)) {
                $page = intval($data['page'], 10);
            }

            if (array_key_exists('filter', $field)) {
                $where_filter = $field['filter'];
            }

            if (!empty($lookup_field_list)) {
                $value_field = $lookup_field_list;
            } else if (!empty($lookup_field)) {
                $value_field = $lookup_field;
            } else {
                $value_field = 'id';
            }

            $where_filter = array_merge($where_filter, compact('filter', 'limit', 'order', 'page'), [
                'paging'  => true,
                'hydrate' => false
            ]);

            $lookup_items = $app['storage']->getContent($lookup_type, $where_filter, $app['pager']);

            try {
                $values = $this->ajaxMultiCTSelectField($lookup_items, $value_field, array_key_exists('multiple', $field), array_key_exists('keys', $field) ? $field['keys'] : 'id', $lookup_type);
            } catch (Exception $e) {

            }

            if (!empty($values)) {
                $pager = $app['pager']->getPager();

                $values                   = ['results' => $values];
                $values['pager']          = $pager->asArray();
                $values['pager']['limit'] = $limit;

                $response->setData($values);
            }
            /**
             *  {% set lookuptype = option.values|split('/')|slice(0,1)|first %}
             * {% set lookupfield = option.values|split('/')|slice(1,1)|first %}
             * {% if ',' in lookupfield %}
             * {% set lookupfieldlist = lookupfield|split(',') %}
             * {% endif %}
             * {% set sortingorder = field.sort|default(lookupfieldlist|default([])|first)|default(lookupfield)|default('id') %}
             * {% if sortingorder == 'contenttype' %}
             * {% set sortingorder = field.sort|default(lookupfieldlist[1]|default([]))|default(lookupfield) %}
             * {% endif %}
             * {% set querylimit = field.limit|default(500) %}
             * {% set wherefilter = field.filter|default({}) %}
             * {#{% setcontent lookups = lookuptype where wherefilter order sortingorder nohydrate limit querylimit %}#}
             * {% set valuefield = lookupfieldlist|default(lookupfield)|default('id') %}
             * {% set values = lookups|ajaxmultictselectfield(valuefield, option.multiple, field.keys|default('id'), lookuptype) %}
             */
        }

        return $response;
    }

    /**
     * Return a selected field from a contentset.
     *
     * @param array        $content      A Bolt record array
     * @param array|string $field_name   Name of a field, or array of field names to return from each record
     * @param boolean      $start_empty  Whether or not the array should start with an empty element
     * @param string       $key_name     Name of the key in the array
     * @param null|string  $content_type The contenttype string used by the select field, defaults to null
     *
     * @return array
     */
    public function ajaxMultiCTSelectField($content, $field_name, $start_empty = false, $key_name = 'id', $content_type = '')
    {

        $values = $start_empty ? [] : [
            [
                'id'   => '',
                'text' => '(none)'
            ]
        ];

        if (empty($content)) {
            return $values;
        }

        foreach ($content as $c) {
            if (is_string($content_type) && $content_type !== '') {
                $content_type = explode(',', $content_type);
            }

            $element = $c->contenttype['slug'] . '/' . $c->values[$key_name];

            if (is_array($field_name)) {
                foreach ($field_name as $name) {
                    if ($name !== 'contenttype' && isset($c->values[$name])) {
                        $values[] = [
                            'id'   => $element,
                            'text' => $c->contenttype['singular_name'] . ' – ' . $c->values[$name]
                        ];
                    }
                }
            } else if ($field_name === 'contenttype') {
                $values[] = [
                    'id'   => $element,
                    'text' => $c->contenttype['singular_name']
                ];
            } elseif (isset($c->values[$field_name])) {
                $values[] = [
                    'id'   => $element,
                    'text' => $c->values[$field_name]
                ];
            }
        }

        return $values;
    }
}
