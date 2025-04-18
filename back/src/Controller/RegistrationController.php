<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/account', name: 'account_create', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setFirstname($data['firstname']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'User created'], 201);
    }
}
