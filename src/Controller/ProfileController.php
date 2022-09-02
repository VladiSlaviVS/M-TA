<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/details.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/profile/my-courses', name: 'app_profile_courses')]
    public function courses(): Response
    {
        return $this->render('profile/courses.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/profile/my-articles', name: 'app_profile_articles')]
    public function articles(): Response
    {
        return $this->render('profile/articles.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/profile/my-students', name: 'app_profile_students')]
    public function students(EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(User::class);
        $students = $repo->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role_1)=0')
            ->andWhere('JSON_CONTAINS(u.roles, :role_2)=0')
            ->setParameter('role_1', '"' . User::ROLE_TEACHER . '"')
            ->setParameter('role_2', '"' . User::ROLE_ADMIN . '"')
            ->getQuery()->execute();
        return $this->render('profile/students.html.twig', [
            'students' => $students,
        ]);
    }
}
