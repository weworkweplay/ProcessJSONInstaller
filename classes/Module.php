<?php

namespace WWWP;

require_once 'Dependency.php';

use \Field;
use \FieldGroup;
use \Template;
use \Page;

class Module
{
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

    public function __construct()
    {
        $this->dependencies = array();

        $this->fields = array();
        $this->templates = array();
        $this->pages = array();
    }

    /**
     * Create a module from a .json module description
     *
     * @return Module
     * @author Pieter Beulque
     **/
    public static function createFromJSON($json)
    {
        $module = new Module();

        $module->name = $json->name;
        $module->description = $json->description;
        $module->prefix = $json->prefix;

        // List dependencies
        if ($json->dependencies) {
            foreach ($json->dependencies as $dependencyJSON) {
                $d = new Dependency();
                $d->name = $dependencyJSON->name;
                $d->github = ($dependencyJSON->github) ? $dependencyJSON->github : '';
                $d->core = ($dependencyJSON->core) ? (bool) $dependencyJSON->core : false;

                $module->dependencies[] = $d;
            }
        }

        $module->fieldsJSON = $json->fields;
        $module->templatesJSON = $json->templates;
        $module->pagesJSON = $json->pages;

        return $module;
    }

    protected function prepareTemplates()
    {
        foreach ($this->templatesJSON as $templateJSON) {
            $t = wire('templates')->get($templateJSON->name);

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

            $this->templates[] = $t;
        }
    }

    protected function prepareFields()
    {
        foreach ($this->fieldsJSON as $fieldJSON) {
            $name = (!empty($this->prefix)) ? $this->prefix . '_' . $fieldJSON->name : $fieldJSON->name;
            $label = (!empty($fieldJSON->label)) ? $fieldJSON->label : '';
            $description = (!empty($fieldJSON->description)) ? $fieldJSON->description : '';
            $attributes = (!empty($fieldJSON->attributes)) ? $fieldJSON->attributes : array();

            $f = wire('fields')->get($name);

            if (!$f) {
                $f = new Field();
                $f->type = $fieldJSON->type;
                $f->name = $name;
                $f->label = $label;
                $f->description = $description;
            }

            foreach ($attributes as $attr) {
                $f->set($attr->name, $attr->value);
            }

            $this->fields[] = $f;
        }
    }

    protected function preparePages()
    {
        foreach ($this->pagesJSON as $pageJSON) {
            $p = wire('pages')->get('/' . $pageJSON->name . '/');

            if (!$p->id) {
                $p = new Page();
                $p->name = $pageJSON->name;
                $p->parent = ($pageJSON->parent) ? wire('pages')->get('/' . $pageJSON->parent . '/') : wire('pages')->get('/');
                $p->template = $pageJSON->template;

                // If set to true, Page:statusHidden, else, Page::statusOn
                $hidden = !is_null($pageJSON->hidden) ? ((bool) $pageJSON->hidden ? Page::statusHidden : Page::statusOn) : Page::statusOn;

                // If set to true, Page::statusOn, else Page::statusUnpublished
                $published = !is_null($pageJSON->published) ? ((bool) $pageJSON->published ? Page::statusOn : Page::statusUnpublished) : Page::statusOn;
                
                $p->addStatus($hidden);
                $p->addStatus($published);

                if ($pageJSON->defaults) {
                    foreach ($pageJSON->defaults as $default) {
                        $p->set($default->field, $default->value);
                    }
                }
            }

            $this->pages[] = $p;
        }
    }

    /**
     * Save all added fields to the database
     *
     * @return void
     * @author Pieter Beulque
     **/
    public function install()
    {
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
     * @author Pieter Beulque
     **/
    public function addToTemplate(Template $template)
    {
        foreach ($this->fields as $field) {
            $template->fields->add($field->name);
        }

        $template->fields->save();
    }
}