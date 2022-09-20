# Course Module type plugin for integration with Nextcloud files or folders.

### EN
This plugin allows to integrate a Nextcloud file or folder in a Moodle resource.

### ES
Este plugin permite integrar un archivo o carpeta de Nextcloud en un recurso de Moodle.

### CA
Aquest plugin permet integrar un fitxer o carpeta de Nextcloud en un recurs de Moodle.

## Compatibility

This plugin version is tested for:

* Moodle 3.11.8+ (Build: 20220805) - 2021051708.04


## Installing via uploaded ZIP file ##

1. Log in to your Moodle's site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/tipnextcloud

Afterwards, log in to your Moodle's site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Configuration

Go to the URL:

    {your/moodle/dirroot}/admin/settings.php?section=modsettingtipnextcloud
