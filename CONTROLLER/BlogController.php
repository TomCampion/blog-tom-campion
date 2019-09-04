<?php
namespace Tom\Blog\Controller;


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
        echo  $this->twig->render('post.twig', ['post' => $post, 'comments' => $comments ] );
    }

    public function executeBlog(){
        $publishedPosts = $this->PostManager->getPublishedPosts();
        echo  $this->twig->render('blog.twig', ['publishedPosts' => $publishedPosts ]);
    }

    public function executeAddComment($params){
        if(!empty($_POST['addComment']) and !empty($params['id'])  ) {
            $msg_addComment = $this->AddComment($_POST['addComment'], $params['id']);
            echo $this->twig->render('post.twig', ['message_addComment' => $msg_addComment]);
        }else{
            $this->executeLoginPage();
        }
    }

    private function addComment(String $content, int $post_id){
        $message = '';
        if(strlen($content) < 3)
            $message = '<p class="msg_error">Votre commentaire est trop court, il doit faire au moins 3 caractères </p>';
        if(strlen($content) > 1024)
            $message = $message.'<p class="msg_error">La taille maximum du mcommentaire est de 1024 caractères ! </p>';

        if (empty($message)) {
            $comment = new \Tom\Blog\Model\Comments();
            $data = [
                'content' => $content,
                'author' => $_SESSION['firstname'].' '.$_SESSION['lastname'],
                'userId' => $_SESSION['id'],
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
    }

}