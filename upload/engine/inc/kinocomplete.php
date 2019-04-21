<?php

// cms specified condition
if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN'))
  die("Hacking attempt!");

// including entry point
require 'engine/modules/kinocomplete/index.php';