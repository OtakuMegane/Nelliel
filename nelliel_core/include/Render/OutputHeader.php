<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputHeader extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function general(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $site_domain = new \Nelliel\Domains\DomainSite($this->database);
        $manage_headers = $parameters['manage_headers'] ?? array();
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);
        $this->render_data['show_styles'] = ($parameters['show_styles']) ?? true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);

        if (isset($parameters['use_site_titles']) && $parameters['use_site_titles'])
        {
            $this->render_data['is_site_header'] = true;
            $this->render_data['name'] = $site_domain->setting('name');
            $this->render_data['slogan'] = $site_domain->setting('slogan');
            $this->render_data['banner_url'] = $site_domain->setting('banner');
        }
        else
        {
            $this->render_data['is_site_header'] = false;
        }

        $this->render_data['is_board_header'] = false;
        $this->render_data['page_title'] = $site_domain->setting('name');

        if (!empty($manage_headers))
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';

            if ($this->domain->id() !== '_site_')
            {
                $this->render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $output = $this->output('header', $data_only, true);
        return $output;
    }

    public function board(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $manage_headers = $parameters['manage_headers'] ?? array();
        $treeline = $parameters['treeline'] ?? array();
        $index_render = $parameters['index_render'] ?? false;
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);
        $this->render_data['show_styles'] = ($parameters['show_styles']) ?? true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['board_navigation'] = $output_navigation->boardLinks([], true);
        $this->render_data['name'] = ($this->domain->setting('show_name')) ? $this->domain->setting('name') : '';
        $this->render_data['slogan'] = ($this->domain->setting('show_slogan')) ? $this->domain->setting('slogan') : '';
        $this->render_data['banner_url'] = ($this->domain->setting('show_banner')) ? $this->domain->setting('banner') : '';
        $this->render_data['is_site_header'] = false;
        $this->render_data['is_board_header'] = true;

        if (!$index_render && !empty($treeline))
        {
            if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
            {
                $this->render_data['page_title'] = $this->domain->setting('name') . ' > Thread #' .
                        $treeline[0]['post_number'];
            }
            else
            {
                $this->render_data['page_title'] = $this->domain->setting('name') . ' > ' . $treeline[0]['subject'];
            }
        }
        else
        {
            $this->render_data['page_title'] = $this->domain->setting('name');
        }

        if ($this->render_data['show_manage_headers'])
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';

            if ($this->domain->id() !== '_site_')
            {
                $this->render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $output = $this->output('header', $data_only, true);
        return $output;
    }
}