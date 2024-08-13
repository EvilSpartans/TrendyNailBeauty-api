<?php

namespace App\Service;

use App\Entity\User;
use App\Classe\ResponseData;
use App\Entity\ResetPassword;
use App\Repository\UserRepository;
use App\Repository\ResetPasswordRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private UserRepository $userRepository,
        private ResetPasswordRepository $repo,
        private MailerInterface $mailer
    ) {}

    public function sendResetPasswordRequest(string $email): ResponseData
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new ResponseData([
                "message" => "Aucun utilisateur n'est associé à cet email."
            ], 400);
        }

        $resetPassword = new ResetPassword();
        $resetPassword->setUser($user);
        $resetPassword->setToken(uniqid());
        $this->repo->save($resetPassword, true);
        $token = $resetPassword->getToken();

        // Envoi de l'email 
        // $content = "Bonjour " . $user->getUsername() . "<br/>Vous avez demandé à réinitialiser votre mot de passe.<br/><br/>";
        // $content .= "Merci de copier et coller ce code dans l'application pour mettre à jour votre mot de passe : $token";
        // $mail = new Mail($this->mailer);
        // $mail->send($user->getEmail(), $user->getUsername(), 'Réinitialiser votre mot de passe', $content);

        return new ResponseData([
            "message" => "Un email contenant la procédure pour réinitialiser votre mot de passe vous a été envoyé.",
            "token" => $token
        ], 201); 
    }

    public function verifyToken(string $token): ResponseData
    {
        $resetPassword = $this->repo->findOneBy(['token' => $token]);

        if (!$resetPassword) {
            return new ResponseData([
                "message" => "Aucun utilisateur correspondant"
            ], 400);
        }

        $now = new \DateTime();
        if ($now > $resetPassword->getCreatedAt()->modify('+3 hours')) {
            return new ResponseData([], 401);
        }

        return new ResponseData([
            "message" => "Vous pouvez maintenant choisir un nouveau mot de passe",
            "token" => $token
        ], 200);
    }

    public function resetPassword(string $token, string $newPassword): ResponseData
    {
        $resetPassword = $this->repo->findOneBy(['token' => $token]);

        if (!$resetPassword) {
            return new ResponseData([
                "message" => "Aucun utilisateur correspondant"
            ], 400);
        }

        $user = $resetPassword->getUser();
        // $user = $this->userRepository->findOneBy(["id" => $resetPassword->getUser()]);

        $now = new \DateTime();
        if ($now > $resetPassword->getCreatedAt()->modify('+3 hours')) {
            return new ResponseData([], 401);
        }

        $user->setPassword($newPassword);
        $this->userRepository->save($user, true);

        return new ResponseData([
            "message" => "Mot de passe modifié"
        ], 200);
    }

    public function updatePassword(User $user, string $oldPassword, string $newPassword, string $confirmPassword): ResponseData
    {
        if ($newPassword !== $confirmPassword) {
            return new ResponseData([
                'success' => false,
                'message' => 'Les mots de passe ne correspondent pas.'
            ], 400);
        }

        if ($this->passwordEncoder->isPasswordValid($user, $oldPassword)) {
            $user->setPassword($newPassword);
            $this->userRepository->save($user, true);

            return new ResponseData([
                'success' => true,
                'message' => 'Votre mot de passe a été réinitialisé'
            ], 200);
        }

        return new ResponseData([
            'success' => false,
            'message' => 'Mot de passe actuel incorrect'
        ], 400);
    }
}
