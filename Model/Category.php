<?php

namespace lightningsdk\blog\Model\Blog;

use lightningsdk\blog\Model\Post;
use lightningsdk\core\Model\BaseObject;
use lightningsdk\core\Tools\Database;

class Category extends BaseObject {
    protected function getCatLink($cat) {
        $categories = Post::getAllCategories();
        return '/blog/category/' . !empty($categories[$cat]) ? $categories[$cat] : null;
    }

    protected function getCategory($search_value) {
        return Database::getInstance()->selectRow(
            self::TABLE . self::TABLE,
            ['cat_url' => ['LIKE', $search_value]]
        );
    }

    public static function getAllCategories($order = 'count', $sort_direction = 'DESC') {
        static $categories = [];
        if (empty($categories[$order][$sort_direction])) {
            $categories[$order][$sort_direction] = Database::getInstance()->selectAll(
                [
                    'from' => self::TABLE . self::BLOG_CATEGORY_TABLE,
                    'join' => ['JOIN', self::TABLE . self::TABLE, 'USING (cat_id)'],
                ],
                [],
                [
                    'count' => ['expression' => 'COUNT(*)'],
                    'category',
                    'cat_url'
                ],
                'GROUP BY cat_id ORDER BY `' . $order . '` ' . $sort_direction . ' LIMIT 10'
            );
        }
        return $categories[$order][$sort_direction];
    }
}
