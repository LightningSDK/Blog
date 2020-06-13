<?php

namespace lightningsdk\blog\Database\Schema;

use lightningsdk\core\Database\Schema;

class BlogCategory extends Schema {

    const TABLE = 'blog_blog_category';

    public function getColumns() {
        return [
            'blog_id' => $this->int(true),
            'cat_id' => $this->int(true),
        ];
    }

    public function getKeys() {
        return [
            'blog_id' => [
                'columns' => ['blog_id'],
            ],
            'cat_id' => [
                'columns' => ['cat_id'],
            ],
        ];
    }
}
