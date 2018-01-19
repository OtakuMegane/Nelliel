<?php

function nel_generate_main_panel()
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header(array(), $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_panel.html');
    $board_listing = $dom->getElementById('board-select-');
    $board_label = $dom->getElementById('board-label-');

    $prepared = $dbh->prepare('SELECT "board" FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?');
    $boards = $dbh->executePreparedFetchAll($prepared, array($_SESSION['username']), PDO::FETCH_COLUMN);

    if($boards !== false)
    {
        foreach($boards as $board)
        {
            $listing = $board_listing->cloneNode(true);
            $label = $board_label->cloneNode(true);
            $board_listing->parentNode->appendChild($listing);
            $board_listing->parentNode->appendChild($label);
            $listing->changeId('board-select-' . $board);
            $listing->extSetAttribute('value', $board);
            $label->removeAttribute('id');
            $label->extSetAttribute('for', 'board-select-' . $board);
            $label->setContent($board);
        }
    }

    $board_listing->removeSelf();
    $board_label->removeSelf();

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_generate_main_board_panel($board_id)
{
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header(array(), $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_board_panel.html');

    $dom->getElementById('board-name')->setContent($board_id);
    $dom->getElementById('board-id-1')->extSetAttribute('value', $board_id);
    $dom->getElementById('board-id-2')->extSetAttribute('value', $board_id);
    $dom->getElementById('board-id-3')->extSetAttribute('value', $board_id);

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_config_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-settings-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_ban_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-ban-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_post_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-thread-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_user_access', $board_id) ||
    $authorize->get_user_perm($_SESSION['username'], 'perm_role_access', INPUT_BOARD_ID))
    {
        $dom->removeChild($dom->getElementById('select-staff-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_modmode_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-mod-mode'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_regen_index', $board_id))
    {
        $dom->removeChild($dom->getElementById('regen-index-form'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_regen_caches', $board_id))
    {
        $dom->removeChild($dom->getElementById('regen-index-form'));
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}