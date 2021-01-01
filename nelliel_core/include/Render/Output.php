<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

abstract class Output
{
    protected $dom;
    protected $domain;
    protected $site_domain;
    protected $database;
    protected $render_core;
    protected static $render_cores = array();
    protected $render_data = array();
    protected $file_handler;
    protected $output_filter;
    protected $timer_start = 0;
    protected $timer_end = 0;
    protected $core_id;
    protected $static_output = false;
    protected $write_mode = false;
    protected $template_substitutes;

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->site_domain = new \Nelliel\Domains\DomainSite(nel_database());
        $this->file_handler = nel_utilities()->fileHandler();
        $this->output_filter = new Filter();
        $this->template_substitutes = new TemplateSubstitutes();
    }

    // Standard setup when beginning a render
    protected function renderSetup()
    {
        $this->render_data = array();
        $this->startTimer(); // Begin rendering timer
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale()); // Convert underscore notation to hyphen for HTML
    }

    protected function selectRenderCore(string $core_id)
    {
        if ($core_id === 'mustache')
        {
            self::$render_cores['mustache'] = self::$render_cores['mustache'] ?? new RenderCoreMustache($this->domain);
            $this->render_core = self::$render_cores['mustache'];
        }
        else if ($core_id === 'DOM')
        {
            self::$render_cores['DOM'] = self::$render_cores['DOM'] ?? new RenderCoreDOM();
            $this->render_core = self::$render_cores['DOM'];
        }
        else
        {
            return;
        }

        $this->core_id = $core_id;
    }

    protected function startTimer(int $time_offset = 0)
    {
        $start = microtime(true);
        $this->timer_start = $start - $time_offset;
        return $start;
    }

    protected function endTimer(bool $rounded = true, int $precision = 4)
    {
        $this->timer_end = microtime(true);

        if ($rounded)
        {
            return number_format($this->timer_end - $this->timer_start, $precision);
        }
        else
        {
            return $this->timer_end - $this->timer_start;
        }
    }

    protected function output(string $template, bool $data_only, bool $translate, array $render_data = array(),
            $dom = null)
    {
        $output = null;
        $render_data = (empty($render_data)) ? $this->render_data : $render_data;
        $dom = (is_null($dom)) ? $this->dom : $dom;
        $substitutes = $this->template_substitutes->getAll();

        if ($this->core_id === 'mustache')
        {
            $this->render_core->renderEngine()->getLoader()->updateSubstituteTemplates($substitutes);

            if ($data_only)
            {
                $output = $render_data;
            }
            else
            {
                if ($this->domain->setting('display_render_timer') && isset($this->timer_start))
                {
                    $render_data['show_stats']['render_timer'] = function ()
                    {
                        return 'Page rendered in ' . $this->endTimer() . ' seconds.';
                    };
                }

                $output = $this->render_core->renderFromTemplateFile($template, $render_data);

                if ($translate)
                {
                    $start = microtime(true);
                    $output = $this->domain->translator()->translateHTML($output);
                    $translate_time = microtime(true) - $start;
                }
            }
        }

        return $output;
    }

    public function writeMode(bool $status = null)
    {
        if (!is_null($status))
        {
            $this->write_mode = $status;
        }

        return $this->write_mode;
    }
}