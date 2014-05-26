<?php

namespace JSONInstaller;
/**
 * Object that holds info about a skipped item on install or uninstall
 */
class SkippedItem {

    /**
     * holding the type identifier (what type was skipped)
     */
    const TYPE_PAGE = "Page";
    const TYPE_FIELD = "Field";
    const TYPE_TEMPLATE = "Template";

    /**
     * holding the process identifier (in which process it was skipped)
     */
    const PROCESS_INSTALL = "on install";
    const PROCESS_UNINSTALL = "on uninstall";

    /**
     * @var string
     */
    public $name;

    /**
     * type of skipped item
     * @var string
     */
    public $type;

    /**
     * reason why it was skipped
     * @var string
     */
    public $reason;

    /**
     * in which process it was skipped
     * @var string
     */
    public $process;

    /**
     * in which module it was skipped
     * @var Module
     */
    public $module;

    public function __construct($name, $type, $reason, $process, $module) {
        $this->name = $name;
        $this->type = $type;
        $this->reason = $reason;
        $this->process = $process;
        $this->module = $module;
    }
}
