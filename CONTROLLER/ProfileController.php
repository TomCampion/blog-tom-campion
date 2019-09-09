<?php
namespace Tom\Blog\Controller;

class ProfileController extends Controller{

    private $helper;
    private $userManager;
    private $commentManager;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new \Tom\Blog\Services\Helper();
        $this->userManager = new \Tom\Blog\Model\UserManager();
        $this->commentManager = new \Tom\Blog\Model\CommentManager();
    }

    public function executeProfil(){
        $comments = $this->commentManager->getCommentsFromUser($_SESSION['id'], 10);
        echo  $this->twig->render('profil.twig',['comments' => $comments]);
    }

    private function redirectedUnconnectedUsers(){
        if(empty($_SESSION['id'])){
            header('Location: accueil');
        }
    }

    private function editProfile(String $firstname, String $lastname, String $email){
        if ($_SESSION['editProfile_token'] == $_POST['token']) {
            $message = '';
            if(!$this->helper->isAlpha($firstname))
                $message = $message.'<p class="msg_error">Le champ prénom ne peut contenir que des lettres ! </p>';
            if(strlen($firstname) > 45)
                $message = $message.'<p class="msg_error">La taille maximum du prénom est de 45 caractères ! </p>';
            if(!$this->helper->isAlpha($lastname))
                $message = $message.'<p class="msg_error">Le champ nom ne peut contenir que des lettres ! </p>';
            if(strlen($lastname) > 45)
                $message = $message.'<p class="msg_error">La taille maximum du nom est de 45 caractères ! </p>';
            if(!$this->helper->isEmail($email))
                $message = $message.'<p class="msg_error">L\adresse e-mail n\'est pas valide ! </p>';
            if(strlen($email) > 254)
                $message = $message.'<p class="msg_error">La taille maximum de l\'email est de 254 caractères ! </p>';

            if (empty($message)) {
                $this->userManager->update($firstname, $lastname, $email, $_SESSION['id']);
                $_SESSION['email'] = $email;
                $_SESSION['firstname'] = $firstname;
                $_SESSION['lastname'] = $lastname;
                header('Location: profil');
            }else{
                $message = '<div class="alert alert-danger" role="alert"><strong>'.$message.'</strong></div>';
            }
            return $message;
        }else{
            header('Location: profil');
        }
    }

    public function executeEditProfile()
    {
        $this->redirectedUnconnectedUsers();
        if(!empty($_POST['nom']) and !empty($_POST['prenom']) and !empty($_POST['email'])) {
            $msg_edit = $this->editProfile($_POST['prenom'], $_POST['nom'], $_POST['email']);
            echo $this->twig->render('profil.twig', ['msg' => $msg_edit]);
        }else{
            $this->executeProfil();
        }
    }

    private function changePassword( String $password, String $password2){
        if ($_SESSION['changePassword_token'] == $_POST['token']) {
            $message = '';
            if(strlen($password) < 3)
                $message = '<p class="msg_error">Votre mot de passe est trop court, il doit faire au moins 3 caractères </p>';
            if(strlen($password) > 254)
                $message = $message.'<p class="msg_error">La taille maximum du mot de passe est de 254 caractères ! </p>';
            if($password !== $password2)
                $message = $message.'<p class="msg_error">Vos mots de passe sont différents ! </p>';

            if (empty($message)) {
                $this->userManager->updatePassword($password, $_SESSION['id']);
                $message = '<div class="alert alert-success" role="alert"><strong>Votre mot de passe a bien été modifié !</strong></div>';
            }else{
                $message = '<div class="alert alert-danger" role="alert"><strong>'.$message.'</strong></div>';
            }
            return $message;
        }else{
            header('Location: profil');
        }
    }

    public function executeChangePassword(){
        $this->redirectedUnconnectedUsers();
        if(!empty($_POST['password']) and !empty($_POST['password2'])) {
            $msg_password = $this->changePassword($_POST['password'], $_POST['password2']);
            echo $this->twig->render('profil.twig', ['msg' => $msg_password]);
        }else{
            $this->executeProfil();
        }
    }

}