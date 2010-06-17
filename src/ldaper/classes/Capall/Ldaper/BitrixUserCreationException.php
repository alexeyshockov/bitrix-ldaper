<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */

/**
 *
 * @internal
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */
class Capall_Ldaper_BitrixUserCreationException extends Exception
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
