<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Command;
use App\Entity\Drink;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    #[Route(
        name: 'assign_command_to_barman',
        path: '/api/commands/{commandeId}/assign',
        methods: ['PATCH']
    )]
    public function assignCommandeToBarman(int $commandeId): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new \Exception('No authentication token found.');
        }
        $currentUser = $token->getUser();
        if (!in_array('ROLE_BARMAN', $currentUser->getRoles())) {
            throw new \Exception('User is not authorized to assign commands.');
        }

        $command = $this->entityManager->getRepository(Command::class)->find($commandeId);
        if (!$command) {
            throw new \Exception('Commande not found.');
        }

        $command->setBarman($currentUser);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande assigned successfully.']);
    }

    #[Route(
        name: 'command_is_ready',
        path: '/api/commands/{commandeId}/ready',
        methods: ['PATCH']
    )]

    public function CommandIsReady(int $commandeId): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new \Exception('No authentication token found.');
        }
        $currentUser = $token->getUser();
        if (!in_array('ROLE_BARMAN', $currentUser->getRoles())) {
            throw new \Exception('User is not authorized to assign commands.');
        }

        $command = $this->entityManager->getRepository(Command::class)->find($commandeId);
        if (!$command) {
            throw new \Exception('Commande not found.');
        }

        if ($command->getBarman()->getId() != $currentUser->getId()) {
            throw new \Exception('Le barman actuel ne correspond pas au barman assigné à la commande.');
        }

        $command->setStatus("prête");
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande is ready.']);
    }

    #[Route(
        name: 'paid_command',
        path: '/api/commands/{commandeId}/paid',
        methods: ['PATCH']
    )]

    public function CommandIsPaid(int $commandeId): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new \Exception('No authentication token found.');
        }
        $currentUser = $token->getUser();
        if (!in_array('ROLE_SERVER', $currentUser->getRoles())) {
            throw new \Exception('User is not authorized to assign commands.');
        }

        $command = $this->entityManager->getRepository(Command::class)->find($commandeId);
        if (!$command) {
            throw new \Exception('Commande not found.');
        }

        if($command->getStatus() === "payée"){
            throw new \Exception('Commande already paid.');
        }

        $command->setStatus("payée");
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande is payed.']);
    }

    #[Route(
        name: 'create_command',
        path: '/api/commands',
        methods: ['Post']
    )]
    public function createCommand(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new \Exception('No authentication token found.');
        }
        $currentUser = $token->getUser();
        if (!in_array('ROLE_BARMAN', $currentUser->getRoles())) {
            throw new \Exception('User is not authorized to assign commands.');
        }

        $requestData = json_decode($request->getContent(), true);

        $command = new Command();
        $command->setServer($currentUser);
        $command->setTableNumber($requestData['table_number']);
        $command->setCreatedDate(new DateTime());
        $command->setStatus("en cours de préparation");
        $drinksId = $requestData['drinks'] ?? [];
        foreach ($drinksId as $drinkId){
            $drink = $this->entityManager->getRepository(Drink::class)->find($drinkId);
            if($drink != null){
                $command->addDrink($drink);
            }
        }
        $this->entityManager->persist($command);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande assigned successfully.']);
    }
}
