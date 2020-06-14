<?php

namespace lightningsdk\blog\Model;

use lightningsdk\core\Model\BaseObject;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\IO\FileManager;
use lightningsdk\core\View\HTML;
use lightningsdk\core\View\HTMLEditor\Markup;
use lightningsdk\core\View\Text;

class PostOverridable extends BaseObject {
    const TABLE = 'blog';
    const PRIMARY_KEY = 'blog_id';

    const CATEGORY_TABLE = '_category';
    const BLOG_CATEGORY_TABLE = '_blog_category';
    const AUTHOR_TABLE = '_author';

    public static function loadPosts($where = [], $join = [], $limit = '') {
        return Database::getInstance()->selectAll(
            [
                'from' => self::TABLE,
                'join' => array_merge($join, self::joinAuthorCatTables()),
            ],
            $where,
            [
                static::TABLE . '.*',
                'blog_author.*',
                'categories' => ['expression' => 'GROUP_CONCAT(blog_blog_category.cat_id)']
            ],
            'GROUP BY ' . self::TABLE . '.blog_id ORDER BY time DESC ' . $limit
        );
    }

    public static function getRecent() {
        static $recent;
        if (empty($recent)) {
            $recent = Database::getInstance()->select(static::TABLE, [], [], 'ORDER BY time DESC LIMIT 5');
        }
        return $recent;
    }

    protected static function joinAuthorCatTables() {
        return [
            // Join categories
            [
                'LEFT JOIN',
                static::TABLE . '_blog_category',
                'ON ' . self::TABLE . '_blog_category.blog_id = ' . self::TABLE . '.blog_id'
            ],
            // Join author
            ['LEFT JOIN', 'blog_author', 'ON blog_author.user_id = ' . self::TABLE . '.user_id']
        ];
    }

    /**
     * Loads the image that was explicitly set as the header image.
     *
     * @return bool|string
     */
    public function getTrueHeaderImage() {
        if (!empty($this->header_image)) {
            // Image from upload.
            $field = self::getHeaderImageSettings();
            $handler = empty($field['file_handler']) ? '' : $field['file_handler'];
            $fileHandler = FileManager::getFileHandler($handler, $field['container']);
            return $fileHandler->getWebURL($this->header_image);
        }
        return false;
    }

    public static function getHeaderImageSettings() {
        return [
            'type' => 'image',
            'browser' => true,
            'container' => 'images',
            'format' => 'jpg',
        ];
    }

    /**
     * Gets any image that can be used for the header.
     *
     * @return string|null
     *   An absolute file url.
     */
    public function getHeaderImage() {
        $header_image = NULL;
        if ($image = $this->getTrueHeaderImage()) {
            return $image;
        }
        elseif ($img = HTML::getFirstImage($this->body)) {
            // Image from post.
            $this->header_from_source = true;
            return $img;
        }
        else {
            // Default image.
            return Configuration::get('blog.default_image');
        }
    }

    public function getLink() {
        return '/blog/' . $this->url;
    }

    public function getURL() {
        return Configuration::get('web_root') . $this->getLink();
    }

    public function getAuthorLink() {
        return '/blog/author/' . $this->author_url;
    }

    public function getBody($force_short = false) {
        if ($this->shorten_body || $force_short) {
            return $this->getShortBody();
        } else {
            return $this->getRenderedBody();
        }
    }

    protected function getRenderedBody() {
        static $rendered_body = null;
        if ($rendered_body === null) {
            $rendered_body = Markup::render($this->body);
        }
        return $rendered_body;
    }

    public function getShortBody($length = 250, $allow_html = true) {
        return Text::shorten($allow_html ? Markup::render($this->body) : strip_tags(Markup::render($this->body)), $length);
    }

    public function getAuthorName() {
        return $this->author_name;
    }

    public function renderCategoryList() {
        $categories = explode(',', $this->categories);
        foreach ($categories as $cat) {
            echo '<li><a href="' . $this->getCatLink($cat) . '">' . $this->getCatName($cat) . '</a></li>';
        }
    }

    protected function getCatLink($cat) {
        $categories = Post::getAllCategoriesIndexed();
        if (!empty($categories[$cat])) {
            return '/blog/category/' . $categories[$cat]['cat_url'];
        }
        return null;
    }

    protected function getCatName($cat) {
        $categories = Post::getAllCategoriesIndexed();
        if (!empty($categories[$cat])) {
            return $categories[$cat]['category'];
        }
        return null;
    }

    public static function getCategory($search_value) {
        return Database::getInstance()->selectRow(
            self::TABLE . self::CATEGORY_TABLE,
            ['cat_url' => ['LIKE', $search_value]]
        );
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

    public static function getAllCategories($order = 'count', $sort_direction = 'DESC') {
        static $categories = [];
        if (empty($categories[$order][$sort_direction])) {
            $categories[$order][$sort_direction] = Database::getInstance()->selectAll(
                [
                    'from' => self::TABLE . self::BLOG_CATEGORY_TABLE,
                    'join' => ['JOIN', self::TABLE . self::CATEGORY_TABLE, 'USING (cat_id)'],
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
