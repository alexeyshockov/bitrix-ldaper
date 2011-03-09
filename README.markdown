Bitrix LDAP Authentication
==========================

Simple LDAP authentication module, that's free alternative to Bitrix's own module (that comes only with Business product edition). Free module allow you to manage all users in one place (very actual with many small Bitrix based sites).

Installation
------------

Module may be installed over PEAR channel:

    pear channel-discover capall.shockov.com
    pear install capall/ldaper

Or over Bitrix update system from [marketplace](http://www.1c-bitrix.ru/solutions/marketplace/sh.ldaper "LDAPer").

With PEAR you need also create symlink in your Bitrix installation(s):

    ln -s /your-pear-directory/capall/sh.ldaper/ /your-site-document-root/bitrix/modules/sh.ldaper
