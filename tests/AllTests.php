<?php
/**
 * All tests suite.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 */

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/Capall/Ldaper/Tests/LdaperTest.php';
require_once dirname(__FILE__).'/Capall/Ldaper/Tests/LdapUserTest.php';

/**
 * All tests suite.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 */
class AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('ldaper');

        $suite->addTestSuite('Capall_Ldaper_Tests_LdaperTest.php');
        $suite->addTestSuite('Capall_Ldaper_Tests_LdapUserTest.php');

        return $suite;
    }
}
