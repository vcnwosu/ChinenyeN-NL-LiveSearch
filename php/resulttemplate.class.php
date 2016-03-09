<?php

    /**
     * template.class.php
     * Handle templating for search results
     */

    class ResultTemplate {

        // properties of the class
	private $tpl = ''; // to hold each result
        private $db_results; // for database connection
        private $db_rows; // database rows

        // constructor
        public function __construct($search) {
	    // query database
	    $conn = new PDO('mysql:host=localhost;dbname=searchengine', 'BLANK', 'BLANK');
	    $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	    $query = "SELECT * FROM nlt WHERE title LIKE '%" . $search . "%' ORDER BY vews_votes DESC";

            $statement = $conn -> query($query);

	    // grab database results
	    $this -> db_results = $statement -> fetchAll();

	    // grab total rows
	    $this -> db_rows = $statement -> rowCount();
        }

        // place content into assigned_values to fill in template
        public function assign($tpl_placeholder, $content) {
	    // assign to template placeholder
            $this -> tpl = str_ireplace('{' . $tpl_placeholder . '}', $content, $this -> tpl);
        }

        // echo content out to browser
        public function show() {
            // fill template
	    // pull in template
	    foreach($this -> db_results as $match) {
                $this -> tpl = file_get_contents('../templates/result.tpl.html');

	        $this -> assign('URL', $match["url"]);
	        $this -> assign('URL_PART', substr($match["url"], 7, 50) . '...');
	        $this -> assign('TOPIC', substr($match["title"], 0, 55) . '...');

	        $poster = (empty($match["poster"])) ? "Unkown" : $match["poster"];
	        $this -> assign('AUTHOR', $poster);
                
		$date = (empty($match["date"])) ? "Today" : preg_filter('/.+On/i', '', $match["date"]);
		// if(empty($date)) $date = "Unknown";
	        $this -> assign('POSTDATE', $date);
	        
	        if(strpos($match["url"], "www.nairaland.com") === 7) $this -> assign('VIEWS_UPVOTES', $match["vews_votes"] . ' views');
	        else if(strpos($match["url"], "www.stackoverflow.com") === 7) $this -> assign('VIEWS_UPVOTES', $match["vews_votes"] . ' votes');

	        echo $this -> tpl;
            }

        }

    }

?>
