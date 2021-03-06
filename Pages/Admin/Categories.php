<?php

namespace lightningsdk\blog\Pages\Admin;

use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\Template;
use lightningsdk\core\Tools\ClientUser;

class Categories extends Table {

    const TABLE = 'blog_category';
    const PRIMARY_KEY = 'cat_id';

    public function hasAccess() {
        ClientUser::requireAdmin();
        return true;
    }

    protected function initSettings() {
        Template::getInstance()->set('full_width', true);
        $this->preset['cat_url'] = [
            'submit_function' => function(&$output){
                $output['cat_url'] = Request::post('cat_url', 'cat_url') ?: Request::post('category', 'url');
            }
        ];
    }
}
