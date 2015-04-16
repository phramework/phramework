<?php

namespace APP\viewers;

/**
 * 
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 */
class viewer implements \Phramework\API\viewers\IViewer {

    /**
     * Display output
     * 
     * @param array $parameters Output parameters to display
     */
    public function view($parameters) {
        
        extract($parameters);
        
        include(__DIR__ . '/header.php');
        if(isset($page) && $page){
            include(__DIR__ . '/pages/' . $page . '.php');
        }else if(isset($error)){
            echo '<pre>';
            print_r($error);
            echo '</pre>';
        }
        include(__DIR__ . '/footer.php');
    }

}