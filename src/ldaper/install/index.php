<?php
/**
 * Module descriptor (and installer).
 */

if (class_exists('ldaper')) {
    return;
}

IncludeModuleLangFile(__FILE__);

/**
 * Module descriptor for Bitrix.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 */
class ldaper extends CModule
{
    public $MODULE_ID           = 'ldaper';
    public $MODULE_VERSION      = '${bitrix.moduleVersion}';
    public $MODULE_VERSION_DATE = '${bitrix.moduleVersionDate}';

    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    /**
     *
     */
    public function __construct()
    {
        $this->MODULE_NAME        = GetMessage(strtoupper($this->MODULE_ID).'_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage(strtoupper($this->MODULE_ID).'_MODULE_DESCRIPTION');
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
