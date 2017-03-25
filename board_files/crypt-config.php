<?php

//
// This config file is for the crypt, hash and security settings
// The defaults are a balance of relative security and resource cost
// If you have performance issues or want greater security you can change these settings
//

// This is the hash algorithm used for post edit/deletion passwords
define('POST_PASSWORD_ALGORITHM', 'sha256');

// This is the hash algorithm used for secure tripcodes
define('SECURE_TRIPCODE_ALGORITHM', 'sha256');

// Choose whether a SHA256 hash is generated and stored for uploaded files
define('GENERATE_FILE_SHA256', true);

// Whether to use SHA256 (if enabled) to check for duplicate uploads; otherwise SHA1 is used
define('COMPARE_FILE_WITH_SHA256', true);

// If a different hashing method or cost was used on something, then rehash it with the current settings
// Leave this false unless permanently upgrading to a better algorithm
define('DO_PASSWORD_REHASH', false);

// Whether to pass PHP's PASSWORD_DEFAULT to password_hash, which should pick the best algorithm available
// If set to false we will try using bcrypt specifically regardless of PHP's default
// If bcrypt or a better algorithm is not available for some reason Nelliel will try a fallback to SHA512 or SHA256
define('USE_PASSWORD_DEFAULT', true);

// The hash settings for staff logins and other higher security things
// Bcrypt - PHP default is 10; 04 is the minimum; 31 is maximum
// SHA2 - PHP default is 5000; minimum is 1000; maximum is 999999999
define('PASSWORD_BCRYPT_COST', 12);
define('PASSWORD_SHA2_COST', 50000);

