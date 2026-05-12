<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup action password
    |--------------------------------------------------------------------------
    |
    | Creating a manual backup or saving automatic backup settings on the queue
    | settings page requires this password for every user, regardless of role.
    |
    | Defaults to 1992 when BACKUP_PASSWORD is not set in .env. Set BACKUP_PASSWORD
    | in .env to use a different value. Set BACKUP_PASSWORD to an empty value in
    | .env only if you need to disable this gate (not recommended).
    |
    */

    'action_password' => env('BACKUP_PASSWORD', '1992'),

];
