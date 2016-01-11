<?php

function nel_parse_template($template, $subdirectory, $render, $regen)
{
    global $total_html;

    if(!empty($subdirectory))
    {
        $subdirectory .= '/';
    }

    $template_short = utf8_str_replace('.tpl', '', $template);
    $info = nel_template_info($template, NULL, NULL, TRUE);

    if ($info['loaded'] === FALSE || $info === NULL)
    {
        clearstatcache();
        $modify_time = filemtime(TEMPLATE_PATH . $subdirectory . $template);

        if ($modify_time !== $info[$template]['modify_time'] || !file_exists(CACHE_PATH . $template_short . '.nelcache'))
        {
            $info[$template]['modify-time'] = $modify_time;
            $lol = file_get_contents(TEMPLATE_PATH . $subdirectory . $template);
            $lol = trim($lol);
            $begin = '<?php function nel_template_render_' . $template_short . '($render) { $total_html; $temp = \''; // Start of the cached template
            $lol = preg_replace_callback('#({{.*?}})|({(.*?)})|(\')#', 'nel_escape_single_quotes', $lol); // Do escaping and variable parse
            $lol = preg_replace('#(})\s*?({)#', '$1$2', $lol); // Clear white space between control statements
            $lol = preg_replace('#{{\s*?(if|elseif|foreach|for|while)\s*?(.*?)}}#', '\'; $1($2): $temp .= \'', $lol); // Parse opening control statements
            $lol = preg_replace('#{{\s*?else\s*?}}#', '\'; else: $temp .= \'', $lol); // Parse else statements
            $lol = preg_replace('#{{\s*?(endif|endforeach|endfor|endwhile|endswitch)\s*?}}#', '\'; $1; $temp .= \'', $lol); // Parse closing control statements
            $lol = preg_replace('#{{{\s*?(.*?)\s*?}}}#', '\'; $1; $temp .= \'', $lol); // Parse other PHP code
            $end = '\'; return $temp; } ?>'; // End of the caches template
            $lol_out = $begin . $lol . $end;
            nel_write_file(CACHE_PATH . $template_short . '.nelcache', $lol_out, 0644);
        }

        include (CACHE_PATH . $template_short . '.nelcache');
        nel_template_info($template, NULL, $info, FALSE);
    }

    if (!$regen)
    {
        $dat_temp = call_user_func('nel_template_render_' . $template_short, $render);
        return $dat_temp;
    }
}

function nel_template_info($template, $parameter, $update, $return)
{
    static $info;
    
    if(!$return)
    {
        if(is_null($parameter))
        {
            $info[$template] = $update;
        }
        else
        {
            $info[$template][$parameter] = $update;
        }

    }
    else
    {
        if(is_null($template))
        {
            return $info;
        }
        else if(is_null($parameter))
        {
            return $info[$template];
        }
        else
        {
            return $info[$template][$parameter];
        }
    }
}

?>