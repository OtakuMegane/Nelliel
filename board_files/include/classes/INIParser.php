<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class INIParser
{
    protected $file_handler;

    function __construct($file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function parseDirectories($path, string $file_name = '', $recursion_depth = -1)
    {
        $ini_files = $this->file_handler->recursiveFileList($path, $recursion_depth);
        $parsed_ini = array();

        foreach ($ini_files as $file)
        {
            if ($file->getExtension() !== 'ini')
            {
                continue;
            }

            if ($file_name !== '' && $file->getFilename() !== $file_name)
            {
                continue;
            }

            $parsed_ini[] = parse_ini_file($file->getPathname(), true);
        }

        return $parsed_ini;
    }
}