______________________________________  ____________
___  __ \__  ____/__    |__  __ \__   |/  /__  ____/
__  /_/ /_  __/  __  /| |_  / / /_  /|_/ /__  __/
_  _, _/_  /___  _  ___ |  /_/ /_  /  / / _  /___
/_/ |_| /_____/  /_/  |_/_____/ /_/  /_/  /_____/
________                     ______                     ______________              _____
___  __ \_______________________  /___________  __      ___  __ \__  /___  ________ ___(_)______
__  / / /_  ___/  __ \__  __ \_  __ \  __ \_  |/_/________  /_/ /_  /_  / / /_  __ `/_  /__  __ \
_  /_/ /_  /   / /_/ /_  /_/ /  /_/ / /_/ /_>  < _/_____/  ____/_  / / /_/ /_  /_/ /_  / _  / / /
/_____/ /_/    \____/_  .___//_.___/\____//_/|_|        /_/     /_/  \__,_/ _\__, / /_/  /_/ /_/
                     /_/                                                    /____/
___________________________________    _____ __ _____ __
____  _/__  /____  _/__    |_  ___/    __  // / __  // /
 __  / __  /  __  / __  /| |____ \     _  // /_ _  // /_
__/ /  _  /____/ /  _  ___ |___/ /     /__  __/_/__  __/
/___/  /_____/___/  /_/  |_/____/        /_/  _(_)/_/


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





