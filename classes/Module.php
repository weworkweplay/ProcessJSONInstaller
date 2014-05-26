<?php

namespace JSONInstaller;

require_once 'Dependency.php';
require_once 'SkippedItem.php';

use \Field;
use \FieldGroup;
use \Template;
use \Page;
use \NullPage;

class Module {

    const PROPERTY_TYPE_SELECTOR = 'selector';
    const PROPERTY_TYPE_SELECTOR_ID = 'selector_id';
    const PROPERTY_TYPE_DEFAULT = 'default';
    const EMPTY_JSON_PARENT = '"EMPTY PARENT"';
    const EMPTY_JSON_TEMPLATE = '"EMPTY TEMPLATE"';

    public $name;
    public $description;
    public $prefix;

    /* Dependencies to install this module */
    public $dependencies;
    public $installedDependencies;
    public $uninstalledDependencies;

    /* JSON Dependencies to install this module */
    public $jsonDependencies;
    public $installedJsonDependencies;
    public $uninstalledJsonDependencies;

    /* Things installing this module will create */
    public $fields;
    public $templates;
    public $pages;

    /* Things uninstalling this module will delete */
    public $deletedFields;
    public $deletedTemplates;
    public $deletedPages;

    /* Things un-/installing this module will skip due to various reasons */
    public $skippedItems;

    public $fieldsHaveSelectors = false;
    public $templatesHaveSelectors = false;
    public $pagesHaveSelectors = false;

    /* Storing unparsed objects until we want to install */
    public $fieldsJSON;
    public $templatesJSON;
    public $pagesJSON;

    /**
     * yields the slug like "some-module", same as filename
     * TODO: it's would be more consistent if this property was calles $name
     * and the current $name property would be renamed to $title, like in PW
     * @var string
     */
    public $slug;

    /**
     * Assoc array to keep track of modules installed in one go.
     * Important when modules reference other modules as dependencies
     * to prevent circular references and to provide complete output for the user
     * @var array
     */
    public static $installedModules;

    /**
     * Assoc array to keep track of modules uninstalled in one go.
     * Important to provide complete output for the user
     * @var array
     */
    public static $uninstalledModules;

    /**
     * Assoc array to keep track of modules dry run uninstalled in one go.
     * Important to provide complete output for the user
     * @var array
     */
    public static $dryRunUninstalledModules;

