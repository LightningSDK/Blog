<?php

namespace lightningsdk\blog\Model;

use lightningsdk\core\Model\BaseObject;
use lightningsdk\core\Tools\Database;

class CategoryOverridable extends BaseObject {

    const TABLE = 'blog_category';

    /**
     * @param $cat
     * @return Category
     */
    public static function getCatFromAll($cat) {
        $categories = static::getAllCategories();
        return new static($categories[$cat]);
    }

    public static function getCategory($search_value) {
        return Database::getInstance()->selectRow(
            self::TABLE . self::CATEGORY_TABLE,
            ['cat_url' => ['LIKE', $search_value]]
        );
    }

    public static function getAllCategories($order = 'count', $sort_direction = 'DESC') {
        static $categories = [];
        if (empty($categories[$order][$sort_direction])) {
            $query = static::getCategoriesQuery([], $order, $sort_direction);
            $categories[$order][$sort_direction] = Database::getInstance()->selectAllQuery($query);
        }
        return $categories[$order][$sort_direction];
    }

    protected static function getCategoriesQuery($where = [], $order = 'count', $sort_direction = 'DESC') {
        return [
            'from' => 'blog_category',
            'join' => ['JOIN', 'blog_blog_category', 'USING (cat_id)'],
            'where' => $where,
            'select' => [
                'count' => ['expression' => 'COUNT(*)'],
                'category',
                'cat_url',
                'cat_id',
            ],
            'group_by' => ['cat_id'],
            'order_by' => [$order => $sort_direction],
            'limit' => 10,
            'indexed_by' => 'cat_id',
        ];
    }

    protected function getCatName($cat) {
        $categories = Post::getAllCategoriesIndexed();
        if (!empty($categories[$cat])) {
            return $categories[$cat]['category'];
        }
        return null;
    }

    public static function getAllCategoriesIndexed() {
        static $categories = [];

        if (empty($categories)) {
            $categories = Database::getInstance()->selectAllQuery([
                'from' => self::TABLE . self::CATEGORY_TABLE,
                'indexed_by' => 'cat_id',
                'order_by' => ['category' => 'ASC'],
            ]);
        }

        return $categories;
    }
}
