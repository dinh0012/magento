<?php

class ResolverBlogPost extends Resolver
{
    public function get($args)
    {
        $this->load->model('blog/post');
        $post = $this->model_blog_post->getPost($args['id']);

        if ($post['featured_img']) {
            $thumb     = $this->image->getUrl($post['featured_img'], '');
            $thumbLazy = $this->image->resize($post['featured_img'], 10, 10, '');
        } else {
            $thumb = '';
            $thumbLazy = '';
        }
        

        return array(
            'id'               => $post['post_id'],
            'title'            => $post['title'],
            'shortDescription' => $post['short_content'],
            'description'      => $post['content'],
            'keyword'          => $post['identifier'],
            'image'            => $thumb,
            'imageLazy'        => $thumbLazy,
            'reviews' => function ($root, $args) {
                return $this->load->resolver('blog/review/get', array(
                    'parent' => $root,
                    'args' => $args
                ));
            }
        );
    }

    public function getList($args)
    {
        $this->load->model('blog/post');
        $filter_data = array(
            'limit' => $args['size'],
            'start'         => ($args['page'] - 1) * $args['size'],
            'sort'        => $args['sort'],
            'order'          => $args['order']
        );

        if ($args['category_id'] !== 0) {
            $filter_data['filter_category_id'] = $args['category_id'];
        }
        
        $results = $this->model_blog_post->getPosts($filter_data);
        $product_total = $this->model_blog_post->getTotalPosts($filter_data);

        $posts = array();

        foreach ($results as $post) {
            $posts[] = $this->get(array( 'id' => $post['ID'] ));
        }

        return array(
            'content'          => $posts,
            'first'            => $args['page'] === 1,
            'last'             => $args['page'] === ceil($product_total / $args['size']),
            'number'           => (int) $args['page'],
            'numberOfElements' => count($posts),
            'size'             => (int) $args['size'],
            'totalPages'       => (int) ceil($product_total / $args['size']),
            'totalElements'    => (int) $product_total,
        );
    }
}