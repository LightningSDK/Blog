<?php

namespace lightningsdk\blog\Database\Schema;

use lightningsdk\core\Database\Schema;

class Post extends Schema {

    const TABLE = 'blog';

    public function getColumns() {
        return [
            'blog_id' => $this->autoincrement(),
            'user_id' => $this->int(true),
            'time' => $this->int(true),
            'title' => $this->varchar(255),
            'url' => $this->varchar(128),
            'keywords' => $this->varchar(255),
            'header_image' => $this->varchar(255),
            'body' => $this->text(Schema::MEDIUMTEXT),
        ];
    }

    public function getKeys() {
        return [
            'primary' => 'blog_id',
            'url' => [
                'columns' => ['url'],
                'unique' => true,
            ],
        ];
    }
}
