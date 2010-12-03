<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */

require_once dirname(__FILE__).'/LdapException.php';

/**
 *
 * @internal
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */
class Capall_Ldaper_UnavailableDependencyException extends Capall_Ldaper_LdapException
{
    /**
     * @var string
     */
    private $_name;
    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct();

        $this->_name = $name;
    }
    /**
     *
     * @return string
     */
    public function __toString()
    {
        return 'Dependency "'.$this->_name.'" unavailable.';
    }
}
