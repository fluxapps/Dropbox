Have a look at the [full Documentation](/doc/Documentation.pdf?raw=true)

## How to install: ##

Requires php 5.3

### 1. Put Files into Customizing Foler
Since you are reading this file, we guess that you successfully downloaded the complete source of the Dropbox-Plugin. Put the whole Dropbox-Plugin into the following Directory: Customizing/global/plugins/Modules/Cloud/CloudHook/
If some subdirectories do not yet exist, you have to create them.

The plugin should now appear in the plugin section of the administration in the plugin slot table (“Administration->Plugins”). When you click on "Action->Informations" you should see some informations about the Dropbox-Plugin and the file “plugin.php” as well as the class file should be marked as “Available”.
Also the language file should show up. If so, the plugin can be updated and activated.

Important note: The Cloud-Module is deactivated by default. In order to create a Dropbox-Plugin in der Repository, the Cloud-Modules must be activated. Uncheck therefore the “Disable Creation” box of the Cloud Module in the modules section (“Administration->Repository->Module”).

### 2. Configure Dropbox-Plugin settings### 2. Configure Dropbox-Plugin settings
So far the plugin is not working. In order to work, the plugin needs to be properly configured. The important settings are "App Name", "Key" and "Secret".
* To get those values, the plugin needs to be registered at https://www.dropbox.com/developers.
* The registration requires a Dropbox Account.
* The values can the be accessed through the "App Console" on the Webpage of Dropbox. Register a "Dropbox API app" with "Files and datastores".
* Set the wished access of the app (we highly recommend to limit the access to the apps private folder).
* Set the app name. Beware, that the app name corresponds to the name of the subfolder of the app in the users file structure
* Next you need to set the OAuth redirect URL. This is the address of your webserver combined with '/Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php'
  (example: http://localhost/Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php or https://ilias.uniX.de/Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php).
* For Testing the plugin with up to 100 users one has to click to button: „Enable Additional Users“

Please consult https://www.dropbox.com/developers for more informations regarding the registration of the app.
Consider that the App needs to go through the "Apply for production" process once the plugin gets operational.

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Source Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  


