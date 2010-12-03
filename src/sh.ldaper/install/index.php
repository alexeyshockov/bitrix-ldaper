<?php
/**
 * Module descriptor (and installer).
 */

if (class_exists('sh_ldaper')) {
    return;
}

/**
 * Module descriptor for Bitrix.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 */
class sh_ldaper extends CModule
{
    // Fail.
    var $MODULE_ID = "sh.ldaper";

    public $MODULE_VERSION      = '${bitrix.moduleVersion}';
    public $MODULE_VERSION_DATE = '${bitrix.moduleVersionDate}';

    public $PARTNER_NAME;
    public $PARTNER_URI;

    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    /**
     *
     */
    public function __construct()
    {
        // Magic... Don't works, if in top of file.
        IncludeModuleLangFile(__FILE__);

        $this->MODULE_NAME        = GetMessage('LDAPER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('LDAPER_MODULE_DESCRIPTION');

        // Fail.
        $this->PARTNER_NAME = "Alexey Shockov";
        $this->PARTNER_URI  = "http://alexey.shockov.com/";
    }
    /**
     * Registration.
     */
    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        RegisterModuleDependences(
            'main',
            'OnUserLoginExternal',
            $this->MODULE_ID,
            'Capall_Ldaper',
            'authenticate'
        );
        RegisterModuleDependences(
            'main',
            'OnExternalAuthList',
            $this->MODULE_ID,
            __CLASS__,
            'getAuthenticationIdentifier'
        );
    }
    /**
     * Unregistration.
     */
    public function DoUninstall()
    {
        UnRegisterModuleDependences(
            'main',
            'OnUserLoginExternal',
            $this->MODULE_ID,
            'Capall_Ldaper',
            'authenticate'
        );
        UnRegisterModuleDependences(
            'main',
            'OnExternalAuthList',
            $this->MODULE_ID,
            __CLASS__,
            'getAuthenticationIdentifier'
        );

        UnRegisterModule($this->MODULE_ID);
    }
    /**
     * Description for Bitrix admin panel.
     *
     * @return array
     */
    public static function getAuthenticationIdentifier()
    {
        return array(
            'ID'   => 'LDAPER',
            'NAME' => 'LDAP'
        );
    }
}
