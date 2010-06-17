<?php
/**
 * Main authenticator class.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */

require_once 'Net/LDAP2.php';

require_once dirname(__FILE__).'/Ldaper/LdapException.php';
require_once dirname(__FILE__).'/Ldaper/BitrixUserCreationException.php';
require_once dirname(__FILE__).'/Ldaper/LdapUser.php';

/**
 * General authenticator class.
 *
 * @todo Debug information to own custom log.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper
 */
class Capall_Ldaper
{
    /**
     * @var CUser
     */
    private $bitrixUser;
    /**
     * @var Net_LDAP2
     */
    private $ldapConnection;
    /**
     * @var string
     */
    private $baseDn;
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
     * @internal
     *
     * @param Net_LDAP2 $ldapConnection
     * @param CUser $bitrixUser
     * @param string $baseDn
     * @param string $loginAttribute
     * @param string $mailAttribute
     * @param int $mailAttributeIndex
     */
    public function __construct(
        $ldapConnection,
        $bitrixUser,
        $baseDn = '',
        $loginAttribute = 'uid',
        $mailAttribute = 'mail',
        $mailAttributeIndex = null
    )
    {
        $this->ldapConnection     = $ldapConnection;
        $this->bitrixUser         = $bitrixUser;
        $this->baseDn             = $baseDn;
        $this->loginAttribute     = $loginAttribute;
        $this->mailAttribute      = $mailAttribute;
        $this->mailAttributeIndex = $mailAttributeIndex;
    }
    /**
     * For Bitrix calls.
     *
     * @param array &$params
     *
     * @return int
     */
    public static function authenticate(&$params)
    {
        try {
            $ldapConnection = Net_LDAP2::connect(
                array(
                    'host'   => COption::GetOptionString('ldaper', 'host'),
                    'port'   => COption::GetOptionInt('ldaper', 'port'),

                    'binddn' => COption::GetOptionString('ldaper', 'binddn'),
                    'bindpw' => COption::GetOptionString('ldaper', 'bindpw'),
                )
            );
            if (PEAR::isError($ldapConnection)) {
                throw new Capall_Ldaper_LdapException($ldapConnection);
            }

            $ldaper = new self(
                $ldapConnection,
                new CUser(),
                COption::GetOptionString('ldaper', 'basedn'),
                COption::GetOptionString('ldaper', 'login_attribute'),
                COption::GetOptionString('ldaper', 'mail_attribute'),
                COption::GetOptionString('ldaper', 'mail_attribute_index')
            );

            $ldapUser = $ldaper->getUser($params['LOGIN']);

            if ($ldapUser) {
                if ($ldaper->authenticateUser($ldapUser, $params['PASSWORD'])) {
                    $bitrixUserIdentifier = $ldaper->loginToBitrix($ldapUser);
                } else {
                    // Authentication failed. May be user not from LDAP?

                    return false;
                }
            } else {
                // User not found. It's normal use case.

                return;
            }

            return $bitrixUserIdentifier;
        } catch (Capall_Ldaper_BitrixUserCreationException $error) {
            CEventLog::Log(
                'WARNING',
                'USER_LOGIN', // Or USER_REGISTER_FAIL?
                'ldaper',
                $params['LOGIN'],
                (string)$error
            );
        } catch (Exception $error) {
            // TODO Use custom log (file?) for this errors.

            return;
        }
    }
    /**
     *
     * @internal
     *
     * @param Capall_Ldaper_LdapUser $ldapUser
     * @param string $password
     *
     * @return bool
     */
    public function authenticateUser($ldapUser, $password)
    {
        $bindResult = $this->ldapConnection->bind($ldapUser->getDn(), $password);

        if (PEAR::isError($bindResult)) {
            // Authentication failed.
            return false;
        }

        return true;
    }
    /**
     * Login LDAP user to Bitrix. If user exists in Bitrix, simple login. If
     * not, create before.
     *
     * @internal
     *
     * @throws Capall_Ldaper_BitrixUserCreationException
     *
     * @param Capall_Ldaper_LdapUser $ldapUser
     *
     * @return int Bitrix's user identifier.
     */
    public function loginToBitrix($ldapUser)
    {
        $findResult = $this->bitrixUser->GetList(
            ($by = 'timestamp_x'),
            ($order = 'desc'),
            array(
            	'LOGIN_EQUAL_EXACT' => $ldapUser->getLogin(),
            	'EXTERNAL_AUTH_ID'  => 'LDAPER'
            )
        );
        if(!($bitrixUserDescription = $findResult->Fetch())) {
            $bitrixUserIdentifier = $this->_createBitrixUser($ldapUser, $password);
        } else {
            $bitrixUserIdentifier = $bitrixUserDescription["ID"];
        }

        return $bitrixUserIdentifier;
    }
    /**
     *
     * @throws Capall_Ldaper_BitrixUserCreationException
     *
     * @param Capall_Ldaper_LdapUser $ldapUser
     * @param string $password
     *
     * @return int
     */
    private function _createBitrixUser($ldapUser)
    {
        // With EXTERNAL_AUTH_ID we are not obliged to pass password (many
        // standard checks are not carried out for external authentication).
        $bitrixUserIdentifier = $this->bitrixUser->Add(
            array(
                'LOGIN'            => $ldapUser->getLogin(),
                'EMAIL'            => $ldapUser->getMail(),
                'ACTIVE'           => 'Y',
                'EXTERNAL_AUTH_ID' => 'LDAPER',
            )
        );

        if (!$bitrixUserIdentifier) {
            throw new Capall_Ldaper_BitrixUserCreationException($this->bitrixUser->LAST_ERROR);
        }

        return $bitrixUserIdentifier;
    }
    /**
     *
     * @internal
     *
     * @throws Capall_Ldaper_LdapException
     *
     * @param string $login
     *
     * @return Capall_Ldaper_LdapUser
     */
    public function getUser($login)
    {
        // Search in tree for user...
        $users = $this->ldapConnection->search(
            $this->baseDn,
            '('.$this->loginAttribute.'='.$login.')'
        );

        if (PEAR::isError($users)) {
            throw new Capall_Ldaper_LdapException($users);
        }

        if ($users->count()) {
            return new Capall_Ldaper_LdapUser(
                $users->shiftEntry(),
                $this->loginAttribute,
                $this->mailAttribute,
                $this->mailAttributeIndex
            );
        }
    }
}
