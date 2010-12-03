<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper
 */

require_once dirname(__FILE__).'/BitrixUserCreationException.php';

/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper
 */
class Capall_Ldaper_BitrixUserManager
{
    /**
     * @var array
     */
    private $defaultGroups;
    /**
     * @var CUser
     */
    private $bitrixUser;
    /**
     *
     * @param array $defaultGroups
     */
    public function __construct($bitrixUser, $defaultGroups = array())
    {
        $this->bitrixUser    = $bitrixUser;
        $this->defaultGroups = $defaultGroups;
    }
    /**
     *
     * @throws Capall_Ldaper_BitrixUserCreationException
     *
     * @param string $login
     * @param string $email
     *
     * @return int
     */
    public function create($login, $email)
    {
        // With EXTERNAL_AUTH_ID we are not obliged to pass password (many
        // standard checks are not carried out for external authentication).
        $id = $this->bitrixUser->Add(
            array(
                'LOGIN'            => $login,
                'EMAIL'            => $email,
                'ACTIVE'           => 'Y',
                'EXTERNAL_AUTH_ID' => 'LDAPER',
                'GROUP_ID'		   => $this->defaultGroups
            )
        );

        if (!$id) {
            throw new Capall_Ldaper_BitrixUserCreationException($this->bitrixUser->LAST_ERROR);
        }

        return $id;
    }
    /**
     * @param string $login
     *
     * @return int|null
     */
    public function getByLogin($login)
    {
        $findResult = $this->bitrixUser->GetList(
            ($by = 'timestamp_x'),
            ($order = 'desc'),
            array(
            	'LOGIN_EQUAL_EXACT' => $login,
            	'EXTERNAL_AUTH_ID'  => 'LDAPER'
            )
        );

        if ($user = $findResult->Fetch()) {
            return $user['ID'];
        }

        return null;
    }
}
