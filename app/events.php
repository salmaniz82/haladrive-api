<?php


Event::on('load', function() {

	echo "this should fire when load fired <br />";

});


Event::on('load', function() {

	echo "this should fire when load fired 2 <br />";

});


Event::on('publish', function() {

	echo "update notification <br />";

});


Event::on('publish', function() {

	echo "trigger email <br />";

});


Event::on('publish', function() {

	echo "update sitemap.xml <br />";

});

Event::on('update', function() {

	echo "post were updated <br>";

});


Event::fire('load');

Event::fire('publish');

Event::fire('update');

Event::fire('nothing');

Event::fire('update');


Event::fire('publish');


?>