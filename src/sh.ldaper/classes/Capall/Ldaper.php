<?php
/**
 * Main authenticator class.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper
 */

require_once dirname(__FILE__).'/Ldaper/LdapException.php';
require_once dirname(__FILE__).'/Ldaper/UnavailableDependencyException.php';
require_once dirname(__FILE__).'/Ldaper/BitrixUserCreationException.php';
require_once dirname(__FILE__).'/Ldaper/LdapUser.php';
require_once dirname(__FILE__).'/Ldaper/BitrixUserManager.php';

/**
 * General authenticator class.
 *
 * @todo Debug information to own custom log.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper
 */
class Capall_Ldaper
{
    /**
     * @var Capall_Ldaper_BitrixUserManager
     */
    private $bitrixUserManager;
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
     * @param Capall_Ldaper_BitrixUserManager $bitrixUserManager
     * @param string $baseDn
     * @param string $loginAttribute
     * @param string $mailAttribute
     * @param int $mailAttributeIndex
     */
    public function __construct(
        $ldapConnection,
        $bitrixUserManager,
        $baseDn = '',
        $loginAttribute = 'uid',
        $mailAttribute = 'mail',
        $mailAttributeIndex = null
    )
    {
        $this->ldapConnection     = $ldapConnection;
        $this->bitrixUserManager  = $bitrixUserManager;
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
            // Import PEAR library gracefully...
            if (!@include_once 'Net/LDAP2.php') {
            	throw new Capall_Ldaper_UnavailableDependencyException('PEAR::Net_LDAP2');
            }

            $ldapConnection = Net_LDAP2::connect(
                array(
                    'host'   => COption::GetOptionString('sh.ldaper', 'host'),
                    'port'   => COption::GetOptionInt('sh.ldaper', 'port'),

                    'binddn' => COption::GetOptionString('sh.ldaper', 'binddn'),
                    'bindpw' => COption::GetOptionString('sh.ldaper', 'bindpw'),
                )
            );
            if (PEAR::isError($ldapConnection)) {
                throw new Capall_Ldaper_LdapException($ldapConnection);
            }

            $ldaper = new self(
                $ldapConnection,
                new Capall_Ldaper_BitrixUserManager(
                    new CUser(),
                    array_filter(
                        explode(
                            ',',
                            COption::GetOptionString('sh.ldaper', 'default_groups', '')
                        ),
                        'trim'
					)
                ),
                COption::GetOptionString('sh.ldaper', 'basedn'),
                COption::GetOptionString('sh.ldaper', 'login_attribute'),
                COption::GetOptionString('sh.ldaper', 'mail_attribute'),
                COption::GetOptionString('sh.ldaper', 'mail_attribute_index')
            );

            $ldapUser = $ldaper->getLdapUser($params['LOGIN']);

            if ($ldapUser) {
                if ($ldaper->authenticateUser($ldapUser, $params['PASSWORD'])) {
                    $bitrixUserIdentifier = $ldaper->getBitrixUser($ldapUser);
                } else {
                    // Authentication failed. May be user not from LDAP?

                    return false;
                }
            } else {
                // User not found. It's normal use case.

                return;
            }

            // Return identifier to Bitrix for authorization.
            return $bitrixUserIdentifier;
        } catch (Capall_Ldaper_BitrixUserCreationException $error) {
            CEventLog::Log(
                'WARNING',
                'USER_LOGIN', // Or USER_REGISTER_FAIL?
                'sh.ldaper',
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
     * @param Capall_Ldaper_LdapUser $ldapUser
     *
     * @return int Bitrix's user identifier.
     */
    public function getBitrixUser($ldapUser)
    {
        if (!($bitrixUserIdentifier = $this->bitrixUserManager->getByLogin($ldapUser->getLogin()))) {
            $bitrixUserIdentifier = $this->bitrixUserManager->create($ldapUser->getLogin(), $ldapUser->getMail());
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
    public function getLdapUser($login)
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
