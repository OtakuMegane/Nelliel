<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginAPI
{
    private $hooks = array();
    private $plugins = array();

    public function registerPlugin($plugin_info)
    {
        if(!ENABLE_PLUGINS)
        {
            return false;
        }

        $plugin_id = $this->generateID($plugin_info);

        if (!in_array($plugin_id, $this->plugins))
        {
            $this->plugins[$plugin_id] = $plugin_info;
            return $plugin_id;
        }

        return false;
    }

    // Register hook functions here
    public function addHookFunction($hook_name, $function_name, $plugin_id, $priority = 10)
    {
        if (!ENABLE_PLUGINS || !$this->isValidPlugin($plugin_id) || !$this->isValidFunction($function_name))
        {
            return false;
        }

        $hooks = $this->hooks[$hook_name][] = ['function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
        $this->sort_hooks($hook_name);
        return true;
    }

    // Register hook methods here
    public function addHookMethod($hook_name, $class, $method_name, $plugin_id, $priority = 10)
    {
        if (!ENABLE_PLUGINS || !$this->isValidPlugin($plugin_id) || !$this->isValidMethod($class, $method_name))
        {
            return false;
        }

        $hooks = $this->hooks[$hook_name][] = ['class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
        $this->sort_hooks($hook_name);
        return true;
    }

    public function removeHookFunction($hook_name, $function_name, $plugin_id)
    {
        if (!ENABLE_PLUGINS || !$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id) ||
            !$this->isValidFunction($function_name))
        {
            return false;
        }

        foreach ($this->hooks[$hook_name] as $key => $value)
        {
            if ($this->verifyRegistrationArray($key, false) && $key['plugin_id'] === $plugin_id &&
                $key['function_name'] === $function_name)
            {
                unset($this->hooks[$hook_name][$key]);
                $this->sort_hooks($hook_name);
                return true;
            }
        }

        return false;
    }

    public function removeHookMethod($hook_name, $class, $method_name, $plugin_id)
    {
        if (!ENABLE_PLUGINS || !$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id) ||
            !$this->isValidMethod($class, $method_name))
        {
            return false;
        }

        foreach ($this->hooks[$hook_name] as $key => $value)
        {
            if ($this->verifyRegistrationArray($key, true) && $key['plugin_id'] === $plugin_id &&
                $key['class'] === $class && $key['method_name'] === $method_name)
            {
                unset($this->hooks[$hook_name][$key]);
                $this->sort_hooks($hook_name);
                return true;
            }
        }

        return false;
    }

    public function processHook($hook_name, $args, $returnable = null)
    {
        if (!ENABLE_PLUGINS || !$this->isValidHook($hook_name))
        {
            return $returnable;
        }

        if (!is_array($args))
        {
            $args = [0 => $args];
        }

        $arguments_array = $args;
        $needs_return = !is_null($returnable);
        $return_type = gettype($returnable);

        if ($needs_return)
        {
            array_unshift($arguments_array, $returnable);
        }

        $hook = $this->hooks[$hook_name];
        $modified = $returnable;

        foreach ($hook as $entry)
        {
            if (isset($entry['method_name']) && $this->isValidMethod($entry['class'], $entry['method_name']))
            {
                $return = call_user_func_array([$entry['class'], $entry['method_name']], $arguments_array);
            }
            else if ($this->isValidFunction($entry['function_name']))
            {
                $return = call_user_func_array($entry['function_name'], $arguments_array);
            }

            if ($needs_return && gettype($return) === $return_type)
            {
                $modified = $return;
                $arguments_array[0] = $modified;
            }
        }

        return $modified;
    }

    private function getPluginIniFiles()
    {
        $file_handler = new \Nelliel\FileHandler();
        $files = $file_handler->recursiveFileList(PLUGINS_PATH, false, ['ini']);
        return $files;
    }

    public function initializePlugins()
    {
        if(!ENABLE_PLUGINS)
        {
            return;
        }

        $ini_files = $this->getPluginIniFiles();

        foreach ($ini_files as $ini_file)
        {
            $ini = parse_ini_file($ini_file);
            $plugin_root = pathinfo($ini_file, PATHINFO_DIRNAME) . '/';
            $initializer = $ini['initializer'];
            include_once $plugin_root . $initializer;
        }
    }

    private function generateID($plugin_info)
    {
        return substr(md5(implode('', $plugin_info) . time()), -8);
    }

    private function verifyRegistrationArray($array, $is_class)
    {
        if (!isset($array['plugin_id']))
        {
            return false;
        }

        if ($is_class)
        {
            return (isset($array['class']) && isset($array['method_name']));
        }
        else
        {
            return isset($array['function_name']);
        }
    }

    private function sort_hooks($hook_name)
    {
        usort($this->hooks[$hook_name], array($this, 'sort_by_priority'));
    }

    private function sort_by_priority($a, $b)
    {
        if ($a['priority'] == $b['priority'])
        {
            return $a['priority'] - $b['priority'];
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }

    private function isValidHook($hook_name)
    {
        return !empty($hook_name) && isset($this->hooks[$hook_name]);
    }

    private function isValidPlugin($plugin_id)
    {
        return !empty($plugin_id) && isset($this->plugins[$plugin_id]);
    }

    private function isValidFunction($function_name)
    {
        return !empty($function_name) && function_exists($function_name);
    }

    private function isValidMethod($class, $method_name)
    {
        return !empty($class) && is_object($class) && method_exists($class, $method_name);
    }
}