<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainSite extends Domain implements NellielCacheInterface
{
    private $file_filters;
    private $settings_cache_file;

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = '_site_';
        $this->database = $database;
        $this->settings_cache_file = $this->domain_id . '/' . 'domain_settings.php';
        $this->utilitySetup();
        $this->locale();
        $templates_file_path = ($this->front_end_data->templateIsCore($this->setting('template_id'))) ? NEL_CORE_TEMPLATES_FILES_PATH : NEL_CUSTOM_TEMPLATES_FILES_PATH;
        $this->templatePath(
                $templates_file_path . $this->front_end_data->template($this->setting('template_id'))['directory']);
    }

    protected function loadSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache($this->settings_cache_file, 'domain_settings');

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();

            if (NEL_USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(NEL_CACHE_FILES_PATH . $this->domain_id . '/',
                        'domain_settings.php', '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    protected function loadReferences()
    {
        $new_reference = array();
        $new_reference['log_table'] = NEL_LOGS_TABLE;
        $this->domain_references = $new_reference;
    }

    protected function loadSettingsFromDatabase()
    {
        $settings = array();
        $config_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE .
                '"."setting_name" WHERE "setting_category" = \'core\'', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting_value'] = nel_cast_to_datatype($config['setting_value'], $config['data_type'], false);
            $settings[$config['setting_name']] = $config['setting_value'];
        }

        return $settings;
    }

    public function globalVariation()
    {
        return false;
    }

    public function fileFilters()
    {
        if (empty($this->file_filters))
        {
            $loaded = false;

            if (!$loaded)
            {
                $filters = $this->database->executeFetchAll(
                        'SELECT "hash_type", "file_hash" FROM "nelliel_file_filters"', PDO::FETCH_ASSOC);
                foreach ($filters as $filter)
                {
                    $this->file_filters[$filter['hash_type']][] = $filter['file_hash'];
                }
            }
        }

        return $this->file_filters;
    }

    public function regenCache()
    {
        if (NEL_USE_INTERNAL_CACHE)
        {
            $this->cacheSettings();
        }
    }

    public function deleteCache()
    {
        if (NEL_USE_INTERNAL_CACHE)
        {
            $this->file_handler->eraserGun(NEL_CACHE_FILES_PATH . $this->domain_id);
        }
    }
}