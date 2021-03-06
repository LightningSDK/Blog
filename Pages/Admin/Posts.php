<?php

namespace lightningsdk\blog\Pages\Admin;

use lightningsdk\core\Model\Permissions;
use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\Navigation;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\blog\Model\Post;
use lightningsdk\core\Tools\Template;

class Posts extends Table {

    const TABLE = Post::TABLE;
    const PRIMARY_KEY = 'blog_id';

    protected $trusted = true;

    protected $sort = ['time' => 'DESC'];

    protected $links = [
        Post::TABLE . Post::CATEGORY_TABLE => [
            'index' => Post::TABLE . Post::BLOG_CATEGORY_TABLE,
            'key' => 'cat_id',
            'display_column' => 'category',
            'list' => 'compact'
        ]
    ];

    protected $action_fields = [
        'view' => [
            'display_name' => 'View',
            'type' => 'html',
        ],
        'share' => [
            'column_name' => 'Share',
            'type' => 'link',
            'url' => '/admin/social/share?type=blog&id=',
            'display_name' => '<img src="/images/lightning/share.png">',
        ]
    ];

    protected $custom_buttons = [
        'send' => [
            'type' => self::CB_SUBMITANDREDIRECT,
            'text' => 'Save &amp; Share',
            'redirect' => '/admin/social/share?type=blog&id={' . self::PRIMARY_KEY . '}',
        ],
    ];

    protected $preset = [
        'user_id' => [
            'type' => 'hidden'
        ],
        'time' => [
            'type' => 'datetime'
        ],
        'url' => [
            'type' => 'url',
            'unlisted' => true
        ],
        'body' => [
            'upload' => true,
            'type' => 'html',
            'div' => true,
        ],
    ];

    protected function hasAccess() {
        return ClientUser::requirePermission(Permissions::EDIT_BLOG);
    }

    protected function afterPost() {
        if (Request::get('return') == 'view') {
            Navigation::redirect('/blog/' . $this->list['url']);
        }
    }

    protected function initSettings() {
        Template::getInstance()->set('full_width', true);

        $this->preset['user_id']['default'] = ClientUser::getInstance()->id;
        $this->preset['url']['submit_function'] = function(&$output) {
            $output['url'] = Request::post('url', Request::TYPE_URL) ?: Request::post('title', Request::TYPE_URL);
        };
        $this->preset['header_image'] = Post::getHeaderImageSettings();

        $this->action_fields['view']['html'] = function($row) {
            return '<a href="/blog/' . $row['url'] . '"><img src="/images/lightning/resume.png" /></a>';
        };
    }
}
