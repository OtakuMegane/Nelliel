<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

ignore_user_abort(TRUE);
require_once BASE_PATH . '/' . BOARD_FILES . 'libraries/portable-utf8/portable-utf8.php';
require_once INCLUDE_PATH . 'setup/setup.php';
setup_check();
generate_auth_file();
require_once INCLUDE_PATH . 'authorize.php';
$authorize = new nel_authorization();
require_once INCLUDE_PATH . 'language.php';
require_once INCLUDE_PATH . 'template.php';
require_once INCLUDE_PATH . 'render.php';

//nel_render_add_default('dotdot', '');
$template_info = array();
$dataforce = array();
$enabled_types = array();

$dataforce['page_gen'] = 'main';
$dataforce['archive_update'] = FALSE;
$dataforce['post_links'] = '';
$dataforce['sp_field1'] = (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD1')])) ? $_POST[nel_stext('TEXT_SPAMBOT_FIELD1')] : NULL;
$dataforce['sp_field2'] = (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD2')])) ? $_POST[nel_stext('TEXT_SPAMBOT_FIELD2')] : NULL;
$dataforce['mode'] = NULL;
$dataforce['get_mode'] = NULL;

if (!empty($_POST))
{
    if (isset($_POST['mode']))
    {
        $mode = explode('->', $_POST['mode']);
    }
    else
    {
        $mode = array();
    }

    $dataforce['mode'] = (isset($mode[0])) ? $mode[0] : NULL;
    $dataforce['sub_mode'] = (isset($mode[1])) ? $mode[1] : NULL;
    $dataforce['mode_action'] = (isset($mode[2])) ? $mode[2] : NULL;
    $dataforce['mode_extra'] = (isset($_POST['mode2'])) ? $_POST['mode2'] : NULL;
    $dataforce['admin_mode'] = (isset($_POST['adminmode'])) ? $_POST['adminmode'] : NULL;
    $dataforce['admin_pass'] = (isset($_POST['super_sekrit'])) ? $_POST['super_sekrit'] : NULL;
    $dataforce['username'] = (isset($_POST['username'])) ? $_POST['username'] : NULL;
    $dataforce['usrdel'] = (isset($_POST['usrdel'])) ? $_POST['usrdel'] : NULL;
    $dataforce['expand_thread'] = (isset($_POST['expand_thread'])) ? $_POST['expand_thread'] : NULL;
    $dataforce['delpost'] = (isset($_POST['delpost'])) ? TRUE : FALSE;
    $dataforce['banpost'] = (isset($_POST['banpost'])) ? TRUE : FALSE;
    $dataforce['banid'] = (isset($_POST['banid']) && is_numeric($_POST['banid'])) ? (int) $_POST['banid'] : NULL;
    $dataforce['banreason'] = (isset($_POST['banreason'])) ? $_POST['banreason'] : NULL;
    $dataforce['banip'] = (isset($_POST['ban_ip'])) ? $_POST['ban_ip'] : NULL;
    $dataforce['timedays'] = (isset($_POST['timedays'])) && is_numeric($_POST['timedays']) ? (int) $_POST['timedays'] : NULL;
    $dataforce['timehours'] = (isset($_POST['timehours'])) && is_numeric($_POST['timehours']) ? (int) $_POST['timehours'] : NULL;
    $dataforce['response_to'] = (isset($_POST['response_to']) && is_numeric($_POST['response_to'])) ? (int) $_POST['response_to'] : NULL;
    $dataforce['only_delete_file'] = (isset($_POST['onlyimgdel'])) ? TRUE : FALSE;
}

if (!empty($_GET))
{
    $dataforce['get_mode'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
    $dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
    $dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
    $dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
    $dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
}

$link_updates = '';
$fgsfds = array('noko' => FALSE, 'noko_topic' => 0, 'sage' => FALSE, 'sticky' => FALSE);
$link_resno = 0;

// Load caching routines and handle current cache files
require_once INCLUDE_PATH . 'cache-functions.php'; // I liek cache
$dataforce['max_pages'] = BS_PAGE_LIMIT;
?>
