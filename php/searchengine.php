<?php

    /**
     * searchengine.php
     * Search engine source code for the NL challenge
     */

    // ini_set("display_errors", 1);
    // error_reporting(E_ALL);

    // includes
    require_once './resulttemplate.class.php'; // crawler function

    // set up result template
    $results = new ResultTemplate($_GET["q"]);

    // echo out the results
    $results -> show();


?>
