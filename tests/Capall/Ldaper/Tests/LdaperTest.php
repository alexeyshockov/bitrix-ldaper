<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper_Tests
 */

require_once dirname(__FILE__).'/../../../../src/ldaper/classes/Capall/Ldaper.php';
require_once 'Net/LDAP2.php';

/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @package Capall_Ldaper_Tests
 */
class Capall_Ldaper_Tests_LdaperTest
    extends PHPUnit_Framework_TestCase
{

    private $_bitrixUser;

    private $_ldapConnection;

    private $_ldaper;

    protected function setUp()
    {
        $this->_ldapConnection = $this->getMock('Net_LDAP2');

        $this->_bitrixUser = $this->getMock('CUser', array('GetList', 'Add'));

        $this->_ldaper = new Capall_Ldaper(
            $this->_ldapConnection,
            $this->_bitrixUser,
            'dc=some,dc=com'
        );
    }

    public function testUserWithExistingBitrixAccountLogin()
    {
        $userFindResult = $this->getMock('CDBResult', array('Fetch'));
        $userFindResult
            ->expects($this->once())
            ->method('Fetch')
            ->with()
            ->will($this->returnValue(
                array('ID' => 1)
            ));
        $this->_bitrixUser
            ->expects($this->once())
            ->method('GetList')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                array('LOGIN_EQUAL_EXACT' => 'existing_login', 'EXTERNAL_AUTH_ID' => 'LDAPER')
            )
            ->will($this->returnValue(
                $userFindResult
            ));

        $user = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $user
            ->expects($this->once())
            ->method('getLogin')
            ->with()
            ->will($this->returnValue(
                'existing_login'
            ));

        $this->assertEquals(1, $this->_ldaper->loginToBitrix($user));
    }

    public function testUserWithNotExistingBitrixAccountLogin()
    {
        $userFindResult = $this->getMock('CDBResult', array('Fetch'));
        $userFindResult
            ->expects($this->once())
            ->method('Fetch')
            ->with()
            ->will($this->returnValue(
                false
            ));
        $this->_bitrixUser
            ->expects($this->once())
            ->method('GetList')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                array('LOGIN_EQUAL_EXACT' => 'existing_login', 'EXTERNAL_AUTH_ID' => 'LDAPER')
            )
            ->will($this->returnValue(
                $userFindResult
            ));
        $this->_bitrixUser
            ->expects($this->once())
            ->method('Add')
            ->with($this->isType('array'))
            ->will($this->returnValue(
                1
            ));

        $user = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $user
            ->expects($this->atLeastOnce())
            ->method('getLogin')
            ->with()
            ->will($this->returnValue(
                'existing_login'
            ));
        $user
            ->expects($this->once())
            ->method('getMail')
            ->with()
            ->will($this->returnValue(
                'test@test.com'
            ));

        $this->assertEquals(1, $this->_ldaper->loginToBitrix($user));
    }

    public function testUserWithNotExistingBitrixAccountAndDuplicateMailLogin()
    {
        $userFindResult = $this->getMock('CDBResult', array('Fetch'));
        $userFindResult
            ->expects($this->once())
            ->method('Fetch')
            ->with()
            ->will($this->returnValue(
                false
            ));
        $this->_bitrixUser
            ->expects($this->once())
            ->method('GetList')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                array('LOGIN_EQUAL_EXACT' => 'existing_login', 'EXTERNAL_AUTH_ID' => 'LDAPER')
            )
            ->will($this->returnValue(
                $userFindResult
            ));
        $this->_bitrixUser
            ->expects($this->once())
            ->method('Add')
            ->with($this->isType('array'))
            ->will($this->returnValue(
                false
            ));

        $user = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $user
            ->expects($this->atLeastOnce())
            ->method('getLogin')
            ->with()
            ->will($this->returnValue(
                'existing_login'
            ));
        $user
            ->expects($this->once())
            ->method('getMail')
            ->with()
            ->will($this->returnValue(
                'test@test.com'
            ));

        $this->setExpectedException('Capall_Ldaper_BitrixUserCreationException');

        $this->_ldaper->loginToBitrix($user);
    }

    public function testUserAuthentication()
    {
        $this->_ldapConnection
            ->expects($this->once())
            ->method('bind')
            ->with('uid=existing_login,dc=some,dc=com', 'some_password')
            ->will($this->returnValue(
                true
            ));

        $user = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $user
            ->expects($this->once())
            ->method('getDn')
            ->with()
            ->will($this->returnValue(
                'uid=existing_login,dc=some,dc=com'
            ));

        $this->assertTrue($this->_ldaper->authenticateUser($user, 'some_password'));
    }

    public function testGettingUserWithLdapError()
    {
        $this->_ldapConnection
            ->expects($this->once())
            ->method('search')
            ->with('dc=some,dc=com', '(uid=existing_login)')
            ->will($this->returnValue(
                new Net_LDAP2_Error()
            ));

        $this->setExpectedException('Capall_Ldaper_LdapException');

        $this->_ldaper->getUser('existing_login');
    }

    public function testGettingNotExistingUser()
    {
        $ldapSearchResult = $this->getMock(
        	'Net_LDAP2_Search',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapSearchResult
            ->expects($this->once())
            ->method('count')
            ->with()
            ->will($this->returnValue(
                0
            ));
        $this->_ldapConnection
            ->expects($this->once())
            ->method('search')
            ->with('dc=some,dc=com', '(uid=not_existing_login)')
            ->will($this->returnValue(
                $ldapSearchResult
            ));

        $this->assertNull($this->_ldaper->getUser('not_existing_login'));
    }

    public function testGettingExistingUser()
    {
        $ldapSearchResult = $this->getMock(
        	'Net_LDAP2_Search',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapSearchResult
            ->expects($this->once())
            ->method('count')
            ->with()
            ->will($this->returnValue(
                1
            ));
        $ldapUserEntry = $this->getMock(
        	'Net_LDAP2_Entry',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapSearchResult
            ->expects($this->once())
            ->method('shiftEntry')
            ->with()
            ->will($this->returnValue(
                $ldapUserEntry
            ));
        $this->_ldapConnection
            ->expects($this->once())
            ->method('search')
            ->with('dc=some,dc=com', '(uid=existing_login)')
            ->will($this->returnValue(
                $ldapSearchResult
            ));

        $this->assertType(
        	'Capall_Ldaper_LdapUser',
            $this->_ldaper->getUser('existing_login')
        );
    }
}
