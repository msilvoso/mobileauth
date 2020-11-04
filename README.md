# mobileauth
Microsoft activesync reverse proxy with mobile id authentication.

The *etc* folder contains the relevant modsecurity and apache2 configuration, but you will have to adapt it as I removed all specific information.

The rest is an old Zend framework 1 application that looks for new telephone accesses in the log files and gives the operator the possibility to add the ids and usernames to the mod-security configuration. You do not really need this as you could edit the modsecurity configuration by hand.

