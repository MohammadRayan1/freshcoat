<?php

return [

    /*
    | States where FreshCoat is open. Location pages, the city picker and listing imports
    | are limited to these. Add a state code here to expand.
    */
    'launch_states' => explode(',', env('FRESHCOAT_LAUNCH_STATES', 'WA')),

];
