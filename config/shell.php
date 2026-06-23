<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Shell — allowed user IDs
    |--------------------------------------------------------------------------
    |
    | The /admin/shell page runs real commands in the production container, so
    | on top of the `admin` middleware it is further restricted to specific
    | Livelatch user IDs. Only a session logged in as one of these IDs may load
    | the page or execute a command; everyone else (including other admins) gets
    | a 403 and never sees the nav link.
    |
    | Comma-separated list in SHELL_ALLOWED_USER_IDS. The default is the owner's
    | account so the lock works without any env change. (Note: on Railway the
    | config is cached at build, so a runtime SHELL_ALLOWED_USER_IDS env edit
    | only takes effect on the next deploy — change this default if you need a
    | guaranteed value.)
    |
    */

    'allowed_user_ids' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('SHELL_ALLOWED_USER_IDS', '922137'))),
        fn ($id) => $id !== ''
    )),

];
