<?php
 
namespace App\State;
 
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
 
class UserPasswordHasherProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }
 
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Si on n'a pas reçu de valeur pour plainPassword
        if (!$data->getPlainPassword()) {
            // On laisse le process s'exécuter
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }
 
        // Si on a reçu une valeur pour plainPassword, on hash le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $data,
            $data->getPlainPassword()
        );
        // On assigne le nouveau mot de passe
        $data->setPassword($hashedPassword);
        // On supprime la valeur de plainPassword
        $data->eraseCredentials();
 
        // On execute le process
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
