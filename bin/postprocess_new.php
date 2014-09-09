<?php

require_once(dirname(__FILE__) . '/config.php');
require_once(WWW_DIR . '/lib/postprocess.php');
require_once(WWW_DIR . '/lib/framework/Settings.php');
require_once(WWW_DIR . '/lib/nntp.php');
require_once(WWW_DIR . '/lib/site.php');
require_once(WWW_DIR . '/lib/anidb.php');
require_once(WWW_DIR . '/lib/thetvdb.php');
require_once(WWW_DIR . "/lib/ColorCLI.php");
require_once(dirname(__FILE__) . '/../lib/TvAnger.php');
require_once(dirname(__FILE__) . '/../lib/Pprocess.php');
require_once(dirname(__FILE__) . '/../lib/Info.php');

$pdo = new Settings();
/**
Array with possible arguments for run and
whether or not those methods of operation require NNTP
 **/

$args = array(
	'additional' => true,
	'all'        => true,
	'allinf'     => true,
	'amazon'     => false,
	'anime'      => false,
	'book'       => false,
	'console'    => false,
	'games'      => false,
	'movies'     => false,
	'music'      => false,
	'nfo'        => true,
	'pre'        => true,
	'sharing'    => true,
	'tv'         => false,
	'xxx'        => false,
);

$bool = array(
	'true',
	'false'
);

if (!isset($argv[1]) || !in_array($argv[1], $args) || !isset($argv[2]) || !in_array($argv[2], $bool)) {
	exit(
	$pdo->log->error(
		"\nIncorrect arguments.\n"
		. "The second argument (true/false) determines wether to echo or not.\n\n"
		. "php postprocess_new.php all true         ...: Does all the types of post processing.\n"
		. "php postprocess_new.php pre true         ...: Processes all Predb sites.\n"
		. "php postprocess_new.php nfo true         ...: Processes NFO files.\n"
		. "php postprocess_new.php movies true      ...: Processes movies.\n"
		. "php postprocess_new.php music true       ...: Processes music.\n"
		. "php postprocess_new.php console true     ...: Processes console games.\n"
		. "php postprocess_new.php games true       ...: Processes games.\n"
		. "php postprocess_new.php book true        ...: Processes books.\n"
		. "php postprocess_new.php anime true       ...: Processes anime.\n"
		. "php postprocess_new.php tv true          ...: Processes tv.\n"
		. "php postprocess_new.php xxx true         ...: Processes xxx.\n"
		. "php postprocess_new.php additional true  ...: Processes previews/mediainfo/etc...\n"
		. "php postprocess_new.php sharing true     ...: Processes uploading/downloading comments.\n"
		. "php postprocess_new.php allinf true      ...: Does all the types of post processing on a loop, sleeping 15 seconds between.\n"
		. "php postprocess_new.php amazon true      ...: Does all the amazon (books/console/games/music/xxx).\n"
	)
	);
}

$nntp = null;
if ($args[$argv[1]] === true) {
	$nntp = new NNTP(['Settings' => $pdo]);
	if (($pdo->getSetting('alternate_nntp') == 1 ? $nntp->doConnect(true, true) : $nntp->doConnect()) !== true) {
		exit($pdo->log->error("Unable to connect to usenet." . PHP_EOL));
	}
}

$postProcess = new PProcess(['Settings' => $pdo, 'Echo' => ($argv[2] === 'true' ? true : false)]);

$charArray = ['a','b','c','d','e','f','0','1','2','3','4','5','6','7','8','9'];

switch ($argv[1]) {

	case 'all':
		$postProcess->processAll($nntp);
		break;
	case 'allinf':
		$i = 1;
		while ($i = 1) {
			$postProcess->processAll($nntp);
			sleep(15);
		}
		break;
	case 'additional':
		$postProcess->processAdditional($nntp, '', (isset($argv[3]) && in_array($argv[3], $charArray) ? $argv[3] : ''));
		break;
	case 'amazon':
		$postProcess->processBooks();
		$postProcess->processConsoles();
		$postProcess->processGames();
		$postProcess->processMusic();
		$postProcess->processXXX();
		break;
	case 'anime':
		exit;
	//$postProcess->processAnime();
	//break;
	case 'book':
		$postProcess->processBooks();
		break;
	case 'console':
		$postProcess->processConsoles();
		break;
	case 'games':
		$postProcess->processGames();
		break;
	case 'nfo':
		$postProcess->processNfos($nntp, '', (isset($argv[3]) && in_array($argv[3], $charArray) ? $argv[3] : ''));
		break;
	case 'movies':
		$postProcess->processMovies('', (isset($argv[3]) && in_array($argv[3], $charArray) ? $argv[3] : ''));
		break;
	case 'music':
		$postProcess->processMusic();
		break;
	case 'pre':
		break;
	case 'sharing':
		$postProcess->processSharing($nntp);
		break;
	case 'tv':
		$postProcess->processTV('', (isset($argv[3]) && in_array($argv[3], $charArray) ? $argv[3] : ''));
		break;
	case 'xxx':
		$postProcess->processXXX();
		break;
	default:
		exit;
}