<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper_Tests
 */

require_once dirname(__FILE__).'/../../../../src/ldaper/classes/Capall/Ldaper.php';
require_once 'Net/LDAP2.php';

/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper_Tests
 */
class Capall_Ldaper_Tests_LdapUserTest
    extends PHPUnit_Framework_TestCase
{
    private $_ldapUserEntry;

    protected function setUp()
    {
        $this->_ldapUserEntry = $this->getMock(
        	'Net_LDAP2_Entry',
            array(),
            array(),
            '',
            false // Don't call original constructor.
        );
    }
    /**
     *
     */
    public function testSimpleMailDetermining()
    {
        $this->_ldapUserEntry
            ->expects($this->any())
            ->method('getValue')
            ->with('mail')
            ->will($this->returnValue(
                array('test@test.com')
            ));

        $user = new Capall_Ldaper_LdapUser($this->_ldapUserEntry);

        $this->assertEquals('test@test.com', $user->getMail());
    }
    /**
     *
     */
    public function testComplexMailDetermining()
    {
        $this->_ldapUserEntry
            ->expects($this->any())
            ->method('getValue')
            ->with('description')
            ->will($this->returnValue(
                array('Some user description.', 'one@test.com', 'two@test.com')
            ));

        $user = new Capall_Ldaper_LdapUser($this->_ldapUserEntry, 'uid', 'description', 1);

        $this->assertEquals('one@test.com', $user->getMail());
    }
}
