<?php
/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper_Tests
 */

require_once dirname(__FILE__).'/../../../../src/ldaper/classes/Capall/Ldaper.php';

/**
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package Capall_Ldaper_Tests
 */
class Capall_Ldaper_Tests_BitrixUserManagerTest
    extends PHPUnit_Framework_TestCase
{
    private $_bitrixUser;

    protected function setUp()
    {
        $this->_bitrixUser = $this->getMock('CUser', array('GetList', 'Add'));
    }
    /**
     * Expects right data to DB and new user identifier as return value.
     */
    public function testCreation()
    {
        $this->_bitrixUser
            ->expects($this->once())
            ->method('Add')
            ->with($this->isType('array'))
            ->will($this->returnValue(
                1
            ));

        $manager = new Capall_Ldaper_BitrixUserManager($this->_bitrixUser);

        $this->assertEquals(1, $manager->create('test', 'test@test.com'));
    }
    /**
     * Expects exception...
     */
    public function testFailureCreation()
    {
        $this->markTestIncomplete('LAST_ERROR :(');
        
        $this->setExpectedException('Capall_Ldaper_BitrixUserCreationException');

        $this->_bitrixUser
            ->expects($this->once())
            ->method('Add')
            ->with($this->isType('array'))
            ->will($this->returnValue(
                false
            ));

        $manager = new Capall_Ldaper_BitrixUserManager($this->_bitrixUser);

        $manager->create('test', 'test@test.com');
    }
    /**
     * Expects identifier.
     */
    public function testGettingExisting()
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
                array('LOGIN_EQUAL_EXACT' => 'test', 'EXTERNAL_AUTH_ID' => 'LDAPER')
            )
            ->will($this->returnValue(
                $userFindResult
            ));

        $manager = new Capall_Ldaper_BitrixUserManager($this->_bitrixUser);

        $this->assertEquals(1, $manager->getByLogin('test'));
    }
    /**
     * Expects NULL.
     */
    public function testGettingNotExisting()
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
                array('LOGIN_EQUAL_EXACT' => 'test', 'EXTERNAL_AUTH_ID' => 'LDAPER')
            )
            ->will($this->returnValue(
                $userFindResult
            ));

        $manager = new Capall_Ldaper_BitrixUserManager($this->_bitrixUser);

        $this->assertNull($manager->getByLogin('test'));
    }
}
