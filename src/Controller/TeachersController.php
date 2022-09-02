<?php

namespace App\Controller;

use App\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TeachersController extends AbstractController
{
    #[Route(path: '/teachers', name: 'app_teachers')]
    public function index(EntityManagerInterface $em)
    {
        $repo = $em->getRepository(Teacher::class);
        return $this->render('teachers/index.html.twig', [
            'teachers' => $repo->findBy(['is_active' => true])
        ]);
    }

    #[Route(path: '/teacher/{id}', name: 'app_teacher_details')]
    public function details(int $id, EntityManagerInterface $em)
    {
        $repo = $em->getRepository(Teacher::class);
        $teacher = $repo->find($id);
        if (!$teacher || !$teacher->isIsActive()) {
            return $this->render('404.html.twig');
        }
        return $this->render('teachers/details.html.twig', [
            'teacher' => $teacher
        ]);
    }
}