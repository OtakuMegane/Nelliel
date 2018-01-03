<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

function nel_thread_panel($dataforce, $authorize)
{
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_post_access'))
    {
        nel_derp(350, nel_stext('ERROR_350'));
    }

    if ($mode === 'admin->thread->update')
    {
        $updates = nel_thread_updates($dataforce);
        nel_regen_threads($dataforce, true, $updates);
        nel_regen_index($dataforce);
    }

    if(isset($_POST['expand_thread']))
    {
        $expand_data = explode(' ', $_POST['expand_thread']);
        nel_render_thread_panel_expand($expand_data[1]);
    }
    else
    {
        nel_render_thread_panel_main();
    }

}
