<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelMain extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();
        $dotdot = ($parameters['dotdot']) ?? '';
        $this->startTimer();
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Main Panel')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        if ($boards !== false)
        {
            foreach ($boards as $board)
            {
                $board_data['board_url'] = MAIN_SCRIPT . '?module=main-panel&board_id=' . $board['board_id'];
                $board_data['board_id'] = '/' . $board['board_id'] . '/';
                $this->render_data['board_list'][] = $board_data;
            }
        }

        $this->render_data['module_manage_boards'] = $user->domainPermission($this->domain, 'perm_manage_boards_access');
        $this->render_data['manage_boards_url'] = MAIN_SCRIPT . '?module=manage-boards';
        $this->render_data['module_users'] = $user->domainPermission($this->domain, 'perm_user_access');
        $this->render_data['users_url'] = MAIN_SCRIPT . '?module=users';
        $this->render_data['module_roles'] = $user->domainPermission($this->domain, 'perm_role_access');
        $this->render_data['roles_url'] = MAIN_SCRIPT . '?module=roles';
        $this->render_data['module_site_settings'] = $user->domainPermission($this->domain, 'perm_site_config_access');
        $this->render_data['site_settings_url'] = MAIN_SCRIPT . '?module=site-settings';
        $this->render_data['module_file_filters'] = $user->domainPermission($this->domain, 'perm_file_filters_access');
        $this->render_data['file_filters_url'] = MAIN_SCRIPT . '?module=file-filters';
        $this->render_data['module_board_defaults'] = $user->domainPermission($this->domain,
                'perm_board_defaults_access');
        $this->render_data['board_defaults_url'] = MAIN_SCRIPT . '?module=board-defaults';
        $this->render_data['module_reports'] = $user->domainPermission($this->domain, 'perm_reports_access');
        $this->render_data['reports_url'] = MAIN_SCRIPT . '?module=reports';
        $this->render_data['module_templates'] = $user->domainPermission($this->domain, 'perm_templates_access');
        $this->render_data['templates_url'] = MAIN_SCRIPT . '?module=templates';
        $this->render_data['module_filetypes'] = $user->domainPermission($this->domain, 'perm_filetypes_access');
        $this->render_data['filetypes_url'] = MAIN_SCRIPT . '?module=filetypes';
        $this->render_data['module_styles'] = $user->domainPermission($this->domain, 'perm_styles_access');
        $this->render_data['styles_url'] = MAIN_SCRIPT . '?module=styles';
        $this->render_data['module_permissions'] = $user->domainPermission($this->domain, 'perm_permissions_access');
        $this->render_data['permissions_url'] = MAIN_SCRIPT . '?module=permissions';
        $this->render_data['module_icon_sets'] = $user->domainPermission($this->domain, 'perm_icon_sets_access');
        $this->render_data['icon_sets_url'] = MAIN_SCRIPT . '?module=icon-sets';
        $this->render_data['module_news'] = $user->domainPermission($this->domain, 'perm_news_access');
        $this->render_data['news_url'] = MAIN_SCRIPT . '?module=news';
        $this->render_data['module_extract_gettext'] = $user->domainPermission($this->domain, 'perm_extract_gettext');
        $this->render_data['extract_gettext_url'] = MAIN_SCRIPT . '?module=language&action=extract-gettext';
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/main_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}