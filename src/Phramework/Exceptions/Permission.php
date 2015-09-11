<?php

namespace Phramework\Exceptions;

/**
 * PermissionException
 * Used to throw an \Exception, when there requested resource is not available for current user.
 * @author Spafaridis Xenophon <nohponex@gmail
 */
class Permission extends \Exception
{
    //The return url
    private $return;

    /**
     *
     * @param string $message \Exception message
     * @param string $return Return url. Optional, default is FALSE.
     */
    public function __construct($message, $return = false)
    {
        parent::__construct($message, 403);
        $this->return = $return;
    }

    public function getReturn()
    {
        return $this->return;
    }
}
