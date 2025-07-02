<?php
// Public endpoint for saving addresses
// This file is accessible as /save-address.php

// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Delegate to the actual save address handler
require_once __DIR__ . '/includes/save_address.php'; 