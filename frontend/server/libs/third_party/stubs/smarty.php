<?php

abstract class Smarty_Internal_Data {
    /**
     * assigns a Smarty variable
     *
     * @param string  $tpl_var the template variable name(s)
     * @param mixed   $value   the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     *
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for
     *                              chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false) {}

    /**
     * Returns a single or all config variables
     *
     * @api  Smarty::getConfigVars()
     * @link http://www.smarty.net/docs/en/api.get.config.vars.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param ?string                                                 $varname        variable name or null
     * @param boolean                                                 $search_parents include parent templates?
     *
     * @return mixed variable value or or array of variables
     */
    public function getConfigVars(\Smarty_Internal_Data $data, $varname = null, boolean $search_parents = true) {}
}

abstract class Smarty_Internal_TemplateBase extends Smarty_Internal_Data {
    /**
     * displays a Smarty template
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   cache id to be used with this template
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     *
     * @throws \Exception
     * @throws \SmartyException
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) : void {}
}

class Smarty_Internal_Template extends Smarty_Internal_TemplateBase {
}

class Smarty extends Smarty_Internal_TemplateBase {
}
