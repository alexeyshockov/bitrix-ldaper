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
    private $_bitrixUserManager;

    private $_ldapConnection;

    private $_ldaper;

    protected function setUp()
    {
        $this->_ldapConnection = $this->getMock('Net_LDAP2');

        $this->_bitrixUserManager = $this->getMock(
        	'Capall_Ldaper_BitrixUserManager',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );

        $this->_ldaper = new Capall_Ldaper(
            $this->_ldapConnection,
            $this->_bitrixUserManager,
            'dc=test,dc=com'
        );
    }

    public function testUserWithExistingBitrixAccountLogin()
    {
        $this->_bitrixUserManager
            ->expects($this->once())
            ->method('getByLogin')
            ->with('test')
            ->will($this->returnValue(
                1
            ));

        $ldapUser = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapUser
            ->expects($this->once())
            ->method('getLogin')
            ->with()
            ->will($this->returnValue(
                'test'
            ));

        $this->assertEquals(1, $this->_ldaper->getBitrixUser($ldapUser));
    }

    public function testUserWithNotExistingBitrixAccountLogin()
    {
        $this->_bitrixUserManager
            ->expects($this->once())
            ->method('getByLogin')
            ->with('test')
            ->will($this->returnValue(
                null
            ));
        $this->_bitrixUserManager
            ->expects($this->once())
            ->method('create')
            ->with('test')
            ->will($this->returnValue(
                1
            ));

        $ldapUser = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapUser
            ->expects($this->atLeastOnce())
            ->method('getLogin')
            ->with()
            ->will($this->returnValue(
                'test'
            ));
        $ldapUser
            ->expects($this->once())
            ->method('getMail')
            ->with()
            ->will($this->returnValue(
                'test@test.com'
            ));

        $this->assertEquals(1, $this->_ldaper->getBitrixUser($ldapUser));
    }

    public function testUserAuthentication()
    {
        $this->_ldapConnection
            ->expects($this->once())
            ->method('bind')
            ->with('uid=test,dc=test,dc=com', 'password')
            ->will($this->returnValue(
                true
            ));

        $ldapUser = $this->getMock(
        	'Capall_Ldaper_LdapUser',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
        $ldapUser
            ->expects($this->once())
            ->method('getDn')
            ->with()
            ->will($this->returnValue(
                'uid=test,dc=test,dc=com'
            ));

        $this->assertTrue($this->_ldaper->authenticateUser($ldapUser, 'password'));
    }

    public function testGettingUserWithLdapError()
    {
        $this->_ldapConnection
            ->expects($this->once())
            ->method('search')
            ->with('dc=test,dc=com', '(uid=test)')
            ->will($this->returnValue(
                new Net_LDAP2_Error()
            ));

        $this->setExpectedException('Capall_Ldaper_LdapException');

        $this->_ldaper->getLdapUser('test');
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
            ->with('dc=test,dc=com', '(uid=test)')
            ->will($this->returnValue(
                $ldapSearchResult
            ));

        $this->assertNull($this->_ldaper->getLdapUser('test'));
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
            ->with('dc=test,dc=com', '(uid=test)')
            ->will($this->returnValue(
                $ldapSearchResult
            ));

        $this->assertType(
        	'Capall_Ldaper_LdapUser',
            $this->_ldaper->getLdapUser('test')
        );
    }
}
