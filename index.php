<?php
/**
 * Shared Hosting Entry Point
 * 
 * This file allows the application to run on shared hosting
 * where the document root cannot be set to the /public folder.
 */

// Pass through to the public index
require __DIR__ . '/public/index.php';
