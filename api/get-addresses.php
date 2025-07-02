<?php
// Public endpoint for getting user addresses
// This file is accessible as /get-addresses.php

// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Delegate to the actual get addresses handler
require_once __DIR__ . '/includes/get_addresses.php'; 