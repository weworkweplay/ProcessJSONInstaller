<?php

namespace JSONInstaller;

require_once 'Dependency.php';

use \Field;
use \FieldGroup;
use \Template;
use \Page;

class Module {
    
    const PROPERTY_TYPE_SELECTOR = "selector";
    const PROPERTY_TYPE_SELECTOR_ID = "selector_id";
    const PROPERTY_TYPE_DEFAULT = "default";
    
    public $name;
    public $description;
    public $prefix;

    /* Dependencies to install this module */
    public $dependencies;

    /* Things installing this module will create */
    public $fields;
    public $templates;
    public $pages;

    /* Storing unparsed objects until we want to install */
    public $fieldsJSON;
    public $templatesJSON;
    public $pagesJSON;

    public function __construct() {
        $this->dependencies = array();

        $this->fields = array();
        $this->templates = array();
        $this->pages = array();
    }

    /**
     * Create a module from a .json module description
     *
     * @return Module
     **/
    public static function createFromJSON($json) {
        $module = new Module();

        $module->name = $json->name;
        $module->description = $json->description;
        $module->prefix = $json->prefix;

        if ($json->dependencies) {
            foreach ($json->dependencies as $dependencyJSON) {
                $d = new Dependency();
                $d->name = $dependencyJSON->name;
                $d->zip = (isset($dependencyJSON->zip)) ? $dependencyJSON->zip : '';
                $d->core = (isset($dependencyJSON->core)) ? (bool) $dependencyJSON->core : false;

                $module->dependencies[] = $d;
            }
        }

        $module->fieldsJSON = isset($json->fields) ? $json->fields : array();
        $module->templatesJSON = isset($json->templates) ? $json->templates : array();
        $module->pagesJSON = isset($json->pages) ? $json->pages : array();

        return $module;
    }

    protected function prepareTemplates() {
        foreach ($this->templatesJSON as $templateJSON) {
            $t = wire('templates')->get($templateJSON->name);
            $attributes = (!empty($templateJSON->attributes)) ? $templateJSON->attributes : array();
            $hasSelector = false;

            if (!$t) {
                $t = new Template();
                $t->name = $templateJSON->name;
                $t->label = $templateJSON->label;
            }

            $fg = wire('fieldgroups')->get($templateJSON->name);

            if (!$fg) {
                $fg = new FieldGroup();
                $fg->name = $templateJSON->name;
                $fg->save();
            }

            if ($templateJSON->fields) {
                foreach ($templateJSON->fields as $f) {
                    $f = wire('fields')->get($f);

                    if ($f) {
                        $fg->add($f);
                    }
                }
            }

            $fg->save();

            $t->fields = $fg;

            // apply attributes and determine if selectors are used
            $hasSelector = self::applyAttributesOrDefaults($attributes, $t, $hasSelector);

            // only save if selector is present
            // see description in preparePages()
            if($hasSelector) {
                $t->save();
            }

            $this->templates[] = $t;
        }
    }

    protected function prepareFields() {
        foreach ($this->fieldsJSON as $fieldJSON) {
            $name = (!empty($this->prefix) && $fieldJSON->name[0] !== '~') ? $this->prefix . '_' . $fieldJSON->name : $fieldJSON->name;
            $label = (!empty($fieldJSON->label)) ? $fieldJSON->label : '';
            $description = (!empty($fieldJSON->description)) ? $fieldJSON->description : '';
            $attributes = (!empty($fieldJSON->attributes)) ? $fieldJSON->attributes : array();
            $hasSelector = false;

            $name = ($name[0] === '~') ? substr($name, 1) : $name;

            $f = wire('fields')->get($name);

            if (!$f) {
                $f = new Field();
                $f->type = $fieldJSON->type;
                $f->name = $name;
                $f->label = $label;
                $f->description = $description;
            }

            // apply attributes and determine if selectors are used
            $hasSelector = self::applyAttributesOrDefaults($attributes, $f, $hasSelector);

            // only save if selector is present
            // see description in preparePages()
            if($hasSelector) {
                $f->save();
            }

            $this->fields[] = $f;
        }
    }

    protected function preparePages() {
        foreach ($this->pagesJSON as $pageJSON) {
            $p = wire('pages')->get('name=' . $pageJSON->name);
            $attributes = (!empty($pageJSON->attributes)) ? $pageJSON->attributes : array();
            $defaults = (!empty($pageJSON->defaults)) ? $pageJSON->defaults : array();
            $hasSelector = false;

            if (!$p->id) {
                $p = new Page();
                $p->name = $pageJSON->name;

                $p->parent = ($pageJSON->parent) ? wire('pages')->get('/' . $pageJSON->parent . '/') : wire('pages')->get('/');

                $p->template = $pageJSON->template;

                // If set to true, Page:statusHidden, else, Page::statusOn
                $hidden = isset($pageJSON->hidden) ? ((bool) $pageJSON->hidden ? Page::statusHidden : Page::statusOn) : Page::statusOn;

                // If set to true, Page::statusOn, else Page::statusUnpublished
                $published = isset($pageJSON->published) ? ((bool) $pageJSON->published ? Page::statusOn : Page::statusUnpublished) : Page::statusOn;

                $p->addStatus($hidden);
                $p->addStatus($published);

                // apply defaults and determine if selectors are used
                $hasSelector = self::applyAttributesOrDefaults($defaults, $p, $hasSelector);

            }

            // apply attributes and determine if selectors are used
            $hasSelector = self::applyAttributesOrDefaults($attributes, $p, $hasSelector);

            // only save if selector is present
            // saving is necessary if pages in the same loop are referencing
            // each other via selector, since selecting a not yet
            // existing/saved page would not work
            if($hasSelector) {
                $p->save();
            }

            $this->pages[] = $p;
        }
    }

    public static function applyAttributesOrDefaults($attributes, $page, $hasSelector) {
        foreach ($attributes as $attr) {
            
            // DRY some
            $fuel = isset($attr->fuel) ? $attr->fuel : "pages";
            $type = isset($attr->type) ? $attr->type : self::PROPERTY_TYPE_DEFAULT;
            $name = $attr->name;
            $value = $attr->value;
            
            switch (true) {
                
                // if "selector" save the value as a selected page
                case $type === self::PROPERTY_TYPE_SELECTOR:
                    $page->set($name, wire($fuel)->get($value));
                    $hasSelector = true;
                    break;
                
                // if "selector_id" save the value as id of a selected page
                case $type === self::PROPERTY_TYPE_SELECTOR_ID:
                    $page->set($name, wire($fuel)->get($value)->id);
                    $hasSelector = true;
                    break;
                
                // just save the value as is
                default:
                    $page->set($name, $value);
                    break;
            }

        }
        return $hasSelector;
    }

    /**
     * Save all added fields to the database
     *
     * @return void
     **/
    public function install() {
        foreach ($this->dependencies as $dependency) {
            $dependency->install();
        }

        $this->prepareFields();

        foreach ($this->fields as $field) {
            $field->save();
        }

        // By first creating the fields, the script allows to specify new fields
        // and use them in a new template in the same JSON file
        $this->prepareTemplates();

        foreach ($this->templates as $template) {
            $template->save();
        }

        // By first creating the templates and fields,
        // you can use a new template with new fields in a new page
        // from the same JSON
        $this->preparePages();

        foreach ($this->pages as $page) {
            $page->save();
        }
    }

    /**
     * Add the created fields to an existing template
     *
     * @return void
     **/
    public function addToTemplate(Template $template) {
        foreach ($this->fields as $field) {
            $template->fields->add($field->name);
        }

        $template->fields->save();
    }
}
