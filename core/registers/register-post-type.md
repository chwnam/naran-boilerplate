# Register post type

See [Code reference](https://developer.wordpress.org/reference/functions/register_post_type/)

## Label param sample
```php
<?php
$labels = [
    'name'                     => _x( 'Plural Name', 'post_type_label', 'nbpc' ),
    'singular_name'            => _x( 'Singular Name', 'post_type_label', 'nbpc' ),
    'add_new'                  => _x( 'Add new', 'post_type_label', 'nbpc' ),
    'add_new_item'             => _x( 'Add new singular name', 'post_type_label', 'nbpc' ),
    'edit_item'                => _x( 'Edit singular name', 'post_type_label', 'nbpc' ),
    'new_item'                 => _x( 'New singular name', 'post_type_label', 'nbpc' ),
    'view_item'                => _x( 'View singular name', 'post_type_label', 'nbpc' ),
    'view_items'               => _x( 'View singular name', 'post_type_label', 'nbpc' ),
    'search_items'             => _x( 'Search singular name', 'post_type_label', 'nbpc' ),
    'not_found'                => _x( 'Not found', 'post_type_label', 'nbpc' ),
    'not_found_in_trash'       => _x( 'Not found in trash', 'post_type_label', 'nbpc' ),
    'all_items'                => _x( 'All singular name', 'post_type_label', 'nbpc' ),
    'archives'                 => _x( 'Singular name archives', 'post_type_label', 'nbpc' ),
    'insert_into_item'         => _x( 'Insert into singular name', 'post_type_label', 'nbpc' ),
    'upload_to_this_item'      => _x( 'Upload to this singular name', 'post_type_label', 'nbpc' ),
    'featured_image'           => _x( 'Featured image', 'post_type_label', 'nbpc' ),
    'set_featured_image'       => _x( 'Set featured image', 'post_type_label', 'nbpc' ),
    'remove_featured_image'    => _x( 'Remove featured image', 'post_type_label', 'nbpc' ),
    'use_featured_image'       => _x( 'Use as featured image', 'post_type_label', 'nbpc' ),
    'menu_name'                => _x( 'Plural Name', 'post_type_label', 'nbpc' ),
    'filter_items_list'        => _x( 'Filter singular name list', 'post_type_label', 'nbpc' ),
    'filter_by_date'           => _x( 'Filter by date', 'post_type_label', 'nbpc' ),
    'items_list_navigation'    => _x( 'Singular name list navigation', 'post_type_label', 'nbpc' ),
    'items_list'               => _x( 'Singular name list', 'post_type_label', 'nbpc' ),
    'item_published'           => _x( 'Singular name published', 'post_type_label', 'nbpc' ),
    'item_published_privately' => _x( 'Singular name published privately', 'post_type_label', 'nbpc' ),
    'item_reverted_to_draft'   => _x( 'Singular name reverted to draft', 'post_type_label', 'nbpc' ),
    'item_scheduled'           => _x( 'Singular name scheduled', 'post_type_label', 'nbpc' ),
    'item_updated'             => _x( 'Singular name updated', 'post_type_label', 'nbpc' ),					
];
```


## Sample params
```php
<?php
$args = [
    'labels' => $labels,
    'description'         => _x( 'Post type description', 'post_type_description', 'nbpc' ),
    'public'              => true,
    'hierarchical'        => false,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_icon'           => '',
    'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
    'has_archive'         => true,
    'rewrite'             => [
        'slug'    => 'slug',
        'feeds'   => true,
        'pages'   => true,
        'ep_mask' => EP_PERMALINK,
    ],
    'query_var'           => true,
    'can_export'          => true,
    'delete_with_user'    => false,
    'show_in_rest'        => false,
]
```
