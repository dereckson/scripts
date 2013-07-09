<?php
/**
 *  @author qkaiser
 *  This is a mediawiki extension that send a message to an IRC channel when a modification 
 *  is done on the wiki. Stable, but needs a little work on the message creation (I need to 
 *  get the structure of $article from mediawiki source code).
*/

$ip = "irc.geeknode.org";
$port = 6667;
$nickname = "";
$password = "";
$ident = "";
$realname = "";
$home = "#bhackspace";
$backslash = chr(92);
 
 
function ircnotify($article, $user, $text, $summary, $isminor, $iswatch, $section) {
    global $ip, $port, $nickname, $password, $ident, $realname, $home, $backslash;
 
   
    # body
    $s = "$user has made a "
       . ($isminor ? "minor" : "major")
       . " change in article \"$article->mTitle\" [http://bhackspace.be/index.php/". urlencode(str_replace(' ', '_', $article->mTitle))."]\n";
    if ($summary) $s .= "saying: $summary\n";

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, $ip, $port);
sleep(1);
socket_write($socket, "USER $ident $ident $ident :$realname\nNICK $nickname\n");
while ($read = socket_read($socket, 2048)) {
	$hack = explode("\n", $read);

	foreach ($hack as $this_hack) {
		preg_match("/(\w+) (\d\d\d) ((\w+) )+/", $this_hack, $match);

		if(count($match)==5){

		if ($match[2] == 376) { 
			socket_write($socket, "PRIVMSG C nick identify $password \r\n");
			socket_write($socket, "JOIN $home\n"); 
		}
	
		if ($match[2] == 366) {
			socket_write($socket, "PRIVMSG $home : $s\r\n");
			$read = socket_read($socket, 2048);
			socket_write($socket, "LEAVE\r\n");
			socket_shutdown($socket, 2);
			socket_close($socket);
		}
		}
	}
	
} 
return true;
}
 
$wgHooks['ArticleSaveComplete'][] = array('ircnotify');
