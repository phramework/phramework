<?php

namespace Phramework\Examples\blog\APP\Viewers;

/**
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 */
class Viewer implements \Phramework\Viewers\IViewer
{
    /**
     * Display output as html using a header and footer.
     *
     * @param array $parameters Output parameters to display
     * @param $VIEWER_page $page Page's file name
     * @param $VIEWER_title Page's title
     */
    public function view($parameters)
    {
        $num_args = func_num_args();

        if ($num_args > 1) {
            $VIEWER_page = func_get_arg(1);
        }
        if ($num_args > 2) {
            $VIEWER_title = func_get_arg(2);
        }

        extract($parameters);

        if (!isset($VIEWER_page) || !$VIEWER_page) { //In case page parameter is not set
            $VIEWER_page = 'error';
        }

        //copy title if not set as funtion argument and set in parameters
        if ((!isset($VIEWER_title) || !$VIEWER_title) && isset($parameters['title'])) {
            $VIEWER_title = $parameters['title'];
        } elseif (!isset($VIEWER_title)) {
            $VIEWER_title = '';
        }

        include __DIR__. '/header.php';

        //Include the page file
        include __DIR__. '/pages/' . $VIEWER_page.'.php';

        include __DIR__. '/footer.php';
    }
}
