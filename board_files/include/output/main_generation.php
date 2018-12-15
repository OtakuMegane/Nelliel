<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_main_thread_generator($domain, $response_to, $write, $page = 0)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $file_handler = new \Nelliel\FileHandler();
    $domain->renderInstance(new NellielTemplates\RenderCore());
    $thread_table = $gen_data = array();
    $dotdot = ($write) ? '../' : '';

    if ($write)
    {
        $session->isIgnored('render', true);
    }

    $result = $database->query(
            'SELECT "thread_id" FROM "' . $domain->reference('thread_table') .
            '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = true;

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $domain->renderInstance()->startRenderTimer();
        nel_render_board_header($domain, $dotdot, $treeline);
        nel_render_posting_form($domain, $response_to, $dotdot);
        nel_render_general_footer($domain, $dotdot, true);
        ;

        if ($write)
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . PHP_SELF2 . PHP_EXT,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM);
            $session->isIgnored('render', false);
        }
        else
        {
            echo $domain->renderInstance()->outputRenderSet();
        }

        return;
    }

    $thread_counter = 0;
    $post_counter = -1;

    while ($thread_counter < $counttree)
    {
        $domain->renderInstance(new \NellielTemplates\RenderCore());
        $dom = $domain->renderInstance()->newDOMDocument();
        $domain->renderInstance()->loadTemplateFromFile($dom, 'thread.html');
        $domain->renderInstance()->startRenderTimer();
        $translator->translateDom($dom, $domain->setting('language'));
        $dom->getElementById('form-content-action')->extSetAttribute('action',
                $dotdot . PHP_SELF . '?module=threads&board_id=' . $domain->id());
        nel_render_board_header($domain, $dotdot, $treeline);
        nel_render_posting_form($domain, $response_to, $dotdot);
        $sub_page_thread_counter = 0;

        while ($sub_page_thread_counter < $domain->setting('threads_per_page'))
        {
            if ($post_counter === -1)
            {
                $current_thread_id = $front_page_list[$thread_counter];
                $thread_element = $dom->getElementById('thread-nci_0_0_0')->cloneNode();
                $thread_element->changeId('thread-nci_' . $current_thread_id . '_0_0');
                $dom->getElementById('form-content-action')->appendChild($thread_element);
                $post_append_target = $thread_element;
                $query = 'SELECT * FROM "' . $domain->reference('thread_table') . '" WHERE "thread_id" = ?';
                $prepared = $database->prepare($query);
                $gen_data['thread'] = $database->executePreparedFetch($prepared, array($current_thread_id),
                        PDO::FETCH_ASSOC);
                $post_count = $gen_data['thread']['post_count'];
                $abbreviate = $post_count > $domain->setting('abbreviate_thread');
                $query = 'SELECT * FROM "' . $domain->reference('post_table') .
                        '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC';
                $prepared = $database->prepare($query);
                $treeline = $database->executePreparedFetchAll($prepared, array($current_thread_id), PDO::FETCH_ASSOC);

                $gen_data['thread']['first100'] = $post_count > 100;
                $post_counter = 0;
            }

            $gen_data['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$post_counter];

            if ($gen_data['post']['has_file'] == 1)
            {
                $query = 'SELECT * FROM "' . $domain->reference('content_table') .
                        '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
                $prepared = $database->prepare($query);
                $gen_data['files'] = $database->executePreparedFetchAll($prepared,
                        array($gen_data['post']['post_number']), PDO::FETCH_ASSOC);
            }

            $new_post_element = nel_render_post($domain, $gen_data, $dom);
            $imported = $dom->importNode($new_post_element, true);
            $post_append_target->appendChild($imported);

            if ($gen_data['post']['op'] == 1)
            {
                $thread_content_id = \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']);
                $expand_div = $dom->getElementById('thread-expand-nci_0_0_0')->cloneNode(true);
                $expand_div->changeId('thread-expand-' . $thread_content_id);
                $post_append_target->appendChild($expand_div);
                $post_append_target = $expand_div;
                $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

                if ($abbreviate)
                {
                    $post_counter = $post_count - $domain->setting('abbreviate_thread');
                    $omitted_count = $post_count - $domain->setting('abbreviate_thread');
                    $omitted_element->firstChild->setContent($omitted_count);
                }
                else
                {
                    $omitted_element->remove();
                }
            }

            if (empty($treeline[$post_counter + 1]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $domain->setting('threads_per_page') : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($dom);
                $post_counter = -1;
            }
            else
            {
                ++ $post_counter;
            }
        }

        $dom->getElementById('post-id-nci_0_0_0')->remove();
        $dom->getElementById('thread-nci_0_0_0')->remove();
        $gen_data['posts_ending'] = true;
        $page_count = (int) ceil($counttree / $domain->setting('threads_per_page'));
        $pages = array();
        $modmode_base = 'imgboard.php?module=render&action=view-index&modmode=true&section=';
        $index_filename = 'index' . PHP_EXT;
        $index_format = $domain->setting('index_filename_format');
        $last_page = $page_count - 1;
        $nav_pieces = array();
        $nav_pieces['prev']['text'] = _gettext('Previous');

        if ($page === 0)
        {
            $nav_pieces[0]['link'] = '';
        }
        else
        {
            $nav_pieces[0]['link'] = 'index' . PHP_EXT;
        }

        $nav_pieces[0]['text'] = 0;

        for ($i = 1; $i < $page_count; ++ $i)
        {
            if($i === $page)
            {
                $nav_pieces[$i]['link'] = '';
            }
            else
            {
                $nav_pieces[$i]['link'] = sprintf($index_format, $i) . PHP_EXT;
            }

            $nav_pieces[$i]['text'] = $i;
        }

        $nav_pieces['next']['text'] = _gettext('Next');
        $nav_pieces['prev']['link'] = $nav_pieces[0]['link'];

        if ($page === $last_page)
        {
            $nav_pieces[$last_page]['link'] = '';
            $nav_pieces['next']['link'] = $nav_pieces[$last_page]['link'];
        }
        else
        {
            $nav_pieces['next']['link'] = $nav_pieces[$page + 1]['link'];
        }

        nel_render_index_navigation($domain, $dom, $nav_pieces);
        nel_render_thread_form_bottom($domain, $dom);
        $domain->renderInstance()->appendHTMLFromDOM($dom);
        nel_render_general_footer($domain, $dotdot, true);

        if ($page > 0)
        {
            $index_filename = sprintf($index_format, $page) . PHP_EXT;
        }
        else
        {
            $index_filename = 'index' . PHP_EXT;
        }

        if (!$write)
        {
            echo $domain->renderInstance()->outputRenderSet();
            nel_clean_exit();
        }
        else
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . $index_filename,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM, true);
        }

        ++ $page;
    }

    if ($write)
    {
        $session->isIgnored('render', false);
    }
}
