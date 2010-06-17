<?php
/**
 * Module settings.
 */

/*
 * Include some standard language constants.
 */
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$tabs = array(
    array(
        "DIV"   => 'general',
        "TAB"   => GetMessage('LDAPER_OPTIONS_GENERAL_TAB'),
        "ICON"  => '',
        "TITLE" => GetMessage('LDAPER_OPTIONS_GENERAL_TAB_TITLE')
    ),
    array(
        "DIV"   => 'additional',
        "TAB"   => GetMessage('LDAPER_OPTIONS_ADDITIONAL_TAB'),
        "ICON"  => '',
        "TITLE" => GetMessage('LDAPER_OPTIONS_ADDITIONAL_TAB_TITLE')
    ),
);
$tabControl = new CAdminTabControl("ldaperSettings", $tabs);

if (
    (strlen($_POST['Update'].$_POST['Apply']) > 0)
    &&
    check_bitrix_sessid()
) {
    foreach ($_POST['settings'] as $settingName => $settingValue) {
        COption::SetOptionString('ldaper', $settingName, $settingValue);
    }

    if (strlen($_REQUEST['Update']) && strlen($_REQUEST['back_url_settings'])) {
        LocalRedirect($_REQUEST['back_url_settings']);
    } else {
        LocalRedirect(
            $GLOBALS['APPLICATION']->GetCurPage().
            "?mid=".urlencode($mid).
            "&lang=".urlencode(LANGUAGE_ID).
            "&back_url_settings=".urlencode($_REQUEST["back_url_settings"]).
            "&".$tabControl->ActiveTabParam()
        );
    }
}

$tabControl->Begin();

?>
<form
    name="ldaperSettingsForm"
    method="post"
    action="<?php echo $GLOBALS['APPLICATION']->GetCurPage() ?>?mid=<?php echo urlencode($mid) ?>&amp;lang=<?php echo LANGUAGE_ID ?>">
<?php $tabControl->BeginNextTab() ?>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_CONNECTION_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_LDAP_SERVER') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionString('ldaper', 'host') ?>"
                    name="settings[host]" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_SEARCH_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_BASE_DN') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionString('ldaper', 'basedn') ?>"
                    name="settings[basedn]" />
            </td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_LOGIN_ATTRIBUTE') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionString('ldaper', 'login_attribute') ?>"
                    name="settings[login_attribute]" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_INFO_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_MAIL_ATTRIBUTE') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionString('ldaper', 'mail_attribute') ?>"
                    name="settings[mail_attribute]" />
            </td>
        </tr>
<?php $tabControl->BeginNextTab() ?>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_CONNECTION_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_PORT') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionInt('ldaper', 'port') ?>"
                    name="settings[port]" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_SEARCH_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_BIND_DN') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionString('ldaper', 'binddn') ?>"
                    name="settings[binddn]" />
            </td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_BIND_PW') ?>:</td>
            <td width="50%">
                <input
                    type="password"
                    size="30"
                    name="settings[bindpw]" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?php echo GetMessage('LDAPER_OPTIONS_INFO_SECTION') ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo GetMessage('LDAPER_OPTIONS_MAIL_ATTRIBUTE_INDEX') ?>:</td>
            <td width="50%">
                <input
                    type="text"
                    size="30"
                    value="<?php echo COption::GetOptionInt('ldaper', 'mail_attribute_index') ?>"
                    name="settings[mail_attribute_index]" />
            </td>
        </tr>
<?php $tabControl->Buttons() ?>
    <input
        type="submit"
        name="Update"
        value="<?php echo GetMessage("MAIN_SAVE") ?>"
        title="<?php echo GetMessage("MAIN_OPT_SAVE_TITLE") ?>" />
    <input
        type="submit"
        name="Apply"
        value="<?php echo GetMessage("MAIN_OPT_APPLY") ?>"
        title="<?php echo GetMessage("MAIN_OPT_APPLY_TITLE") ?>" />
    <?php if (strlen($_REQUEST["back_url_settings"])) { ?>
        <input
            type="button"
            name="Cancel"
            value="<?php echo GetMessage("MAIN_OPT_CANCEL") ?>"
            title="<?php echo GetMessage("MAIN_OPT_CANCEL_TITLE") ?>"
            onclick="window.location='<?php echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'" />
        <input
            type="hidden"
            name="back_url_settings"
            value="<?php echo htmlspecialchars($_REQUEST["back_url_settings"]) ?>" />
    <?php } ?>
    <?php echo bitrix_sessid_post() ?>
<?php $tabControl->End() ?>
</form>
