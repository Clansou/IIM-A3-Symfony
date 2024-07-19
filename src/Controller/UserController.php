<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route(
        name: 'create_employee',
        path: '/api/employees/create',
        methods: ['POST'],
        defaults: [
            '_api_resource_class' => User::class,
            '_api_operation_name' => 'create_employee',
        ]
    )]
    public function __invoke(Request $request): User
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new \Exception('No authentication token found.');
        }
        $currentUser = $token->getUser();
        if (in_array('ROLE_PATRON', $currentUser->getRoles())) {
        $userData = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setRoles($userData['roles']);

        $plainPassword = $userData['plainPassword'] ?? null;
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $user;
    }
}
