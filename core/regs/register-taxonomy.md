# Register post type

See [Code reference](https://developer.wordpress.org/reference/functions/register_taxonomy/)


## Labels
```php
<?php
$labels = [
    'name'          => _x( 'Plural name', 'taxonomy_label', 'nbpc' ),
    'singular_name' => _x( 'Singular name', 'taxonomy_label', 'nbpc' ),
];
```


## Sample params
```php
<?php
$args = [
    'description'        => _x( 'Taxonomy description.', 'taxonomy_description', 'nbpc' ),
    'public'             => true,
    'publicly_queryable' => true,
    'hierarchical'       => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'show_in_nav_menus'  => true,
    'show_in_rest'       => false,
    'show_tagcloud'      => false,
    'show_in_quick_edit' => true,
    'show_admin_column'  => true,
    'rewrite'            => [
        'slug'         => 'slug',
        'with_front'   => true,
        'hierarchical' => false,
        'ep_mask'      => EP_NONE,
    ],
    'query_var'          => true,
]
```