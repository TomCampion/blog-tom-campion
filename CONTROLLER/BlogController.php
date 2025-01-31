<?php
namespace Tom\Blog\Controller;

use Tom\Blog\Services\Session;

class BlogController extends Controller{

    private $PostManager;
    private $CommentManager;

    public function __construct()
    {
        parent::__construct();
        $this->PostManager = new \Tom\Blog\Model\PostManager();
        $this->CommentManager = new \Tom\Blog\Model\CommentManager();
    }

    public function executePost($params){
        $post = $this->PostManager->get($params['id']);
        $comments = $this->CommentManager->getValidCommentsFromPost($params['id']);
        $this->twig->display('post.twig', ['post' => $post, 'comments' => $comments ]);
    }

    public function executeBlog( $params = null){
        $nbrpost = 4;
        $nbr_pages = ceil($this->PostManager->getPostsNumber()/$nbrpost);

        if(empty($params['page'])){
            $params['page'] = 1;
        }
        if($params['page']  > $nbr_pages){
            $params['page']  = ceil($this->PostManager->getPostsNumber()/$nbrpost);
        }
        $publishedPosts = $this->PostManager->getPublishedPosts((int)$params['page'] , $nbrpost);
        $this->twig->display('blog.twig', ['publishedPosts' => $publishedPosts, 'page'=> (int)$params['page'], 'nbr_pages'=> $nbr_pages ]);
    }

    public function executeAddComment($params){
        if(!empty(filter_input(INPUT_POST, 'addComment')) and !empty($params['id'])  ) {
            $msg_addComment = $this->AddComment(filter_input(INPUT_POST, 'addComment'), $params['id']);
            $this->twig->display('post.twig', ['message_addComment' => $msg_addComment]);
        }else{
            $this->executeBlog();
        }
    }

    public function executeEditComment($params){
        if (!empty(filter_input(INPUT_POST, 'post_id')) and !empty(filter_input(INPUT_POST, 'editComment')) and !empty($params['id'])) {
            $msg = $this->editComment(filter_input(INPUT_POST, 'editComment'), $params['id']);
            $post = $this->PostManager->get(filter_input(INPUT_POST, 'post_id'));
            $comments = $this->CommentManager->getValidCommentsFromPost(filter_input(INPUT_POST, 'post_id'));
            $this->twig->display('post.twig', ['post' => $post, 'comments' => $comments, 'message'=>$msg] );
        } else {
            $this->executeBlog();
        }
    }

    private function editComment( String $content, int $comment_id){
        if (Session::get('editComment_token') == filter_input(INPUT_POST, 'token')) {
            $message = '';
            if(strlen($content) < 3)
                $message = '<p class="msg_error">Votre commentaire est trop court, il doit faire au moins 3 caractères </p>';
            if(strlen($content) > 1024)
                $message = $message.'<p class="msg_error">La taille maximum du mcommentaire est de 1024 caractères ! </p>';

            if (empty($message)) {
                $comment = $this->CommentManager->get($comment_id);
                $comment->setContent(filter_input(INPUT_POST, 'editComment'));
                $comment->setState(\Tom\Blog\Model\Comments::WAITING_FOR_VALIDATION);
                $this->CommentManager->update($comment);
                $message = '<div class="alert alert-success" role="alert"><strong>Votre commentaire à bien été modifié, il sera publié dès sa validation !</strong></div>';
            }else{
                $message = '<div class="alert alert-danger" role="alert"><strong>'.$message.'</strong></div>';
            }
            return $message;
        }else{
            header('Location: blog');
        }
    }

    private function addComment(String $content, int $post_id){
        if (Session::get('addComment_token') == filter_input(INPUT_POST, 'token')) {
            $message = '';
            if(strlen($content) < 3)
                $message = '<p class="msg_error">Votre commentaire est trop court, il doit faire au moins 3 caractères </p>';
            if(strlen($content) > 1024)
                $message = $message.'<p class="msg_error">La taille maximum du mcommentaire est de 1024 caractères ! </p>';

            if (empty($message)) {
                $comment = new \Tom\Blog\Model\Comments();
                $data = [
                    'content' => $content,
                    'author' => Session::get('firstname').' '.Session::get('lastname'),
                    'userId' => Session::get('id'),
                    'postId' => $post_id,
                    'state' => \Tom\Blog\Model\Comments::WAITING_FOR_VALIDATION
                ];
                $comment->hydrate($data);
                $this->CommentManager->add($comment);
                header('Location: post-'.$post_id);
            }else{
                $message = '<div class="alert alert-danger" role="alert"><strong>'.$message.'</strong></div>';
            }
            return $message;
        }else{
            header('Location: blog');
        }
    }

}
