<?php

namespace App\Controller;

use App\Entity\Contacts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{

    #[Route(path: '/contacts', name: 'app_contacts')]
    public function index(EntityManagerInterface $entityManager) {
        $contacts = $entityManager->getRepository(Contacts::class)->findBy([
            'is_active' => 1
        ]);

        return $this->render('contacts/index.html.twig', [
            'contacts' => $contacts
        ]);
    }

}