<?php
/**
 *
 */

require_once dirname(__FILE__).'/LdapException.php';

/**
 * Wrapper over LDAP entry to encapsulate custom functionality.
 *
 * @internal
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */
class Capall_Ldaper_LdapUser
{
    /**
     * @var Net_LDAP2_Entry
     */
    private $ldapEntry;
    /**
     * @var string
     */
    private $loginAttribute;
    /**
     * @var string
     */
    private $mailAttribute;
    /**
     * @var int
     */
    private $mailAttributeIndex;
    /**
     *
     * @param Net_LDAP2_Entry $ldapEntry
     * @param string $loginAttribute
     * @param string $mailAttribute
     * @param int $mailAttributeIndex
     */
    public function __construct(
        $ldapEntry,
        $loginAttribute = 'uid',
        $mailAttribute = 'mail',
        $mailAttributeIndex = null
    )
    {
        $this->ldapEntry          = $ldapEntry;
        $this->loginAttribute     = $loginAttribute;
        $this->mailAttribute      = $mailAttribute;
        $this->mailAttributeIndex = $mailAttributeIndex;
    }
    /**
     * Determine user mail (because mail may be stored in different fields in diffent object types).
     *
     * @throws Capall_Ldaper_LdapException If error ocured when getting mail from LDAP.
     *
     * @param Net_LDAP2_Entry $userEntry
     *
     * @return string
     */
    public function getMail()
    {
        $mails = $this->ldapEntry->getValue($this->mailAttribute, 'all');

        if (PEAR::isError($mail)) {
            // Attribute setted incorrectly?
            throw new Capall_Ldaper_LdapException($mail);
        }

        if (null === $this->mailAttributeIndex) {
            $mail = array_shift($mails);
        } else {
            $mail = null;
            if (array_key_exists($this->mailAttributeIndex, $mails)) {
                $mail = $mails[$this->mailAttributeIndex];
            } else {
                // TODO Report error!
            }
        }

        return (empty($mail) ? null : $mail);
    }
    /**
     *
     * @return string
     */
    public function getLogin()
    {
        // TODO Check result for error?..
        return $this->ldapEntry->getValue($this->loginAttribute);
    }
    /**
     *
     * @return string
     */
    public function getDn()
    {
        return $this->ldapEntry->currentDN();
    }
}
