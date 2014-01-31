<?php

require_once 'NetteFramework-2.0.13-PHP5.3/Nette/loader.php';
require_once './dibi-2.1.1/dibi/dibi.php';
require_once 'DataFrontController.php';
if(!isset($_SESSION))
{
session_start();
}
$_SESSION['action'] = "select";
$_SESSION['control'] = "product";

// path to log directory where the exceptions will be stored
$path_to_log = 'log';
//- Enable debug ----------------------------------------------------------------------
\Nette\Diagnostics\Debugger::enable( \Nette\Diagnostics\Debugger::DEVELOPMENT, $path_to_log );
// Enable strict mode which will stop even on notices, comment it out if it annoys You too much, but it will force You to a better code  
\Nette\Diagnostics\Debugger::$strictMode = TRUE;



