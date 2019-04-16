<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputNews extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = ($parameters['dotdot']) ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'use_site_titles' => true], true);
        $this->render_data['news_entries'] = $this->newsList();
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('news', $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => '', 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        $this->file_handler->writeFile(BASE_PATH . 'news.html', $output);
    }

    private function newsList(int $limit = 0)
    {
        $database = $this->domain->database();
        $authorization = new \Nelliel\Auth\Authorization($database);
        $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $limit_counter = 0;
        $entry_list = array();

        foreach ($news_entries as $news_entry)
        {
            if ($limit !== 0 && $limit_counter >= $limit)
            {
                break;
            }

            $news_info = array();
            $news_info['headline'] = $news_entry['headline'];
            $poster_name = $authorization->getUser($news_entry['poster_id'])->auth_data['display_name'];
            $news_info['poster'] = ' by ' . $poster_name;
            $news_info['time'] = ' - ' . date('Y/m/d (D) H:i:s', $news_entry['time']);
            $news_info['news_lines'] = array();

            foreach ($this->output_filter->newlinesToArray($news_entry['text']) as $line)
            {
                $news_info['news_lines'][]['news_line'] = $line;
            }

            $entry_list[] = $news_info;
            ++ $limit_counter;
        }

        return $entry_list;
    }
}