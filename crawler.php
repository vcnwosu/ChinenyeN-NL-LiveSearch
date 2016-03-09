<?php

    /**
     * crawler.php
     * Designed specifically for the Nairaland Search Engine Challenge
     */

    // database connection to store crawl results
    $db = new PDO('mysql:host=localhost;dbname=searchengine', 'BLANK', 'BLANK');
    $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = 'INSERT INTO nlt (url, title, poster, date, vews_votes) VALUES(?, ?, ?, ?, ?)'; 

    // prepare and bind the SQL statement
    $statement = $db -> prepare($query);

    function crawl($url, $statement) {
        /**
         * Credit for the idea to use the DOMDocument class goes to <hobodave>
         * of StackOverflow. The implementation however has been tweaked to 
         * suit the needs of this script.
         */

	// parse the url to use the host if needed to resolve relative paths
	$url_parts = parse_url($url);

        // new DOMDocument instance
        $dom = new DOMDocument();

	foreach(range(1, 200) as $i) {
	    echo $url . PHP_EOL;

            @$dom -> loadHTMLFile($url);

            if($dom === FALSE) return;

	    // xpath to query the DOM
	    $xpath = new DOMXPath($dom);

	    // set up url to continue crawling
	    $new_url = $url_parts["scheme"] . '://' . $url_parts["host"];

	    // sub dom if necessary
	    $sub_dom = new DOMDocument();

	    // for Nairaland links
            if(strcmp($url_parts["host"], "www.nairaland.com") === 0) {

	        // get topic, poster, date and views
	        $td = $xpath -> query("//td[@id]");
	        foreach($td as $td) {
	            $topic = $xpath -> query("b[1]/a[1]", $td) -> item(0) -> textContent;
		    $topicURL = $xpath -> query("b[1]/a[1]", $td) -> item(0) -> getAttribute('href');	
                    $poster = $xpath -> query("span[1]/b[1]", $td) -> item(0) -> textContent;
		    $views = $xpath -> query("span[1]/b[3]", $td) -> item(0) -> textContent;

		    @$sub_dom -> loadHTMLFIle($new_url.$topicURL);
		    $sub_xpath = new DOMXPath($sub_dom);
		    $date = $sub_xpath -> query("//td[@class='bold l pu']/span[last()]") -> item(0) -> textContent;
                   
    	            // write the crawled data to file
		    $line = $new_url.$topicURL . "\t" . $topic . "\t" . $poster . "\t" . $date . "\t" . $views . PHP_EOL;
		    //echo $line . PHP_EOL; 
		    file_put_contents('php/data', $line, FILE_APPEND);

		    // put into database
		    $statement -> execute([$new_url.$topicURL, $topic, $poster, $date, $views]);

	        }
		// finish url setup
	        $new_url .= '/programming/' . $i;
            }

            // for StackOverflow links
	    if(strcmp($url_parts["host"], "www.stackoverflow.com") === 0) {
	        // find the questions
	        $questions = $xpath -> query("//div[@class='question-summary']");
	        foreach($questions as $question) {
		    $topic = $xpath -> query("div[2]/h3[1]", $question) -> item(0) -> textContent;
	  	    $topicURL = $xpath -> query("div[2]/h3[1]/a[1]", $question) -> item(0) -> getAttribute('href');
		    $poster = $xpath -> query("div[2]/div[3]/*/div[@class='user-details']/a[last()]", $question) -> item(0) -> textContent;
		    $upvotes = $xpath -> query("div[1]/div[2]/div[1]/*/span[1]", $question) -> item(0) -> textContent;
		    $date = $xpath -> query("div[2]/div[3]/*/div[@class='user-action-time']/span[1]", $question) -> item(0) -> textContent;

		    if(empty($date)) {
			@$sub_dom -> loadHTMLFile($new_url.$topicURL);
			$sub_xpath = new DOMXPath($sub_dom);

			$time = $sub_xpath -> query("//p[@class='label-key']/b") -> item(0) -> textContent;
		 	$date = $time;
		    }

		    // write crawled data to file
		    $line = $new_url.$topicURL . "\t" . $topic . "\t" . $poster . "\t" . $date . "\t" . $upvotes . PHP_EOL;
		    //echo $line . PHP_EOL; 
   	  	    file_put_contents('php/data', $line, FILE_APPEND);

                    // put into database
                    $statement -> execute([$new_url.$topicURL, $topic, $poster, $date, $upvotes]);

	        }
		// finish url setup
		$page = $i + 1;
		$new_url .= '/questions?page=' . $page . '&sort=votes';
            }

	    // complete url setup
	    $url = $new_url;

        }

    }

    crawl('http://www.nairaland.com/programming/0', $statement);
    crawl('http://www.stackoverflow.com/questoins?sort=votes', $statement);


?>
