<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminNews extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(471, _gettext('You are not allowed to make news posts.'));
        }

        $news_info = array();
        $news_info['poster_id'] = $this->session_user->id();
        $news_info['headline'] = $_POST['headline'] ?? null;
        $news_info['time'] = time();
        $news_info['text'] = $_POST['news_text'] ?? null;
        $query = 'INSERT INTO "' . NEL_NEWS_TABLE . '" ("poster_id", "headline", "time", "text") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$news_info['poster_id'], $news_info['headline'], $news_info['time'], $news_info['text']]);
        $this->regenNews();
        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(473, _gettext('You are not allowed to remove news posts.'));
        }

        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_NEWS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->regenNews();
        $this->outputMain(true);
    }

    private function regenNews()
    {
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_news'))
        {
            nel_derp(470, _gettext('You are not allowed to access the news panel.'));
        }
    }
}
