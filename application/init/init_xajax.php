//example of init_ajax file
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists('xajax'))
{
    require_once(APPPATH.'libraries/xajax_core/xajax'.EXT);
}

$obj =& get_instance();
$obj->xajax = new Xajax();
$obj->ci_is_loaded[] = 'xajax';

?>