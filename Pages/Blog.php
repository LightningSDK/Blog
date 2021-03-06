<?php
/**
 * @file
 * Contains lightningsdk\core\Pages\Blog
 */

namespace lightningsdk\blog\Pages;

use lightningsdk\blog\Model\Blog as BlogModel;
use lightningsdk\blog\Model\Category;
use lightningsdk\core\Model\URL;
use lightningsdk\core\Tools\Output;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\Template;
use lightningsdk\core\View\Page;
use lightningsdk\blog\Model\Post;

/**
 * A page handler for viewing and editing the blog.
 *
 * @package lightningsdk\core\Pages
 */
class Blog extends Page {

    protected $nav = 'blog';
    protected $page = ['blog', 'lightningsdk/blog'];
    protected $share = false;

    protected function hasAccess() {
        return true;
    }

    public function get() {
        $blog_id = Request::get('id', Request::TYPE_INT) | Request::get('blog_id', Request::TYPE_INT);
        $path = explode('/', Request::getLocation());

        $blog = BlogModel::getInstance();

        if ($blog_id) {
            $blog->loadContentById($blog_id);
            $this->setBlogMetadata(new Post($blog->posts[0]));
        }
        elseif (!empty($path[0]) || count($path) > 2) {
            // This page num can be in index 2 (blog/page/#) or index 3 (blog/category/a-z/#).
            $blog->page = is_numeric($path[count($path) - 1]) ? $path[count($path) - 1] : 1;

            if (!empty($path[1]) && $path[1] == 'author') {
                // Load an author's article list.
                if ($author_id = $blog->getAuthorID(preg_replace('/\.htm$/', '', $path[2]))) {
                    $blog->loadList('author', $author_id);
                } else {
                    Output::http(404);
                }
            } elseif (!empty($path[1]) && $path[1] == 'category') {
                // Load category list.
                $category = preg_replace('/\.htm$/', '', $path[2]);
                $c_parts = explode('-', $category);
                if (is_numeric(end($c_parts))) {
                    $blog->page = array_pop($c_parts);
                }
                $blog->category = implode('-', $c_parts);
                if ($cat = Post::getCategory($blog->category)) {
                    $blog->category_url = $cat['cat_url'];
                    $blog->loadList('category', $cat['cat_id']);
                } else {
                    Output::http(404);
                }
            } elseif (preg_match('/.htm$/', $path[0])) {
                // DEPRECATED
                // Load single blog by URL.
                $blog->loadContentByURL(preg_replace('/.htm$/', '', $path[0]));
                if (empty($blog->id)) {
                    Output::http(404);
                }
                $this->setBlogMetadata(new Post($blog->posts[0]));
            } elseif (!empty($path[1]) && $path[1] != 'page') {
                $blog->loadContentByURL(preg_replace('/.htm$/', '', $path[1]));
                if (empty($blog->id)) {
                    Output::http(404);
                }
                $this->setBlogMetadata(new Post($blog->posts[0]));
            } elseif (!empty($blog->page)) {
                $blog->loadList();
            }
        }
        else {
            // Fall back, load blogroll
            // TODO: This should only happen on /blog, otherwise it should return a 404
            $blog->loadList();
        }

        $template = Template::getInstance();
        if (count($blog->posts) == 1) {
            $template->set('page_section','blog');
        } else {
            // If there is more than one, we show a list with short bodies.
            $blog->shorten_body = true;
        }
        $template->set('blog', $blog);
    }

    /**
     * Render a widget.
     *
     * @param $options
     * @param $vars
     * @return string
     */
    public static function renderMarkup($options, $vars) {
        $template = new Template();
        if (!empty($options['id'])) {
            $blog = Post::loadByID($options['id']);
            $template->set('blog', $blog);
            return $template->render(['blog-preview', 'lightningsdk/blog'], true);
        }
        $limit = $options['limit'] ?? 10;
        $output = '';
        if (array_key_exists('recent', $options)) {
            // Show recent list
            $recent = Post::getRecent();
            $output = '<div><ul>';
            foreach ($recent as $p) {
                $p = new Post($p);
                $output .= "<li><a href='{$p->getLink()}'>{$p->title}</a></li>";
            }
            $output .= '</ul></div>';
        }
        elseif (array_key_exists('authors',  $options)) {
        }
        elseif (array_key_exists('categories', $options)) {
            $categories = Category::getAllCategories();
            $output = '<div><ul>';
            foreach ($categories as $c) {
                $output .= "<li><a href='/blog/category/". $c['cat_url'] . "'>{$c['category']}</a> ({$c['count']})</li>";
            }
            $output .= '</ul></div>';
        }
        return $output;
    }

    /**
     * @param Post $post
     */
    protected function setBlogMetaData($post) {
        $this->setMeta('title', $post->title);
        $this->setMeta('keywords', $post->keywords);
        $this->setMeta('description', $post->getShortBody(250, false));
        $this->setMeta('twitter_creator', $post->twitter);
        if ($image = $post->getHeaderImage()) {
            $this->setMeta('image', URL::getAbsolute($image));
        }
    }
}
