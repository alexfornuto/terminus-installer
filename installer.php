<?php

/* Terminus Installer Script
Created by Alex Fornuto - alex@fornuto.com
*/

// Define environment variables.
$pathUpdated = false;
$paths = explode(":", getenv('PATH'));             // Creates an array with all paths in $PATH
$installdir = ("/usr/local/bin");                  // Creates a string with the desired installation path
$rcfiles = array(                                  // Array of common .rc files to look for.
  ".bashrc",
  ".zshrc",
  ".config/fish/config.fish",
  ".profile",
);
$package = "terminus";
$opts = getopt("", ["version::"]);
$version = $opts['version'] ?? NULL;

// Function to download Terminus executable file from GitHub to /tmp/ then move it to $installdir
// prompts for sudo if required.
function downloadTerminus($installdir, $package, $version)
{
    // $opts defines values required by the GitHub API to respond correctly. $context formats them for use.
    $opts = [
            'http' => [
              'method' => 'GET',
              'header' => [
                'User-Agent: PHP'
              ]
            ]
    ];
    $context  = stream_context_create($opts);
    $releases = file_get_contents("https://api.github.com/repos/pantheon-systems/" . $package . "/releases", false, $context);
    $releases = json_decode($releases);
    $release = getRelease($releases, $version);

    if (!$release) {
        echo "No release found for version " . $version . "\n";
        exit("Download unsuccessful.\n\n");
    }

    $url = $release['url'];
    
    // Do the needful
    echo("\nDownloading Terminus " . $release['version'] . " from " . $url . " to /tmp \n");
    $couldDownload = file_put_contents("/tmp/" . $package . ".phar", file_get_contents($url));
    echo("Moving to " . $installdir . "...\n");
    if(!rename("/tmp/" . $package . ".phar", $installdir . "/" . $package)){
        echo("\n" . $installdir . " requires admin rights to write to...\n");
        exec("sudo mv /tmp/" . $package . ".phar " . $installdir . "/" . $package);
        echo("\n");
    }
    // Return true if successful
    $couldMove = exec("ls " . $installdir . "/" . $package, $output, $couldMove);
    return $couldMove;
}

function getRelease($releases, $version) {
  if ($version) {
    foreach ($releases as $i => $release) {
      if ($release->tag_name == $version) {
        return [
          'url' => $release->assets[0]->browser_download_url,
          'version' => $release->tag_name,
        ];
      }
    }
    return NULL;
  }
  $version  = $releases[0]->tag_name;
  $url = $releases[0]->assets[0]->browser_download_url;
  return [
    'url' => $url,
    'version' => $version
  ];
}


// Function to add to any common shell configuration files a line to amend $PATH with  $installdir.
function ammendPath($rcfile, $installdir, &$pathUpdated)
{
    $pathUpdated = file_put_contents(getenv('HOME') . "/$rcfile", "# Adds Terminus to \$PATH\nPATH=\$PATH:" . $installdir . "\n\n", FILE_APPEND | LOCK_EX);
    if (!$pathUpdated) {
        throw new Exception($rcfile . " found, but unable to write to it.");
    }

    return $pathUpdated;
}

function checkCompletion($rcfile)
{
  if( strpos(file_get_contents(getenv('HOME') . "/$rcfile"), "eval \"$(terminus autocomplete)\"") !== false)
  {
    return false;
  }
  else {
    return true;
  }
}

function enableAutoComplete($rcfile)
{
    $evalCompletion = file_put_contents(getenv('HOME') . "/$rcfile", "\n# Enable Terminus autocomplete\neval \"$(terminus autocomplete)\"", FILE_APPEND | LOCK_EX);
    if (!$evalCompletion) {
        throw new Exception($rcfile . " found, but unable to write to it.");
    }

    return $evalCompletion;

}

// Function to determine if ~/.terminus/bin is already in $PATH
function checkpath($paths, $installdir)
{

    return in_array($installdir, $paths);
}

// BEGIN ACTUAL DOING OF THINGS!

//Makes ~/.terminus/bin if it doesn't exist.
if (!file_exists($installdir)) {
    echo("Creating " . $installdir . "/\n");
    mkdir($installdir, 0700, true);
}

//Download terminus.phar
if (downloadTerminus($installdir, $package, $version)) {
    echo("Installed to " . $installdir . "\n\n");
} else {
    exit("Download unsuccessful.\n\n");
}

// Make Terminus executable
echo("Making Terminus executable... ");
chmod($installdir . "/" . $package, 0755)
or exit("\nUnable to set Terminus as executable.\n");
echo("Done.\n");

// If $installdir isn't in path, add it.
if (checkpath($paths, $installdir) === false) {
    foreach ($rcfiles as $rcfile) {
        if (file_exists(getenv('HOME') . "/$rcfile") && is_writable(getenv('HOME') . "/$rcfile")) {
            ammendpath($rcfile, $installdir, $pathUpdated);
            echo("Found " . $rcfile . " and added " . $installdir .
            " to your \$PATH.\nIn order to run Terminus, you must first run:\n\nsource ~/" . $rcfile . "\n");
        }
    }
    if (!$pathUpdated) {
        echo("Terminus has been installed to " . $installdir .
        " But no suitable configuration file was found to update \$PATH.\n\nYou must manually add " . $installdir .
        " to your PATH, or execute Terminus from the full path.\n\n");
    }
}

// Check for autocompletion in rcfile and amend if not
foreach ($rcfiles as $rcfile) {
  if (file_exists(getenv('HOME') . "/$rcfile") && is_writable(getenv('HOME') . "/$rcfile")){
    if (checkCompletion($rcfile) === true) {
      enableAutoComplete($rcfile);
      echo("Added Autocompletion to " . $rcfile . "\n");
    }
    else {
      echo("Found autocompletion setting for Terminus in " . "/$rcfile" . "\n" );
    }
  }
}

exit();
