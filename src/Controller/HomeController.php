<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Entity\Course;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    #[Route(path: '/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response {
        $courseRepository = $em->getRepository(Course::class);
        $courses = $courseRepository->findBy([
            'is_active' => true,
        ], [
            'start_date' => 'DESC'
        ]);
        return $this->render('home/index.html.twig', [
            'courses' => $courses
        ]);
    }

    #[Route(path: '/courseRegister/{courseId}', name: 'app_course_register')]
    public function registerForCourse(int $courseId, EntityManagerInterface $em): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $courseRepository = $em->getRepository(Course::class);

        $course = $courseRepository->find($courseId);
        if ($course === null || $course->isIsActive() === false) {
           return $this->render('404.html.twig');
        }

        $course->addUser($user);
        $em->flush();

        $contactsRepo = $em->getRepository(Contacts::class);
        $contacts = $contactsRepo->createQueryBuilder('c')
            ->andWhere('c.is_active = 1')->getQuery()->execute();
        return $this->render('home/course_register.html.twig', [
            'course' => $course,
            'contacts' => $contacts
        ]);
    }

    #[Route(path: '/courseDetails/{courseId}', name: 'app_course_details')]
    public function courseDetails(int $courseId, EntityManagerInterface $em): Response {
        $courseRepository = $em->getRepository(Course::class);
        $course = $courseRepository->find($courseId);
        if ($course === null || $course->isIsActive() === false) {
            return $this->render('404.html.twig');
        }

        return $this->render('home/course_details.html.twig', [
            'course' => $course
        ]);
    }
}