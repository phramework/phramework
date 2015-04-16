<?php

namespace APP\viewers;

/**
 * @package examples/blog
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 */
class viewer implements \Phramework\API\viewers\IViewer {

    /**
     * Display output as html using a header and footer
     * 
     * @param array $parameters Output parameters to display
     */
    public function view($parameters) {
        
        extract($parameters);
        
        if(!isset($page) || !$page){ //In case page parameter is set
            $page = 'error';
        }
        
        include(__DIR__ . '/header.php');
        
        
        //Include the page file
        include(__DIR__ . '/pages/' . $page . '.php');
       
        include(__DIR__ . '/footer.php');
    }

}