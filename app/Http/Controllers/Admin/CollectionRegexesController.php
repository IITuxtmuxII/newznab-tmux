<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasePageController;
use App\Models\Category;
use Blacklight\Regexes;
use Illuminate\Http\Request;

class CollectionRegexesController extends BasePageController
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $this->setAdminPrefs();
        $regexes = new Regexes(['Settings' => $this->pdo, 'Table_Name' => 'collection_regexes']);

        $title = 'Collections Regex List';

        $group = ($request->has('group') && ! empty($request->input('group')) ? $request->input('group') : '');
        $regex = $regexes->getRegex($group);
        $this->smarty->assign(
            [
                'group'             => $group,
                'regex'             => $regex,
            ]
        );

        $this->smarty->assign('pager', $this->smarty->fetch('pager.tpl'));

        $content = $this->smarty->fetch('collection_regexes-list.tpl');

        $this->smarty->assign(
            [
                'title' => $title,
                'content' => $content,
            ]
        );

        $this->adminrender();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function edit(Request $request)
    {
        $this->setAdminPrefs();
        $regexes = new Regexes(['Settings' => $this->pdo, 'Table_Name' => 'collection_regexes']);
        $error = '';
        $regex = ['id' => '', 'regex' => '', 'description' => '', 'group_regex' => '', 'ordinal' => '', 'status' => 1];

        switch ($request->input('action') ?? 'view') {
            case 'submit':
                if ($request->input('group_regex') === '') {
                    $error = 'Group regex must not be empty!';
                    break;
                }

                if ($request->input('regex') === '') {
                    $error = 'Regex cannot be empty';
                    break;
                }

                if ($request->input('description') === '') {
                    $request->merge(['description' => '']);
                }

                if (! is_numeric($request->input('ordinal')) || $request->input('ordinal') < 0) {
                    $error = 'Ordinal must be a number, 0 or higher.';
                    break;
                }

                if ($request->input('id') === '') {
                    $regexes->addRegex($request->all());
                } else {
                    $regexes->updateRegex($request->all());
                }

                return redirect('collection_regexes-list');
                break;

            case 'view':
            default:
                if ($request->has('id')) {
                    $title = 'Collections Regex Edit';
                    $regex = $regexes->getRegexByID($request->input('id'));
                } else {
                    $title = 'Collections Regex Add';
                    $regex += ['status' => 1];
                }
                break;
        }

        $this->smarty->assign('regex', $regex);
        $this->smarty->assign('error', $error);
        $this->smarty->assign('status_ids', [Category::STATUS_ACTIVE, Category::STATUS_INACTIVE]);
        $this->smarty->assign('status_names', ['Yes', 'No']);

        $content = $this->smarty->fetch('collection_regexes-edit.tpl');

        $this->smarty->assign(
            [
                'title' => $title,
                'content' => $content,
            ]
        );

        $this->adminrender();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     */
    public function testRegex(Request $request)
    {
        $this->setAdminPrefs();
        $title = 'Collections Regex Test';

        $group = trim($request->has('group') && ! empty($request->input('group')) ? $request->input('group') : '');
        $regex = trim($request->has('regex') && ! empty($request->input('regex')) ? $request->input('regex') : '');
        $limit = ($request->has('limit') && is_numeric($request->input('limit')) ? $request->input('limit') : 50);
        $this->smarty->assign(['group' => $group, 'regex' => $regex, 'limit' => $limit]);

        if ($group && $regex) {
            $this->smarty->assign('data', (new Regexes(['Settings' => $this->pdo, 'Table_Name' => 'collection_regexes']))->testCollectionRegex($group, $regex, $limit));
        }

        $content = $this->smarty->fetch('collection_regexes-test.tpl');

        $this->smarty->assign(
            [
                'title' => $title,
                'content' => $content,
            ]
        );

        $this->adminrender();

    }
}
