<?php

namespace JW\WP_CLI_Utils;

/*\
=== WP_CLI | PACKAGE | LIBRARY
|||
 |  This module provides helpers related to the `wp-cli` package and `wp-content` folder .
|||
\*/

/**
  Get the `wp-content` directory.
*/
function WP_ContentRoot()
{
  return get_home_path()."/wp-content/";
}

/**
  Get the path to a subfolder of `wp-content`.
*/
function WP_ContentPath(...$folders)
{
  return Build_Path(WP_ContentRoot(), ...$folders);
}

/**
  Build a path, each parameter is between path delimeters.
*/
function Build_Path(...$folders)
{
  return wp_normalize_path( join("/",$folders) );
}

/**
  Builds a namespace, like a path, where each string is a namespace part.
*/
function Build_Namespace(...$path)
{
  return join('\\',$path);
}

/**
  Helper object for plugin programming.

  Quickly initialize a namespace as a plugin with:

  ```{php}
    use function \JW\WP_CLI_Utils\WP_Command;
    `WP_Command(__NAMESPACE__)->Register();`
  ```

  There is not shared state via a global instance.

  To work with the package relative helper functions, cache an instance.

  ```{php}
    $WP_Command = WP_Command(__NAMESPACE__);
  ```

  The main command for the plugin defaults to:

  ```{php}
  function Command_Function ()
  {
    ...
  }
  ```

*/
Class WP_CLI_Command
{
  public $Command_Namespace;
  public $Command_Name;
  public $Command_Function = "Command_Function";

  public function __construct($Command_Namespace) {
        $this->initNamespace($Command_Namespace);
  }

  /**
    Builds the namespaces required for registration.
  */
  function initNamespace($Command_Namespace)
  {
    $this->Command_Namespace = $Command_Namespace;
    $this->Command_Name = str_replace("\\","-", $Command_Namespace);
  }

  function Callback_Path()
  {
    return Build_Namespace($this->Command_Namespace, $this->Command_Function);
  }

  function Register()
  {
    \WP_CLI::add_command($this->Command_Name, $this->Callback_Path());
    return $this;
  }

  /**
    Creates a file path for this package namespace inside `wp-content`.
  */
  function ContentPath(...$folders)
  {
    return WP_ContentPath($this->Command_Name, ...$folders);
  }

  function Debug()
  {
    print("\nDEBUG\n");
    print($this->Command_Namespace . "\n");
    print($this->Command_Name . "\n");
  }
}

function WP_Command($Namespace)
{
  return new WP_CLI_Command($Namespace);
}

function Define_WP_CLI_Command($Command_Namespace)
{
  $r = new WP_CLI_Command($Command_Namespace);

  $r->Register();

  return $r;
}
