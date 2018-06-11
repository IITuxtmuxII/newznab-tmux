<?php

namespace App\Http\Controllers;

use App\Models\UserDownload;
use Blacklight\XXX;
use App\Models\User;
use App\Models\Predb;
use App\Models\Video;
use Blacklight\AniDB;
use Blacklight\Books;
use Blacklight\Games;
use Blacklight\Movie;
use Blacklight\Music;
use App\Models\Release;
use Blacklight\Console;
use App\Models\Settings;
use Blacklight\Releases;
use App\Models\ReleaseNfo;
use App\Models\DnzbFailure;
use App\Models\ReleaseFile;
use App\Models\ReleaseRegex;
use Blacklight\ReleaseExtra;
use App\Models\ReleaseComment;
use Illuminate\Support\Facades\Auth;

class DetailsController extends BasePageController
{
    /**
     * @param $guid
     *
     * @throws \Exception
     */
    public function show($guid)
    {
        $this->setPrefs();

        if ($guid !== null) {
            $releases = new Releases(['Settings' => $this->settings]);
            $re = new ReleaseExtra;
            $data = Release::getByGuid($guid);
            $user = User::find(Auth::id());
            $cpapi = $user['cp_api'];
            $cpurl = $user['cp_url'];
            $releaseRegex = ReleaseRegex::query()->where('releases_id', '=', $data['id'])->first();

            if (! $data) {
                $this->show404();
            }

            if ($this->isPostBack()) {
                ReleaseComment::addComment($data['id'], $data['gid'], \request()->input('txtAddComment'), Auth::id(), \request()->ip());
            }

            $nfo = ReleaseNfo::getReleaseNfo($data['id']);
            $reVideo = $re->getVideo($data['id']);
            $reAudio = $re->getAudio($data['id']);
            $reSubs = $re->getSubs($data['id']);
            $comments = ReleaseComment::getComments($data['id']);
            $similars = $releases->searchSimilar($data['id'], $data['searchname'], $this->userdata['categoryexclusions']);
            $failed = DnzbFailure::getFailedCount($data['id']);
            $downloadedBy = UserDownload::query()->with('user')->where('releases_id', $data['id'])->get(['users_id']);

            $showInfo = '';
            if ($data['videos_id'] > 0) {
                $showInfo = Video::getByVideoID($data['videos_id']);
            }

            $mov = '';
            if ($data['imdbid'] !== '' && $data['imdbid'] !== 0000000) {
                $movie = new Movie(['Settings' => $this->settings]);
                $mov = $movie->getMovieInfo($data['imdbid']);
                if (! empty($mov['title'])) {
                    $mov['title'] = str_replace(['/', '\\'], '', $mov['title']);
                    $mov['actors'] = makeFieldLinks($mov, 'actors', 'movies');
                    $mov['genre'] = makeFieldLinks($mov, 'genre', 'movies');
                    $mov['director'] = makeFieldLinks($mov, 'director', 'movies');
                    if (Settings::settingValue('site.trailers.trailers_display')) {
                        $trailer = empty($mov['trailer']) || $mov['trailer'] === '' ? $movie->getTrailer($data['imdbid']) : $mov['trailer'];
                        if ($trailer) {
                            $mov['trailer'] = sprintf('<iframe width="%d" height="%d" src="%s"></iframe>', Settings::settingValue('site.trailers.trailers_size_x'), Settings::settingValue('site.trailers.trailers_size_y'), $trailer);
                        }
                    }
                }
            }

            $xxx = '';
            if ($data['xxxinfo_id'] !== '' && $data['xxxinfo_id'] !== 0) {
                $x = new XXX();
                $xxx = $x->getXXXInfo($data['xxxinfo_id']);

                if (isset($xxx['trailers'])) {
                    $xxx['trailers'] = $x->insertSwf($xxx['classused'], $xxx['trailers']);
                }

                if ($xxx && isset($xxx['title'])) {
                    $xxx['title'] = str_replace(['/', '\\'], '', $xxx['title']);
                    $xxx['actors'] = makeFieldLinks($xxx, 'actors', 'xxx');
                    $xxx['genre'] = makeFieldLinks($xxx, 'genre', 'xxx');
                    $xxx['director'] = makeFieldLinks($xxx, 'director', 'xxx');
                } else {
                    $xxx = false;
                }
            }

            $game = '';
            if ($data['gamesinfo_id'] !== '') {
                $g = new Games();
                $game = $g->getGamesInfoById($data['gamesinfo_id']);
            }

            $mus = '';
            if ($data['musicinfo_id'] !== '') {
                $music = new Music(['Settings' => $this->settings]);
                $mus = $music->getMusicInfo($data['musicinfo_id']);
            }

            $book = '';
            if ($data['bookinfo_id'] !== '') {
                $b = new Books();
                $book = $b->getBookInfo($data['bookinfo_id']);
            }

            $con = '';
            if ($data['consoleinfo_id'] !== '') {
                $c = new Console();
                $con = $c->getConsoleInfo($data['consoleinfo_id']);
            }

            $AniDBAPIArray = '';
            if ($data['anidbid'] > 0) {
                $AniDB = new AniDB(['Settings' => $releases->pdo]);
                $AniDBAPIArray = $AniDB->getAnimeInfo($data['anidbid']);
            }

            $pre = Predb::getForRelease($data['predb_id']);

            $releasefiles = ReleaseFile::getReleaseFiles($data['id']);

            $this->smarty->assign('releasefiles', $releasefiles);
            $this->smarty->assign('release', $data);
            $this->smarty->assign('reVideo', $reVideo);
            $this->smarty->assign('reAudio', $reAudio);
            $this->smarty->assign('reSubs', $reSubs);
            $this->smarty->assign('nfo', $nfo);
            $this->smarty->assign('show', $showInfo);
            $this->smarty->assign('movie', $mov);
            $this->smarty->assign('xxx', $xxx);
            $this->smarty->assign('anidb', $AniDBAPIArray);
            $this->smarty->assign('music', $mus);
            $this->smarty->assign('con', $con);
            $this->smarty->assign('game', $game);
            $this->smarty->assign('book', $book);
            $this->smarty->assign('predb', $pre);
            $this->smarty->assign('comments', $comments);
            $this->smarty->assign('searchname', getSimilarName($data['searchname']));
            $this->smarty->assign('similars', $similars);
            $this->smarty->assign('privateprofiles', (int) Settings::settingValue('..privateprofiles') === 1);
            $this->smarty->assign('failed', $failed);
            $this->smarty->assign('cpapi', $cpapi);
            $this->smarty->assign('cpurl', $cpurl);
            $this->smarty->assign('regex', $releaseRegex);
            $this->smarty->assign('downloadedby', $downloadedBy);

            $meta_title = 'View NZB';
            $meta_keywords = 'view,nzb,description,details';
            $meta_description = 'View NZB for'.$data['searchname'];

            $content = $this->smarty->fetch('viewnzb.tpl');
            $this->smarty->assign(
                [
                    'content' => $content,
                    'meta_title' => $meta_title,
                    'meta_keywords' => $meta_keywords,
                    'meta_description' => $meta_description,
                ]
            );
            $this->pagerender();
        }
    }
}
