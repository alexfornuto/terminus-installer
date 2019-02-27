[![CircleCI](https://circleci.com/gh/alexfornuto/terminus-installer/tree/master.svg?style=svg)](https://circleci.com/gh/alexfornuto/terminus-installer/tree/master)

# Terminus Installer
This is an **unoficial third party** script to help new users install [Terminus](https://github.com/pantheon-systems/terminus) to a standard location, and add that location to `$PATH` so it can be called from any directory.

## Requirements

As Terminus is for macOS/Linux only, so is this installation script.

Before using this script, you should make sure you have all of the [dependencies required](https://github.com/pantheon-systems/terminus#required) for Terminus.

## Usage
This script is written in PHP. Using it is as easy as downloading it and passing it to `php`. The following one-liner will do both:

```bash
curl -O https://raw.githubusercontent.com/alexfornuto/terminus-installer/master/installer.php && php installer.php
```

### Customization
If you're using this script as part of a larger distribution/installation process and want to change the default installation path (set to `/usr/local/bin` by default), update `$installdir` with the preferred installation directory. Note that the script **will**:

 - Prompt for a sudo password if the installation path cannot be written to by the user running the script.
 - Attempt to add the installation directory to the $PATH variable, and modify the first shell config file it finds to make it persistent.

## Disclaimer

This installation script is **non-official** third party software. It is not provided by, endorsed, or supported by Pantheon Systems. It is made available with no warranty or commitment for upkeep. Refer to the [LICENSE](LICENSE.txt) file for more information.
