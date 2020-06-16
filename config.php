<?php

return [
    'routes' => [
        'dynamic' => [
            '^blog(/.*)?$' => lightningsdk\blog\Pages\Blog::class,
        ],
        'static' => [
            'admin/blog/edit' => lightningsdk\blog\Pages\Admin\Posts::class,
            'admin/blog/categories' => lightningsdk\blog\Pages\Admin\Categories::class,
        ],
    ],
    'markup' => [
        'renderers' => [
            'blog' => lightningsdk\blog\Pages\Blog::class,
        ]
    ],
    'menus' => [
        'admin' => [
            'Blog' => [
                'children' => [
                    'Posts' => 'admin/blog/edit',
                    'Categories' => 'admin/blog/categories'
                ],
            ],
        ],
    ],
    'compiler' => [
        'css' => [
            'lightningsdk/blog' => [
                'blog.scss' => 'lightning.css',
            ],
        ],
    ],
    'sitemap' => [
        'blog' => \lightningsdk\blog\Model\Blog::class,
    ],
];
