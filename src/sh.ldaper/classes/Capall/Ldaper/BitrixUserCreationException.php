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
class Capall_Ldaper_BitrixUserCreationException extends Capall_Ldaper_LdapException
{
    /**
     * @var string
     */
    private $_bitrixError;
    /**
     *
     * @param string $bitrixError
     */
    public function __construct($bitrixError)
    {
        parent::__construct();

        $this->_bitrixError = $bitrixError;
    }
    /**
     *
     * @return string
     */
    public function getBitrixError()
    {
        return $this->_bitrixError;
    }
    /**
     *
     * @return string
     */
    public function __toString()
    {
        return '[Bitrix Error] '.$this->_bitrixError;
    }
}
