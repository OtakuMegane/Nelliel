<?php

declare(strict_types=1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use IPTools\IP;
use IPTools\Range;
use Nelliel\Domains\Domain;
use PDO;

class Snacks
{
    private $domain;
    private $database;
    private $ip_address;
    private $hashed_ip_address;
    private $bans_access;
    private $file_hashes;

    function __construct(Domain $domain, BansAccess $bans_access)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->bans_access = $bans_access;

        if (nel_site_domain()->setting('store_unhashed_ip'))
        {
            $this->ip_address = nel_request_ip_address();
        }
        else
        {
            $this->ip_address = null;
        }

        $this->hashed_ip_address = nel_request_ip_address(true);
    }

    public function checkHoneypot(): void
    {
        if (!empty($_POST[NEL_BASE_HONEYPOT_FIELD1 . '_' . $this->domain->id()]) ||
                !empty($_POST[NEL_BASE_HONEYPOT_FIELD2 . '_' . $this->domain->id()]) ||
                !empty($_POST[NEL_BASE_HONEYPOT_FIELD3 . '_' . $this->domain->id()]))
        {
            $ban_hammer = new BanHammer($this->database);
            $ban_hammer->modifyData('ip_address_start', $this->ip_address);
            $ban_hammer->modifyData('reason', 'Ur a spambot. Nobody wants any. GTFO!');
            $ban_hammer->modifyData('start_time', time());
            $ban_hammer->modifyData('length', 86400 * 9001);
            $ban_hammer->modifyData('all_boards', 1);
            $ban_hammer->apply();
        }
    }

    public function fileHashIsBanned(string $file_hash, string $hash_type): bool
    {
        if (empty($this->file_filters[$this->domain->id()]))
        {
            $loaded = false;

            if (!$loaded)
            {
                $prepared = $this->database->prepare(
                        'SELECT "hash_type", "file_hash" FROM "nelliel_file_filters" WHERE "board_id" = ? OR "all_boards" = 1');
                $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()],
                        PDO::FETCH_ASSOC);

                foreach ($filters as $filter)
                {
                    $this->file_filters[$this->domain->id()][$filter['hash_type']][] = bin2hex($filter['file_hash']);
                }
            }
        }

        if (!isset($this->file_filters[$this->domain->id()][$hash_type]))
        {
            return false;
        }

        return in_array($file_hash, $this->file_filters[$this->domain->id()][$hash_type]);
    }

    public function banAppeal(): void
    {
        $bawww = $_POST['bawww'] ?? null;
        $ban_id = $_POST['ban_id'] ?? null;

        if (empty($bawww) || empty($ban_id))
        {
            return;
        }

        if (!nel_site_domain()->setting('allow_ban_appeals'))
        {
            nel_derp(156, _gettext('Ban appeals are not enabled.'));
        }

        $ban_hammer = new BanHammer($this->database);

        if (!$ban_hammer->loadFromID($ban_id))
        {
            nel_derp(150, _gettext('Invalid ban ID given.'));
        }

        if ($ban_hammer->getData('ip_type') == BansAccess::RANGE)
        {
            nel_derp(151, _gettext('You cannot appeal a range ban.'));
        }

        if ($this->ip_address !== $ban_hammer->getData('ip_address_start') &&
                $this->hashed_ip_address !== $ban_hammer->getData('hashed_ip_address'))
        {
            nel_derp(152, _gettext('Your IP address does not match the one on the ban.'));
        }

        if (!$ban_hammer->addAppeal($bawww))
        {
            nel_derp(153, _gettext('You have already appealed your ban.'));
        }
    }

    public function applyBan(): void
    {
        $this->banAppeal();
        $this->checkRangeBans();
        $this->checkIPBans();
    }

    public function checkExpired(BanHammer $ban_hammer, bool $remove): bool
    {
        if ($ban_hammer->expired())
        {
            if (nel_site_domain()->setting('must_see_ban') && !$ban_hammer->getData('seen'))
            {
                return false;
            }

            if ($remove)
            {
                $ban_hammer->remove();
            }

            return true;
        }

        return false;
    }

    public function banPage(BanHammer $ban_hammer): void
    {
        $ban_hammer->modifyData('seen', 1);
        $ban_hammer->apply();
        $output_ban_page = new \Nelliel\Render\OutputBanPage($this->domain, false);
        $output_ban_page->render(['ban_hammer' => $ban_hammer], false);
        nel_clean_exit();
    }

    private function checkRangeBans(): void
    {
        $bans_range = $this->bans_access->getBansByType(BansAccess::RANGE, $this->domain->id());

        foreach ($bans_range as $ban_hammer)
        {
            if ($this->checkExpired($ban_hammer, true))
            {
                continue;
            }

            if ($ban_hammer->getData('all_boards') > 0 || $ban_hammer->getData('board_id') === $this->domain->id())
            {
                $range = new Range(new IP($ban_hammer->getData('ip_address_start')),
                        new IP($ban_hammer->getData('ip_address_end')));

                if ($range->contains(new IP($this->ip_address)))
                {
                    $this->banPage($ban_hammer);
                }
            }
        }
    }

    private function checkIPBans(): void
    {
        if (nel_site_domain()->setting('store_unhashed_ip'))
        {
            $bans_ip = $this->bans_access->getBansByIP($this->ip_address);
        }
        else
        {
            $bans_ip = array();
        }

        $bans_hashed = $this->bans_access->getBansByHashedIP($this->hashed_ip_address);
        $bans = array_merge($bans_ip, $bans_hashed);
        $longest = null;

        foreach ($bans as $ban_hammer)
        {
            if ($this->checkExpired($ban_hammer, true))
            {
                continue;
            }

            if ($ban_hammer->getData('all_boards') > 0|| $ban_hammer->getData('board_id') === $this->domain->id())
            {
                if (empty($longest) || $ban_hammer->timeToExpiration() > $longest->timeToExpiration())
                {
                    $longest = $ban_hammer;
                }

                continue;
            }
        }

        if (is_null($longest))
        {
            return;
        }

        $this->banPage($longest);
    }
}