    public function __construct() {

        $this->dependencies = array();
        $this->installedDependencies = array();
        $this->uninstalledDependencies = array();

        $this->jsonDependencies = array();
        $this->installedJsonDependencies = array();
        $this->uninstalledJsonDependencies = array();

        $this->fields = array();
        $this->templates = array();
        $this->pages = array();

        $this->deletedFields = array();
        $this->deletedTemplates = array();
        $this->deletedPages = array();

        $this->skippedItems = array();

        // only create once for all instances
        if (self::$installedModules === null) {
            self::$installedModules = array();
        }

        // only create once for all instances
        if (self::$uninstalledModules === null) {
            self::$uninstalledModules = array();
        }

        // only create once for all instances
        if (self::$dryRunUninstalledModules === null) {
            self::$dryRunUninstalledModules = array();
        }
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

                $d->zip = (isset($dependencyJSON->zip)) ? $dependencyJSON->zip : '';
                $d->core = (isset($dependencyJSON->core)) ? (bool) $dependencyJSON->core : false;
                $d->force = (isset($dependencyJSON->force)) ? (bool) $dependencyJSON->force : false;

                $d->name = $dependencyJSON->name;

                $module->dependencies[] = $d;
            }
        }

        if (isset($json->jsonDependencies)) {
            foreach ($json->jsonDependencies as $jsonDependencyJSON) {

                if ($jsonModule = JSONLoader::loadModule($jsonDependencyJSON)) {
                    // TODO: documentation
                    if ($jsonModule instanceof Module) {
                        $module->jsonDependencies[] = JSONLoader::loadModule($jsonDependencyJSON);
                    }
                }

            }
        }

        $module->fieldsJSON = isset($json->fields) ? $json->fields : array();
        $module->templatesJSON = isset($json->templates) ? $json->templates : array();
        $module->pagesJSON = isset($json->pages) ? $json->pages : array();

        return $module;
    }

    /**
     * prepare the templates
     *
     * @return void
     */
    protected function installTemplates() {

        // empty array, for running this method more than once
        $this->templates = array();

        foreach ($this->templatesJSON as $templateJSON) {
            $t = wire('templates')->get($templateJSON->name);
            $attributes = (!empty($templateJSON->attributes)) ? $templateJSON->attributes : array();
            $hasSelector = false;

            if (!$t) {
                $t = new Template();
                $t->name = $templateJSON->name;
                if (isset($templateJSON->label)) {
                    $t->label = $templateJSON->label;
                }
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
            if ($hasSelector) {
                $this->templatesHaveSelectors = true;
            }

            $t->save();

            $this->templates[] = $t;
        }
    }

    /**
     * prepare the fields
     *
     * @return void
     */
    protected function installFields() {

        // empty array, for running this method more than once
        $this->fields = array();

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
            if ($hasSelector) {
                $this->fieldsHaveSelectors = true;
            }

            $f->save();

            $this->fields[] = $f;
        }
    }

    /**
     * install dependencies
     *
     * @return void
     */
    protected function installDependencies() {
        foreach ($this->dependencies as $dependency) {
            if ($dependency->install()) {
                $this->installedDependencies[] = $dependency;
            }
        }
    }

    /**
     * install dependencies
     *
     * @return void
     */
    protected function installJsonDependencies() {
        foreach ($this->jsonDependencies as $jsonDependency) {
            if ($jsonDependency->install()) {
                $this->installedJsonDependencies[] = $jsonDependency;
            }
        }
    }

    /**
     * prepare the pages
     *
     * @return void
     */
    protected function installPages() {

        // empty array, for running this method more than once
        $this->pages = array();

        $templates = wire('templates');

        foreach ($this->pagesJSON as $pageJSON) {

            $skipped = false;

            $p = wire('pages')->get('name=' . $pageJSON->name . ',template=' . $pageJSON->template);
            $attributes = (!empty($pageJSON->attributes)) ? $pageJSON->attributes : array();
            $defaults = (!empty($pageJSON->defaults)) ? $pageJSON->defaults : array();
            $hasSelector = false;

            if (!$p->id) {
                $p = new Page();
            }

            $p->name = $pageJSON->name;

            $p->parent = (isset($pageJSON->parent)) ? wire('pages')->get('/' . $pageJSON->parent . '/') : wire('pages')->get('/');

            if ($templates->get($pageJSON->template)) {
                $p->template = $pageJSON->template;
                // If set to true, Page:statusHidden, else, Page::statusOn
                $hidden = isset($pageJSON->hidden) ? ((bool) $pageJSON->hidden ? Page::statusHidden : Page::statusOn) : Page::statusOn;

                // If set to true, Page::statusOn, else Page::statusUnpublished
                $published = isset($pageJSON->published) ? ((bool) $pageJSON->published ? Page::statusOn : Page::statusUnpublished) : Page::statusOn;

                $p->addStatus($hidden);
                $p->addStatus($published);

                // apply defaults and determine if selectors are used
                $hasSelector = self::applyAttributesOrDefaults($defaults, $p, $hasSelector);

                // apply attributes and determine if selectors are used
                $hasSelector = self::applyAttributesOrDefaults($attributes, $p, $hasSelector);
                if ($hasSelector) {
                    $this->pagesHaveSelectors = true;
                }
            } else {
                $altTemplate = isset($pageJSON->template) ? $pageJSON->template : self::EMPTY_JSON_TEMPLATE;
                $this->skippedItems[] = new SkippedItem(
                    $pageJSON->name,
                    SkippedItem::TYPE_PAGE,
                    $reason = 'Template "' . $altTemplate . '" does not exist',
                    SkippedItem::PROCESS_INSTALL,
                    $this
                );
                $skipped = true;
            }


            if ($p->parent instanceof NullPage) {
                $altParent = isset($pageJSON->parent) ? $pageJSON->parent : self::EMPTY_JSON_PARENT;
                $this->skippedItems[] = new SkippedItem(
                    $pageJSON->name,
                    SkippedItem::TYPE_PAGE,
                    $reason = 'Parent "' . $altParent . '" does not exist',
                    SkippedItem::PROCESS_INSTALL,
                    $this
                );
                $skipped = true;
            }

            if (!$skipped) {
                $p->save();
                $this->pages[] = $p;
            }

        }
    }

    /**
     * delete all pages defined in this module, which are not marked as "prefab"
     *
     * @param  boolean $dryRun when true, nothing gets deleted
     * @return void
     **/
    protected function deletePages($dryRun = false) {
        $pages = wire('pages');
        // empty array, for running this method more than once
        $this->deletedPages = array();

        // uninstall order must be reverse of install order
        $pagesJSONReversed = array_reverse($this->pagesJSON);

        foreach ($pagesJSONReversed as $pageJSON) {

            $p = wire('pages')->get('name=' . $pageJSON->name . ',template=' . $pageJSON->template);

            if (isset($p->id) && $p->id) {
                $this->addPageAndAllChildrenToDeletedPages($p);

                if (!$dryRun) {
                    $pages->delete($p, $recursive = true);
                }
            }
        }
    }

    /**
     * add given page and all children to $this->deletedPages recursively
     * @param Page $page
     */
    protected function addPageAndAllChildrenToDeletedPages($page) {
        if (!in_array($page, $this->deletedPages)) {
            $this->deletedPages[] = $page;
        }
        foreach ($page->children as $child) {
            $this->addPageAndAllChildrenToDeletedPages($child);
        }
    }

    /**
     * delete all templates defined in this module, which are not marked as "prefab"
     *
     * @param  boolean $dryRun when true, nothing gets deleted
     * @return void
     **/
    protected function deleteTemplates($dryRun = false) {

        // empty array, for running this method more than once
        $this->deletedTemplates = array();

        $templates = wire('templates');
        $fieldgroups = wire('fieldgroups');

        foreach ($this->templatesJSON as $templateJSON) {

            $t = $templates->get($templateJSON->name);
            $skip = isset($templateJSON->prefab) && $templateJSON->prefab === true;

            if (isset($t) && $t->id && !$skip) {
                if (!$dryRun) {
                    $fg = $t->fieldgroup;
                    $templates->delete($t, true);
                    $fieldgroups->delete($fg, true);
                }
                $this->deletedTemplates[] = $t;
            }
        }
    }

    /**
     * delete all fields defined in this module, which are not marked as "prefab"
     *
     * @param  boolean $dryRun when true, nothing gets deleted
     * @return void
     **/
    protected function deleteFields($dryRun = false) {

        // empty array, for running this method more than once
        $this->deletedFields = array();

        $fields = wire('fields');

        foreach ($this->fieldsJSON as $fieldJSON) {

            $name = (!empty($this->prefix) && $fieldJSON->name[0] !== '~') ? $this->prefix . '_' . $fieldJSON->name : $fieldJSON->name;
            $name = ($name[0] === '~') ? substr($name, 1) : $name;

            $f = $fields->get($name);
            $skip = isset($fieldJSON->prefab) && $fieldJSON->prefab === true;

            if (isset($f->id) && $f->id && !$skip) {
                if (!$dryRun) {
                    self::removeFieldFromFieldgroups($f);
                    $fields->delete($f, true);
                }
                $this->deletedFields[] = $f;
            }
        }
    }

    /**
     * uninstall all dependencies defined in this module
     *
     * @param  boolean $dryRun when true, nothing gets uninstalled
     * @return void
     **/
    protected function uninstallDependencies($dryRun = false) {

        // empty array, for running this method more than once
        $this->uninstalledDependencies = array();

        $fields = wire('fields');

        foreach ($this->dependencies as $dependency) {
            if ($dependency->isInstalled()) {
                if (!$dryRun) {
                    $dependency->uninstall();
                }
                $this->uninstalledDependencies[] = $dependency;
            }
        }
    }

    /**
     * uninstall json dependencies
     *
     * @return void
     */
    protected function uninstallJsonDependencies($dryRun = false) {
        foreach ($this->jsonDependencies as $jsonDependency) {
            if ($jsonDependency->hasDeletableItems($forceDryRun = true)) {
                $jsonDependency->uninstall($dryRun);
            }
        }
    }

    /**
     * checks if either of "deletedPages", "deletedTemplates" or "deletedFields"
     * is not empty after a dry run unistall process
     *
     * @param  boolean $forceDryRun it forces the dry run to collect the deleted items,
     * which is not always necessary if a dry run has been called from outside already
     * @return boolean
     **/
    public function hasDeletableItems($forceDryRun = false) {

        if ($forceDryRun) {
            $this->uninstall($dryRun = true);
        }

        return !(empty($this->deletedPages) && empty($this->deletedTemplates) && empty($this->deletedFields));
    }

    protected static function removeFieldFromFieldgroups($field) {

        $fieldgroups = wire('fieldgroups');

        foreach ($fieldgroups as $fieldgroup) {

            $fieldExists = $fieldgroup->has($field);

            if ($fieldExists) {
                $fieldgroup->remove($field);
                $fieldgroup->save();
            }
        }
    }

    /**
     * Interate through the attributes and assigns their values to the given $page
     *
     * @param  array $attributes
     * @param  Page $page
     * @param  boolean $hasSelector
     * @return boolean
     */
    public static function applyAttributesOrDefaults($attributes, $page, $hasSelector) {
        foreach ($attributes as $attr) {

            // DRY some
            $wire = isset($attr->fuel) ? wire($attr->fuel) : wire('pages');
            $type = isset($attr->type) ? $attr->type : self::PROPERTY_TYPE_DEFAULT;
            $name = isset($attr->name) ? $attr->name : $attr->field;
            $value = $attr->value;

            switch (true) {

                // if 'selector' save the value as a selected page
                case $type === self::PROPERTY_TYPE_SELECTOR:
                    $page->set($name, $wire->get($value));
                    $hasSelector = true;
                    break;

                // if 'selector_id' save the value as id of a selected page
                case $type === self::PROPERTY_TYPE_SELECTOR_ID:
                    $page->set($name, $wire->get($value)->id);
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

        // install only if not installed already
        if (isset(self::$installedModules[$this->slug])) {
            return;
        }
        self::$installedModules[$this->slug] = $this;

        // if install is run more than once, skippedItems should be emptied
        $this->skippedItems = array();

        $this->installDependencies();
        $this->installJsonDependencies();

        $this->installFields();
        $this->installTemplates();
        $this->installPages();

        // run instalation again if necessary
        if ($this->moduleHasSelectors()) {
            // if install is run more than once, skippedItems should be emptied
            // because skipped items may not be valid anymore
            $this->skippedItems = array();
        }

        // rerun template installation, because they may reference
        // items via selector that are created afterwards
        // for example: for page fields
        if ($this->fieldsHaveSelectors) {
            $this->installFields();
        }

        // rerun template installation, because they may reference
        // items via selector that are created afterwards
        if ($this->templatesHaveSelectors) {
            $this->installTemplates();
        }

        // rerun template installation, because they may reference
        // items via selector that are created afterwards
        if ($this->pagesHaveSelectors) {
            $this->installPages();
        }

    }

    /**
     * determine if any of the fields, templates or pages have selectors
     * @return boolean
     */
    protected function moduleHasSelectors() {
        return ($this->fieldsHaveSelectors || $this->templatesHaveSelectors || $this->pagesHaveSelectors);
    }

    /**
     * Delete everything, but dependecies (modules)
     *
     * @param  boolean $dryRun if true, it performs a dry run and does not delete anything
     * @return void
     */
    public function uninstall($dryRun = false) {

        // TODO: not pretty, perhaps merge with new JSONLoader logic
        if ($dryRun) {
            if (isset(self::$dryRunUninstalledModules[$this->slug])) {
                return;
            }
            self::$dryRunUninstalledModules[$this->slug] = $this;
        } else {
            if (isset(self::$uninstalledModules[$this->slug])) {
                return;
            }
            self::$uninstalledModules[$this->slug] = $this;
        }

        $this->deletePages($dryRun);
        $this->deleteTemplates($dryRun);
        $this->deleteFields($dryRun);
        $this->uninstallJsonDependencies($dryRun);

        // disabled for now, maybe forever
        // $this->uninstallDependencies($dryRun);
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

    /**
     * globally accessible for outputting a list of all skipped item
     * @return array all skipped items of all modules
     */
    public static function getAllSkippedItems() {
        $skippedItems = array();
        foreach (self::$installedModules as $module) {
            foreach ($module->skippedItems as $skippedItem) {
                $skippedItems[] = $skippedItem;
            }
        }
        return $skippedItems;
    }
}
